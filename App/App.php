<?php

namespace App;

use App\Modules\IMAP\HandleEmails;
use App\Modules\IMAP\IMAP;
use Zend\ServiceManager\ServiceManager;

class App
{
    public static function run(ServiceManager $serviceManager)
    {
        /** @var HandleEmails $handleEmails */
        $handleEmails = $serviceManager->get(HandleEmails::class);
        $emails = $handleEmails->getAllInvoiceEmails();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($emails);
//        var_dump($emails);
    }
}
