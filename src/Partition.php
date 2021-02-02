<?php


namespace Jinraynor1\PartitionRotator;


class Partition
{
    public $partition_name;
    public $partition_description;

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getDate(){
        $timestamp = DateHelper::from_days($this->partition_description);
        return new \DateTime($timestamp);
    }

    public function getName()
    {
        return $this->partition_name;
    }
}