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
        $_dateTime->modify('first day of this month');
        $_dateTime->modify('-1 day');

        return DateHelper::to_days($_dateTime->getTimestamp());
    }
}