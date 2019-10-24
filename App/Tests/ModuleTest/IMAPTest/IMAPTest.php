<?php

namespace Test\IMAPTest;

use App\App;
use App\Config;
use App\Modules\IMAP\HostConfig\GmailHostConfig;
use App\Modules\IMAP\IMAP;
use PHPUnit\Framework\TestCase;

class IMAPTest extends TestCase
{
    public function testBasic()
    {
        $mailHostConfig = $this->createMock(GmailHostConfig::class);
        $mailHostConfig->method('getIMAPHost')->willReturn('GMAIL');
        $imapCredentials = $this->createMock(Config::class);

        $imap = new IMAP($mailHostConfig, $imapCredentials);

        $this->assertTrue(method_exists($imap, 'setCredentials'),
            'Class does not have method setCredentials'
        );
        $this->assertTrue(method_exists($imap, 'setHostConfig'),
            'Class does not have method setHostConfig'
        );
        $this->assertTrue(method_exists($imap, 'openConnection'),
            'Class does not have method openConnection'
        );
        $this->assertTrue(method_exists($imap, 'closeConnection'),
            'Class does not have method closeConnection'
        );

        $this->assertNull($imap->setCredentials($imapCredentials));
        $this->assertNull($imap->setHostConfig($mailHostConfig));
        $this->assertNull($imap->closeConnection());
    }
}
