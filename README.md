##With the help of this tool you can find cases of AWS SQS messages delivery duplication.

###Installation and settings
1. Clone the repository.
2. `composer install`
3. Adjust .env settings `cp .env.example .env`. Put your DB and AWS credentials.
4. Upgrade DB scheme `vendor/bin/doctrine orm:schema-tool:update --force`

###Usage
There are two commands available - write and read  
For example:

`php index.php write --count=2000 --queue=https://sqs.eu-west-1.amazonaws.com/928794414836/load-test --threads=20`  
`php index.php read --count=2000 --queue=https://sqs.eu-west-1.amazonaws.com/928794414836/load-test --threads=20`