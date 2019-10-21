<?php


namespace App\Modules\IMAP\Factory;


use App\Modules\IMAP\HostConfig\GmailHostConfigConfig;
use App\Modules\IMAP\IMAP;

class GmailIMAP
{
    private $username;
    private $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function __invoke() {
        return new IMAP(new GmailHostConfigConfig(), $this->username, $this->password);
    }
}
