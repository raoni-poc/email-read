<?php


namespace App\Modules\IMAP\Factory;


use App\Config;
use App\Modules\IMAP\HostConfig\GmailHostConfig;
use App\Modules\IMAP\IMAP;
use App\Modules\IMAP\Interfaces\hasIMAPHost;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class IMAPFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get(Config::class);

        if(!($config instanceof hasIMAPHost)){
            throw new \Exception('IMAP configuration class must be implement hasIMAP host interface.');
        }

        switch ($config->getIMAPHost()) {
            case 'GMAIL':
                $host = $container->get(GmailHostConfig::class);
                break;
            default:
                throw new \Exception('IMAP Host Not Found');
        }

        return new IMAP($host, $config);
    }
}
