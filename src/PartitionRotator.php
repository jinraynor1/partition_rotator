<?php


namespace Jinraynor1\PartitionRotator;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PartitionRotator implements LoggerAwareInterface
{
    /**
     * @var \PDO
     */
    private $db;
    private $database_name;
    private $table_name;
    /**
     * @var \DateTime
     */
    private $old_partition_time;
    /**
     * @var \DateTime
     */
    private $new_partition_time;

    /**
     * @var RotateModeInterface
     */
    private $rotate_mode;

    private $logger;


    public function __construct(\PDO $db, $database_name, $table_name,
                                \DateTime $old_partition_time, \DateTime $new_partition_time,
                                RotateModeInterface $rotate_mode, LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->database_name = $database_name;
        $this->table_name = $table_name;
        $this->old_partition_time = $old_partition_time;
        $this->new_partition_time = $new_partition_time;
        $this->rotate_mode = $rotate_mode;
        $this->logger = $logger;

        if (is_null($this->logger)) {
            $this->logger = new NullLogger();
        }
    }


    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    public function removeOldPartition()
    {

        $partition_list = $this->getPartitions();

        if (!$partition_list) {
            $this->logger->info("no partitions found in removal");
            return true;
        }
        foreach ($partition_list as $partition) {

            if ($partition->getDate() < $this->old_partition_time) {
                $this->logger->info(sprintf("attempting to remove partition %s ", $partition->getName()));

                $sql = sprintf("ALTER TABLE `%s`.`%s` DROP  PARTITION %s",
                    $this->database_name, $this->table_name,
                    $partition->getName());
                if ($this->db->query($sql)) {
                    $this->logger->info(sprintf("partition %s successfully removed", $partition->getName()));
                }else{
                    $this->logger->error(sprintf("partition %s was not removed", $partition->getName()));
                }
            }
        }
        return true;

    }

    /**
     * @return Partition[]
     */
    public function getPartitions()
    {
        $sql = "SELECT partition_name, partition_description FROM INFORMATION_SCHEMA.PARTITIONS 
                WHERE table_schema='$this->database_name'
                AND table_name='$this->table_name'
                AND partition_name NOT IN ('start','future')
                ";

        return $this->db->query($sql)->fetchAll(\PDO::FETCH_CLASS, 'Jinraynor1\\PartitionRotator\\Partition');

    }

    /**
     * @param \DateTime $partition_time
     * @return Partition|bool
     */
    private function partitionExists(\DateTime $partition_time)
    {
        $sql = sprintf("SELECT partition_name,partition_description FROM INFORMATION_SCHEMA.PARTITIONS 
                WHERE table_schema='%s'
                AND table_name='%s'
                AND partition_name='%s'",
            $this->database_name,
            $this->table_name,
            "from" . $this->rotate_mode->getPartitionName($partition_time)
        );
      return $this->db->query($sql)->fetchObject('Jinraynor1\\PartitionRotator\\Partition');

    }


    public function addNewPartition(\DateTime $partition_date)
    {
        $this->logger->info(sprintf("attempting to add new partition for date %s", $partition_date->format("Y-m-d")));
        $partition_exists = $this->partitionExists($partition_date);

        if ($partition_exists) {
            $this->logger->info(sprintf("partition %s already exists",$partition_exists->getName()));
            return true;
        }
        $partition_name = $this->rotate_mode->getPartitionName($partition_date);
        $partition_value = $this->rotate_mode->getPartitionValue($partition_date);

        $sql = sprintf(
            "ALTER TABLE  `%s`.`%s`  REORGANIZE PARTITION future INTO(
                PARTITION %s VALUES LESS THAN (%s), PARTITION future VALUES LESS THAN MAXVALUE )",
            $this->database_name, $this->table_name,
            "from" . $partition_name,
            $partition_value

        );

        if($this->db->query($sql)){
            $this->logger->info(sprintf("partition %s was successfully created", $partition_name));
        }else{
            $this->logger->error(sprintf("partition %s was not created", $partition_name));
        }

    }

    public function rotate()
    {
        $this->removeOldPartition();
        $this->addNewPartition($this->new_partition_time);
    }
}