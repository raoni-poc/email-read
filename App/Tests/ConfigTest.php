<?php

namespace Test;

use App\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testBasic()
    {
        $arr = [
            'IMAP' => [
                'host' => 'GMAIL',
                'username' => 'raoniforapp@gmail.com',
                'password' => '123456',
            ],
            'path_to_save_attachment' => 'public/attachment_storage',
            'host' => 'http://localhost:8000/',
        ];
        $config = new Config();
        $this->assertNull($config->setConfig($arr));
        $this->assertSame('GMAIL', $config->getIMAPHost());
        $this->assertSame($arr, $config->getConfig());
        $this->assertSame('raoniforapp@gmail.com', $config->getIMAPUser());
        $this->assertSame('123456', $config->getIMAPPassword());
        $this->assertSame('public/attachment_storage', $config->getPathToSaveAttachment());
        $this->assertSame('http://localhost:8000/', $config->getHost());
    }
}
