<?php


use Jinraynor1\PartitionRotator\RotateModeMonthly;
use PHPUnit\Framework\TestCase;

class RotateModeMonthlyTest extends TestCase
{
    private $rotateMode;
    public function setUp()
    {
        $this->rotateMode = new RotateModeMonthly();
    }
    public function testGetPartitionName()
    {
        $this->assertEquals("202102",$this->rotateMode->getPartitionName(new DateTime("2021-02-02")));
        $this->assertEquals("202101",$this->rotateMode->getPartitionName(new DateTime("2021-01-30")));
    }

    public function testGetPartitionValue()
    {
        $this->assertEquals(738215, $this->rotateMode->getPartitionValue(new DateTime("2021-02-02")));
        $this->assertEquals(738187,$this->rotateMode->getPartitionValue(new DateTime("2021-01-30")));
    }
}