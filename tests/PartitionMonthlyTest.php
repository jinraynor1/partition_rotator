<?php
require_once __DIR__ . '/AbstractPartitionTest.php';

use Jinraynor1\PartitionRotator\PartitionRotator;
use Jinraynor1\PartitionRotator\RotateModeMonthly;

class PartitionMonthlyTest extends AbstractPartitionTest
{

    public function setUp()
    {
        parent::setUp();

        $this->partition = new PartitionRotator(self::$pdo, $GLOBALS["DB_NAME"] , "test_rotate_monthly",
            new DateTime("2020-11-01"), new DateTime("2021-01-01"), new RotateModeMonthly() );

        $this->initTable();
    }

    public function initTable()
    {
        self::$pdo->query("DROP TABLE IF EXISTS test_rotate_monthly");

        self::$pdo->query("
        CREATE TABLE `test_rotate_monthly` (
  `dt` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1
 PARTITION BY RANGE (TO_DAYS(dt))
(PARTITION `start` VALUES LESS THAN (0) ,
 PARTITION from202009 VALUES LESS THAN (TO_DAYS('2020-10-01')),
 PARTITION from202010 VALUES LESS THAN (TO_DAYS('2020-11-01')),
 PARTITION from202011 VALUES LESS THAN (TO_DAYS('2020-12-01')),
 PARTITION from202012 VALUES LESS THAN (TO_DAYS('2021-01-01')),
 PARTITION future VALUES LESS THAN MAXVALUE ) 
        ");


    }


    public function testGetPartitions()
    {
        $partitions = $this->partition->getPartitions();
        $this->assertNotEmpty($partitions);
        $this->assertEquals("from202009",$partitions[0]->getName());
        $this->assertEquals("2020-10-01",$partitions[0]->getDate()->format("Y-m-d"));


        $this->assertEquals("from202012",$partitions[count($partitions)-1]->getName());
        $this->assertEquals("2021-01-01",$partitions[count($partitions)-1]->getDate()->format("Y-m-d"));
    }

    public function testRemovePartition()
    {
        $this->partition->removeOldPartition();
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from202010",$partitions[0]->getName());
        $this->assertCount(3, $partitions);
    }

    public function testAddPartition()
    {
        $this->partition->addNewPartition(new DateTime("2021-02-01"));
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from202102",$partitions[count($partitions)-1]->getName());
        $this->assertCount(5, $partitions);
    }

    public function testRotatePartitions()
    {
        $this->partition->rotate();

        // multiple rotate calls do not affect result
        $this->partition->rotate();
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from202010",$partitions[0]->getName());
        $this->assertEquals("from202101",$partitions[count($partitions)-1]->getName());
        $this->assertCount(4, $partitions);


    }
}