<?php


namespace Jinraynor1\PartitionRotator;


class PartitionRotator
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


    public function __construct(\PDO $db, $database_name, $table_name,
                                \DateTime $old_partition_time, \DateTime $new_partition_time,
                                RotateModeInterface $rotate_mode)
    {
        $this->db = $db;
        $this->database_name = $database_name;
        $this->table_name = $table_name;
        $this->old_partition_time = $old_partition_time;
        $this->new_partition_time = $new_partition_time;
        $this->rotate_mode = $rotate_mode;
    }


    public function removeOldPartition()
    {

        $partition_list = $this->getPartitions();

        if (!$partition_list)
            return true;

            foreach ($partition_list as $partition) {
                if($partition->getDate() < $this->old_partition_time ){

                $sql = sprintf("ALTER TABLE `%s`.`%s` DROP  PARTITION %s",
                    $this->database_name,$this->table_name,
                    $partition->getName());
                $this->db->query($sql);
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

        return $this->db->query($sql)->fetchAll(\PDO::FETCH_CLASS,'Jinraynor1\\PartitionRotator\\Partition');

    }

    private function partitionExists(\DateTime $partition_time)
    {
        $sql = sprintf("SELECT partition_name FROM INFORMATION_SCHEMA.PARTITIONS 
                WHERE table_schema='%s'
                AND table_name='%s'
                AND partition_name='%s'",
        $this->database_name,
            $this->table_name,
            "from".$this->rotate_mode->getPartitionName($partition_time)
        );
        return $this->db->query($sql)->fetchColumn() ? true : false;
    }


    public function addNewPartition(\DateTime $partition_date)
    {
        if ($this->partitionExists($partition_date)) {
            return true;
        }

        $sql = sprintf(
            "ALTER TABLE  `%s`.`%s`  REORGANIZE PARTITION future INTO(
                PARTITION %s VALUES LESS THAN (%s), PARTITION future VALUES LESS THAN MAXVALUE )",
            $this->database_name,$this->table_name,
            "from" . $this->rotate_mode->getPartitionName($partition_date),
            $this->rotate_mode->getPartitionValue($partition_date)

        );
        $this->db->query($sql);

    }

    public function rotate()
    {
        $this->removeOldPartition();
        $this->addNewPartition($this->new_partition_time);
    }
}