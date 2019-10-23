<?php


namespace App\Modules\IMAP;

use App\Modules\IMAP\HostConfig\MailHostConfig;
use App\Modules\IMAP\Interfaces\IMAPCredentials;

class IMAP
{
    private $mailHostConfig;
    private $username = '';
    private $password = '';
    private $imapStream;

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
        $this->imapStream = imap_open($this->mailHostConfig->getIMAPHost(), $this->username, $this->password);
        if (!$this->imapStream) {
            throw new \Exception(imap_last_error());
        }
        return $this->imapStream;
    }

    public function closeConnection()
    {
        if (is_resource($this->imapStream)) {
            return imap_close($this->imapStream);
        }
    }
}


