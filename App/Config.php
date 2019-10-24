<?php

namespace App;

use App\Modules\IMAP\Interfaces\hasIMAPHost;
use App\Modules\IMAP\Interfaces\IMAPCredentials;

class Config implements IMAPCredentials, hasIMAPHost
{
    private $config = [];

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getIMAPUser(): string
    {
        if (isset($this->config['IMAP']) && isset($this->config['IMAP']['username'])) {
            return (string)$this->config['IMAP']['username'];
        }
    }

    public function getIMAPPassword(): string
    {
        if (isset($this->config['IMAP']) && isset($this->config['IMAP']['password'])) {
            return (string)$this->config['IMAP']['password'];
        }
    }

    public function getIMAPHost(): string
    {
        if (isset($this->config['IMAP']) && isset($this->config['IMAP']['host'])) {
            return (string)$this->config['IMAP']['host'];
        }
    }

    public function getPathToSaveAttachment(): string
    {
        if (isset($this->config['path_to_save_attachment'])) {
            return (string)$this->config['path_to_save_attachment'];
        }
    }

    public function getHost(): string
    {
        if (isset($this->config['host'])) {
            return (string)$this->config['host'];
        }
    }
}
