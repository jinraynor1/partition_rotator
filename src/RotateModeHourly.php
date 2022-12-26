<?php


namespace Jinraynor1\PartitionRotator;


class RotateModeHourly implements RotateModeInterface
{

    function getPartitionName(\DateTime $dateTime)
    {
        return $dateTime->format("YmdH");
    }

    function getPartitionValue(\DateTime $dateTime)
    {
        if ($dateTime->format("H:i:s") == "00:00:00") {
            $from = new \DateTime("0000-01-00 00:00:00");
        } else {
            $from = new \DateTime("0000-01-01 00:00:00");
        }

        $interval = $from->diff($dateTime);

        $secs = ($interval->days) * 24 * 60 * 60;

        $secs += $dateTime->format('H') * 60 * 60;
        $secs += $dateTime->format('i') * 60;
        $secs += (int)$dateTime->format('s');

        return $secs;
    }

    function getPartitionDate(Partition $partition)
    {
        return \DateTime::createFromFormat('YmdH', substr($partition->getName(), 4));
    }
}