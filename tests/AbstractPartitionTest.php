<?php


use Jinraynor1\PartitionRotator\PartitionRotator;
use PHPUnit\Framework\TestCase;

abstract class AbstractPartitionTest extends TestCase
{
    /**
     * @var PDO
     */
    protected static $pdo;

    /**
     * @var PartitionRotator
     */
    protected $partition;

    public function setUp()
    {
        self::initDatabase();



    }

    public static function initDatabase()
    {
        $dsn = "mysql:host=" . $GLOBALS["DB_HOST"] . ";dbname=" . $GLOBALS["DB_NAME"];
        self::$pdo = new \PDO($dsn, $GLOBALS["DB_USER"], $GLOBALS["DB_PASS"], array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION

        ));
    }

}