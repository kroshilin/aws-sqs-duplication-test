<?php
namespace app;

use app\Entity\Message;

class MessageGenerator
{
    public function generate(int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $message = new Message();
            $message->setData(uniqid('_', true));
            yield $message;
        }
    }
}