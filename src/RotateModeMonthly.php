<?php


namespace Jinraynor1\PartitionRotator;


class RotateModeMonthly implements RotateModeInterface
{

    function getPartitionName(\DateTime $dateTime)
    {
        return $dateTime->format("Ym");
    }

    function getPartitionValue(\DateTime $dateTime)
    {
        $_dateTime = clone($dateTime);
        $_dateTime->modify('first day of next month');

        return DateHelper::to_days($_dateTime->getTimestamp());
    }
}