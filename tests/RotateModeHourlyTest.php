<?php


use Jinraynor1\PartitionRotator\RotateModeHourly;
use PHPUnit\Framework\TestCase;

class RotateModeHourlyTest extends TestCase
{
    private $rotateMode;
    public function setUp()
    {
        $this->rotateMode = new RotateModeHourly();
    }
    public function testGetPartitionName()
    {
        $this->assertEquals("2021020100",$this->rotateMode->getPartitionName(new DateTime("2021-02-01 00:00:00")));
        $this->assertEquals("2021020213",$this->rotateMode->getPartitionName(new DateTime("2021-02-02 13:00:00")));
    }

    public function testGetPartitionValue()
    {
        $this->assertEquals(63779356800 + 3600,$this->rotateMode->getPartitionValue(new DateTime("2021-02-01 00:00:00")));
        $this->assertEquals(63779490000 + 3600, $this->rotateMode->getPartitionValue(new DateTime("2021-02-02 13:00:00")));
        $this->assertEquals(63781720203 + 3600,$this->rotateMode->getPartitionValue(new DateTime("2021-02-28 08:30:03")));
        $this->assertEquals(63779443199 + 3600,$this->rotateMode->getPartitionValue(new DateTime("2021-02-01 23:59:59")));
    }
}