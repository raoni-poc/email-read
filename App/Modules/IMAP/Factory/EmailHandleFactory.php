<?php


namespace App\Modules\IMAP\Factory;


use App\Config;
use App\Modules\IMAP\HandleEmails;
use App\Modules\IMAP\HostConfig\GmailHostConfig;
use App\Modules\IMAP\IMAP;
use App\Modules\IMAP\Interfaces\hasIMAPHost;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class EmailHandleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var Config $config */
        $config = $container->get(Config::class);
        $imap = $container->get(IMAP::class);
        return new HandleEmails($imap, $config);
    }
}
