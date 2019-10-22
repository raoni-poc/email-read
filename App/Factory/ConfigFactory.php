<?php

namespace App\Factory;

use App\Config;
use App\Modules\IMAP\HostConfig\GmailHostConfig;
use App\Modules\IMAP\IMAP;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ConfigFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configArr = include_once 'config.php';
        $config = new Config();
        $config->setConfig($configArr);
        return $config;
    }
}
