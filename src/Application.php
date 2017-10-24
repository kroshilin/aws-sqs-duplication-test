<?php
namespace app;

use Psr\Container\ContainerInterface;

class Application extends \Symfony\Component\Console\Application
{
    /** @var  ContainerInterface */
    private $container;

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}