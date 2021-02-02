<?php


use Jinraynor1\PartitionRotator\RotateModeDaily;
use Jinraynor1\PartitionRotator\RotateModeMonthly;
use PHPUnit\Framework\TestCase;

class RotateModeDailyTest extends TestCase
{
    private $rotateMode;
    public function setUp()
    {
        $this->rotateMode = new RotateModeDaily();
    }
    public function testGetPartitionName()
    {
        $this->assertEquals("20210201",$this->rotateMode->getPartitionName(new DateTime("2021-02-01")));
        $this->assertEquals("20210202",$this->rotateMode->getPartitionName(new DateTime("2021-02-02")));
    }

    public function testGetPartitionValue()
    {

        $this->assertEquals(738188,$this->rotateMode->getPartitionValue(new DateTime("2021-02-01")));
        $this->assertEquals(738189, $this->rotateMode->getPartitionValue(new DateTime("2021-02-02")));
    }
}