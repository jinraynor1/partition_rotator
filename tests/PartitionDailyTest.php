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
            new DateTime("2020-10-01"), new DateTime("2020-10-07"), new RotateModeDaily() );

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
(PARTITION `start` VALUES LESS THAN (0) ENGINE = MyISAM,
 PARTITION from20201001 VALUES LESS THAN (738063) ENGINE = MyISAM,
 PARTITION from20201002 VALUES LESS THAN (738064) ENGINE = MyISAM,
 PARTITION from20201003 VALUES LESS THAN (738065) ENGINE = MyISAM,
 PARTITION from20201004 VALUES LESS THAN (738066) ENGINE = MyISAM,
 PARTITION from20201005 VALUES LESS THAN (738067) ENGINE = MyISAM,
 PARTITION from20201006 VALUES LESS THAN (738068) ENGINE = MyISAM,
 PARTITION future VALUES LESS THAN MAXVALUE ENGINE = MyISAM) 
        ");


    }


    public function testGetPartitions()
    {
        $partitions = $this->partition->getPartitions();
        $this->assertNotEmpty($partitions);
        $this->assertEquals("2020-09-30",$partitions[0]->getDate()->format("Y-m-d"));
        $this->assertEquals("2020-10-05",$partitions[count($partitions)-1]->getDate()->format("Y-m-d"));
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
}