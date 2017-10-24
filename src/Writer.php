<?php

namespace app;

use app\Entity\Message;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Sqs\SqsClient;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Writer extends Command
{
    private $log;
    private $client;
    private $generator;

    public function __construct(SqsClient $client, Logger $log, MessageGenerator $generator)
    {
        parent::__construct('write');
        $this->log = $log;
        $this->client = $client;
        $this->generator = $generator;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getOption('queue');
        $threads = $input->getOption('threads');
        $count = $input->getOption('count') / $threads;
        $output->writeln("<info>Will generate and publish $count messages</info>");
        $pushed = $this->start($this->generator->generate((int)$count), $queue);
        while ($threads - 1) {
            $threads--;
            system("/usr/local/bin/php " . __DIR__ . "/../index.php write --count=$count --queue=$queue >> /dev/null & 2>/dev/null");
        }
        $output->writeln("<question>$pushed messages published in parent process. Some processes may still work in background.</question>");
    }

    public function write(Message $message, string $queue): Result
    {
        try {
            $this->logGeneratedMessage($message);
            $result = $this->client->sendMessage([
                'QueueUrl' => $queue,
                'MessageBody' => $message,
            ]);
            $this->logMessageSent($result, $message);
            return $result;
        } catch (AwsException $e) {
            $this->log->error($e->getMessage());
            throw new \Exception();
        }
    }

    public function start(\Iterator $messages, string $queue): int
    {
        $count = 0;

        foreach ($messages as $message) {
            if ($this->write($message, $queue)) {
                $count++;
            }
        }
        return $count;
    }

    protected function configure()
    {
        $this->setDescription('Write some messages to SQS')
            ->addOption('count', null,InputOption::VALUE_REQUIRED, 'Number of messages to write')
            ->addOption('queue',null,InputOption::VALUE_REQUIRED, 'Queue name')
            ->addOption('threads',null,InputOption::VALUE_OPTIONAL, 'Number of threads', 1);
    }

    private function logMessageSent(Result $result, Message $message)
    {
        /** @var EntityManager $em */
        $em = $this->getApplication()->getContainer()->get(EntityManager::class);
        /** @var Message $fetchedMessage */
        $fetchedMessage = $em->getRepository(Message::class)->findOneBy(['data' => $message->getData()]);
        $fetchedMessage->setAwsId($result->get('MessageId'));
        $em->persist($message);
        $em->flush();
    }

    private function logGeneratedMessage(Message $message)
    {
        $em = $this->getApplication()->getContainer()->get(EntityManager::class);
        $em->persist($message);
        $em->flush();
    }
}