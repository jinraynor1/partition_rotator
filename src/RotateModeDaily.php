<?php


namespace Jinraynor1\PartitionRotator;


class RotateModeDaily implements RotateModeInterface
{

    function getPartitionName(\DateTime $dateTime)
    {
        return $dateTime->format("Ymd");
    }

    function getPartitionValue(\DateTime $dateTime)
    {
        $_dateTime = clone($dateTime);
        $_dateTime->modify('+1 day');

        return DateHelper::to_days($_dateTime->getTimestamp());
    }

    function getPartitionDate(Partition $partition)
    {
       return $partition->getDate();
    }


}