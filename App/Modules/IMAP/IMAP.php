<?php


namespace App\Modules\IMAP;


use App\Modules\IMAP\HostConfig\MailHostConfig;
use App\Modules\IMAP\Interfaces\IMAPCredentials;

class IMAP
{
    private $mailHostConfig;
    private $username = '';
    private $password = '';

    public function __construct(MailHostConfig $mailhost, IMAPCredentials $credentials)
    {
        $this->setCredentials($credentials);
        $this->setHostConfig($mailhost);
    }

    public function setCredentials(IMAPCredentials $credentials)
    {
        $this->username = $credentials->getIMAPUser();
        $this->password = $credentials->getIMAPPassword();
    }

    public function setHostConfig(MailHostConfig $mailHostConfig)
    {
        $this->mailHostConfig = $mailHostConfig;
    }

    public function openConnection()
    {
        $response = imap_open($this->mailHostConfig->getIMAPHost(), $this->username, $this->password);
        if (!$response) {
            throw new \Exception(imap_last_error());
        }
    }
}
