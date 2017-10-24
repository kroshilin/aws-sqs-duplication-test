<?php
$config = require 'config.php';

return [
     \Aws\Sqs\SqsClient::class => new \Aws\Sqs\SqsClient([
         'credentials' => [
             'key' => $config['sqs']['key'],
             'secret' => $config['sqs']['secret'],
         ],
         'region' => $config['sqs']['region'],
         'version' => 'latest',
     ]),
    \Monolog\Logger::class => new \Monolog\Logger(
        'sqs-tester',
        [new \Monolog\Handler\StreamHandler(__DIR__ . '/log', \Monolog\Logger::INFO)]
    ),
    'config' => $config,
];