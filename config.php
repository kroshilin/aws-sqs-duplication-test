<?php

return [
    'sqs' => [
        'key' => getenv('AWS_ACCESS_KEY_ID'),
        'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
        'region' => getenv('AWS_REGION'),
    ],
    'queue' => [
        'fifo' => getenv('AWS_QUEUE_FIFO'),
        'regular' => getenv('AWS_QUEUE_REGULAR'),
        'toPut' => getenv('TO_PUT'),
    ],
    'db' => [
        'host' => getenv('DB_HOST'),
        'name' => getenv('DB_NAME'),
        'user' => getenv('DB_USER'),
        'password' => getenv('DB_PASS'),
    ],
    'phpPath' => getenv('PHP_PATH'),
];