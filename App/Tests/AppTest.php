<?php

namespace Test;

use App\App;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    public function testBasic()
    {
        $this->assertTrue(method_exists(App::class, 'run'),
            'Class does not have method run'
        );
    }
}
