<?php
require 'bootstrap.php';

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions('di.php');
$container = $builder->build();
$container->set(\Doctrine\ORM\EntityManager::class, $entityManager);

$app = new \app\Application;
$app->setContainer($container);

$app->add($container->get(\app\Writer::class));
$app->add($container->get(\app\Reader::class));

$app->run();
