<?php


namespace App\Modules\IMAP\HostConfig;


class GmailHostConfigConfig extends MailHostConfig
{
    public function getIMAPHost(): string
    {
        return '{imap.gmail.com:993/imap/ssl}INBOX';
    }
}
