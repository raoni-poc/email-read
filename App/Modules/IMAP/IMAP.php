<?php


namespace App\Modules\IMAP;


use App\Modules\IMAP\HostConfig\MailHostConfig;

class IMAP
{
    private $mailhost;
    private $username;
    private $password;

    public function __construct(MailHostConfig $mailhost, string $username, string $password)
    {
        $this->mailhost = $mailhost;
        $this->username = $username;
        $this->password = $password;
    }

    public function openConnection()
    {
        $response = imap_open($this->mailhost->getIMAPHost(), $this->username, $this->password);
        if(!$response){
            throw new \Exception(imap_last_error());
        }
    }
}
