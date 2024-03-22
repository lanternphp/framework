<?php

namespace LanternTest\Unit;

use Lantern\Lantern;
use LanternTest\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ConfigurationTest extends TestCase
{
    #[Test]
    public function pathDirsTest()
    {
        $count = count(Lantern::pathDirs());

        Lantern::pathDirs([__DIR__]);

        $dirs = Lantern::pathDirs();

        $this->assertTrue(in_array(__DIR__, $dirs));
        $this->assertCount($count+1, $dirs);
    }
}