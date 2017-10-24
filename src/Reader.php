<?php
namespace app;

use app\Entity\Message;
use Aws\Result;
use Aws\Sqs\SqsClient;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Reader extends Command
{
    private $log;
    private $client;

    public function __construct(SqsClient $client, Logger $log)
    {
        parent::__construct('read');
        $this->log = $log;
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getOption('queue');
        $threads = $input->getOption('threads');
        $count = $input->getOption('count')/$threads;
        $output->writeln("<info>Will receive $count messages in this process. Others will be received in forks.</info>");
        while ($threads - 1) {
            $threads--;
            system("/usr/local/bin/php " . __DIR__ . "/../index.php read --count=$count --queue=$queue >> /dev/null & 2>/dev/null");
        }
        foreach ($this->read($queue, $count) as $message) {
            $data = $message->getData();
            $output->writeln("<comment>Received message with data: $data</comment>");
        }
        $output->writeln("<question>Parent process has received all messages. Some processes may still work in background.</question>");
    }

    public function read(string $queue, int $count)
    {
        while ($count && $result = $this->receiveOne($queue)) {
            $count--;
            try {
                if ($message = $this->process($result)) {
                    $this->acknowledge($result);
                    yield $message;
                }
            } catch (\Exception $e) {
                if ($result->get('Messages')) {
                    $this->acknowledge($result);
                    $this->log->warning('Invalid message', [$result->toArray()]);
                } else {
                    break;
                }
            }
        }
    }

    public function receiveOne(string $queue): Result
    {
        $result = $this->client->receiveMessage([
            'QueueUrl' => $queue
        ]);

        return $result;
    }

    private function process(Result $result): Message
    {
        $data = \GuzzleHttp\json_decode($result->get('Messages')[0]['Body']);
        $receipt = $result->get('Messages')[0]['ReceiptHandle'];
        /** @var EntityManager $em */
        $em = $this->getApplication()->getContainer()->get(EntityManager::class);
        /** @var Message $fetchedMessage */
        $fetchedMessage = $em->getRepository(Message::class)->findOneBy(['data' => $data->data]);
        if ($fetchedMessage) {
            sleep(rand(1,5));
            if ($fetchedMessage->getReceipt() && $fetchedMessage->getReceipt() != $receipt) {
                $this->log->error('Duplicated message receive', [$fetchedMessage]);
            }
            $fetchedMessage->setReceipt($receipt);
            $fetchedMessage->setReceiveCount($fetchedMessage->getReceiveCount() + 1);
            $em->persist($fetchedMessage);
            $em->flush();
        } else {
            throw new \Exception();
        }

        return $fetchedMessage;
    }

    private function acknowledge(Result $result)
    {
        $this->client->deleteMessage([
            'QueueUrl' => $result->get('@metadata')['effectiveUri'],
            'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle'],
        ]);
    }

    protected function configure()
    {
        $this->setDescription('Read some messages from SQS')
            ->addOption('count', null,InputOption::VALUE_REQUIRED, 'Number of messages to read', 0)
            ->addOption('queue',null,InputOption::VALUE_REQUIRED, 'Queue name')
            ->addOption('threads',null,InputOption::VALUE_OPTIONAL, 'Number of threads', 1);
    }
}