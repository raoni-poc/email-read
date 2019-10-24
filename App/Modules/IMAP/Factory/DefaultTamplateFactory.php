<?php


namespace App\Modules\IMAP\Factory;


use App\Config;
use App\Modules\IMAP\DecodeTamplate;
use App\Modules\IMAP\DefaultTemplate;
use App\Modules\IMAP\HandleEmails;
use App\Modules\IMAP\HostConfig\GmailHostConfig;
use App\Modules\IMAP\IMAP;
use App\Modules\IMAP\Interfaces\hasIMAPHost;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class DefaultTamplateFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var DecodeTamplate $config */
        $decoder = $container->get(DecodeTamplate::class);
        return new DefaultTemplate($decoder);
    }
}
