<?php
require 'vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$config = require 'config.php';

$isDevMode = true;
$doctrineConfig = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);
$conn = array(
    'driver' => 'pdo_mysql',
    'host' => $config['db']['host'],
    'user' => $config['db']['user'],
    'password' => $config['db']['password'],
    'dbname' => $config['db']['name'],
);
$entityManager = EntityManager::create($conn, $doctrineConfig);