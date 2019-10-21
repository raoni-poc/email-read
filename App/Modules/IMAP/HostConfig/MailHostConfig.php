<?php

namespace App\Modules\IMAP\HostConfig;


abstract class MailHostConfig
{
    abstract public function getIMAPHost(): string;
}
