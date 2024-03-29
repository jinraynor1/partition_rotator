<?php
require_once __DIR__ . '/AbstractPartitionTest.php';

use Jinraynor1\PartitionRotator\PartitionRotator;
use Jinraynor1\PartitionRotator\RotateModeDaily;

class PartitionDailyTest extends AbstractPartitionTest
{

    public function setUp()
    {
        parent::setUp();

        $this->partition = new PartitionRotator(self::$pdo, $GLOBALS["DB_NAME"] , "test_rotate_daily",
            new DateTime("2020-10-03"), new DateTime("2020-10-07"), new RotateModeDaily() );

        $this->initTable();
    }

    public function initTable()
    {
        self::$pdo->query("DROP TABLE IF EXISTS test_rotate_daily");

        self::$pdo->query("
        CREATE TABLE `test_rotate_daily` (
  `dt` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1
 PARTITION BY RANGE (TO_DAYS(dt))
(PARTITION `start` VALUES LESS THAN (0) ,
PARTITION from20201001 VALUES LESS THAN (TO_DAYS('2020-10-02')) ,
PARTITION from20201002 VALUES LESS THAN (TO_DAYS('2020-10-03')) ,
PARTITION from20201003 VALUES LESS THAN (TO_DAYS('2020-10-04')) ,
PARTITION from20201004 VALUES LESS THAN (TO_DAYS('2020-10-05')) ,
PARTITION from20201005 VALUES LESS THAN (TO_DAYS('2020-10-06')) ,
PARTITION from20201006 VALUES LESS THAN (TO_DAYS('2020-10-07')) ,
PARTITION future VALUES LESS THAN MAXVALUE ) 
");


    }


    public function testGetPartitions()
    {
        $partitions = $this->partition->getPartitions();
        $this->assertNotEmpty($partitions);
        $this->assertEquals("2020-10-02",$partitions[0]->getDate()->format("Y-m-d"));
        $this->assertEquals("2020-10-07",$partitions[count($partitions)-1]->getDate()->format("Y-m-d"));
    }

    public function testRemovePartition()
    {
        $this->partition->removeOldPartition();
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from20201002",$partitions[0]->getName());
        $this->assertCount(5, $partitions);
    }

    public function testAddPartition()
    {
        $this->partition->addNewPartition(new DateTime("2020-10-07"));
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from20201007",$partitions[count($partitions)-1]->getName());
        $this->assertCount(7, $partitions);
    }

    public function testRotatePartitions()
    {
        $this->partition->rotate();
        $partitions = $this->partition->getPartitions();
        $this->assertEquals("from20201002",$partitions[0]->getName());
        $this->assertEquals("from20201007",$partitions[count($partitions)-1]->getName());
        $this->assertCount(6, $partitions);
    }

    public function testPartitionPrunning(){

        self::$pdo->query("INSERT INTO test_rotate_daily(dt) VALUES
        ('2020-09-30 23:25:00'),
        ('2020-10-01 21:29:00'),
        ('2020-10-02 22:10:00'),
        ('2020-10-03 23:30:00'),
        ('2020-10-04 00:10:00'),
        ('2020-10-05 01:48:00')
        
        ");

        $data = self::$pdo->query(
            "EXPLAIN PARTITIONS SELECT * FROM test_rotate_daily 
            WHERE dt BETWEEN '2020-10-03 23:09:00' AND '2020-10-03 23:39:00'")->fetchColumn(3);

        $this->assertEquals("from20201003",$data);

        $data = self::$pdo->query(
            "EXPLAIN PARTITIONS SELECT * FROM test_rotate_daily 
            WHERE dt BETWEEN '2020-10-03 23:00:00' AND '2020-10-04 01:00:00'")->fetchColumn(3);

        $this->assertEquals("from20201003,from20201004",$data);

    }

    public function testRangePartitions()
    {

        $partitions = $this->partition->getPartitions();
        $oldPartitions = count($partitions);

        $this->partition->addRangePartitions(
            new DateTime("2020-10-07 00:00:00"),
            new DateInterval("P1D"),
            new DateTime("2020-10-09 00:00:00")
        );

        $partitions = $this->partition->getPartitions();

        $totalPartitions = $oldPartitions + 2;
        $this->assertCount($totalPartitions ,$partitions);

        $this->assertEquals("from20201008",$partitions[$totalPartitions-1]->partition_name);
        $this->assertEquals("from20201007",$partitions[$totalPartitions-2]->partition_name);
    }

}