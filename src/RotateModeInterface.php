<?php


namespace Jinraynor1\PartitionRotator;


interface RotateModeInterface
{

    function getPartitionName(\DateTime $dateTime);
    function getPartitionValue(\DateTime $dateTime);


}