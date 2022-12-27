<?php
require_once __DIR__ . '/AbstractPartitionTest.php';

use Jinraynor1\PartitionRotator\PartitionRotator;
use Jinraynor1\PartitionRotator\RotateModeHourly;

class PartitionHourlyTest extends AbstractPartitionTest
{

    public function setUp()
    {
        parent::setUp();

        $this->partition = new PartitionRotator(self::$pdo, $GLOBALS["DB_NAME"] , "test_rotate_hourly",
            new DateTime("2020-10-03 23:00:00"), new DateTime("2020-10-04 04:00:00"), new RotateModeHourly() );


        $this->initTable();
    }

    public function initTable()
    {
        self::$pdo->query("DROP TABLE IF EXISTS test_rotate_hourly");

        self::$pdo->query("
        CREATE TABLE `test_rotate_hourly` (
  `dt` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1
 PARTITION BY RANGE (TO_SECONDS(dt))
(PARTITION `start` VALUES LESS THAN (0) ,
PARTITION from2020100322 VALUES LESS THAN (TO_SECONDS('2020-10-03 23:00:00')) ,
PARTITION from2020100323 VALUES LESS THAN (TO_SECONDS('2020-10-04 00:00:00')) ,
PARTITION from2020100400 VALUES LESS THAN (TO_SECONDS('2020-10-04 01:00:00')) ,
PARTITION from2020100401 VALUES LESS THAN (TO_SECONDS('2020-10-04 02:00:00')) ,
PARTITION from2020100402 VALUES LESS THAN (TO_SECONDS('2020-10-04 03:00:00')) ,
PARTITION from2020100403 VALUES LESS THAN (TO_SECONDS('2020-10-04 04:00:00')) ,
PARTITION future VALUES LESS THAN MAXVALUE ) 
");


    }


    public function testGetPartitions()
    {
        $partitions = $this->partition->getPartitions();
        $this->assertNotEmpty($partitions);
        $this->assertEquals("from2020100322",$partitions[0]->getName());
        $this->assertEquals("from2020100403",$partitions[count($partitions)-1]->getName());
    }

    public function testRemovePartition()
    {
        $this->partition->removeOldPartition();
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from2020100323",$partitions[0]->getName());
        $this->assertCount(5, $partitions);
    }

    public function testAddPartition()
    {
        $this->partition->addNewPartition(new DateTime("2020-10-04 05:00:00"));
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from2020100405",$partitions[count($partitions)-1]->getName());
        $this->assertCount(7, $partitions);
    }

    public function testRotatePartitions()
    {
        $this->partition->rotate();
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from2020100323",$partitions[0]->getName());
        $this->assertEquals("from2020100404",$partitions[count($partitions)-1]->getName());
        $this->assertCount(6, $partitions);
    }

    public function testPartitionPrunning(){

        self::$pdo->query("INSERT INTO test_rotate_hourly(dt) VALUES
        ('2020-10-03 21:29:00'),
        ('2020-10-03 22:10:00'),
        ('2020-10-03 23:30:00'),('2020-10-04 00:10:00'),('2020-10-04 01:48:00'),
        ('2020-10-04 02:33:00')
        ");

        $data = self::$pdo->query(
            "EXPLAIN PARTITIONS SELECT * FROM test_rotate_hourly 
            WHERE dt BETWEEN '2020-10-03 21:19:00' AND '2020-10-03 21:39:00'")->fetchColumn(3);

        $this->assertEquals("from2020100322",$data);

        $data = self::$pdo->query(
            "EXPLAIN PARTITIONS SELECT * FROM test_rotate_hourly 
            WHERE dt BETWEEN '2020-10-03 23:00:00' AND '2020-10-04 01:00:00'")->fetchColumn(3);

        $this->assertEquals("from2020100323,from2020100400,from2020100401",$data);

    }

    public function testRangePartitions()
    {

        $partitions = $this->partition->getPartitions();
        $oldPartitions = count($partitions);

        $this->partition->addRangePartitions(
            new DateTime("2020-10-04 04:00:00"),
            new DateInterval("PT1H"),
            new DateTime("2020-10-04 06:00:00")
        );

        $partitions = $this->partition->getPartitions();

        $totalPartitions = $oldPartitions + 2;
        $this->assertCount($totalPartitions ,$partitions);

        $this->assertEquals("from2020100405",$partitions[$totalPartitions-1]->partition_name);
        $this->assertEquals("from2020100404",$partitions[$totalPartitions-2]->partition_name);
    }

}