# partition_rotator
rotate table partition for MySQL
## Getting Started


### Daily rotate mode
First you must create the table

```sql
CREATE TABLE table_rotate_daily (
dt datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1
PARTITION BY RANGE (TO_DAYS(dt))
(PARTITION start VALUES LESS THAN (0) ,
PARTITION from20201002 VALUES LESS THAN (738064) ,
PARTITION from20201003 VALUES LESS THAN (738065) ,
PARTITION from20201004 VALUES LESS THAN (738066) ,
PARTITION from20201005 VALUES LESS THAN (738067) ,
PARTITION from20201006 VALUES LESS THAN (738068) ,
PARTITION from20201007 VALUES LESS THAN (738069) ,
PARTITION future VALUES LESS THAN MAXVALUE ) 
```
Then you can start rotating the table
```php
<?php
use Jinraynor1\PartitionRotator\RotateModeDaily;
use Jinraynor1\PartitionRotator\PartitionRotator;

$username = "root";
$password = "";
$db = new \PDO("mysql:host=localhost;dbname=testdb",$username,$password);
$database_name ="testdb";
$table_name = "table_rotate_daily";
$oldest_partition = new DateTime("1 week ago");
$newest_partition = new DateTime("today");
$rotate_mode = new RotateModeDaily();
            
$partition = new PartitionRotator($db, $database_name. $table_name. $oldest_partition, $newest_partition);
$partition->rotate();
```        
### Monthly rotate mode
```sql
CREATE TABLE table_rotate_monthly (
dt DATETIME NOT NULL
) ENGINE=MYISAM DEFAULT CHARSET=latin1
PARTITION BY RANGE (TO_DAYS(dt))
(PARTITION start VALUES LESS THAN (0) ,
PARTITION from202010 VALUES LESS THAN (738063) ,
PARTITION from202011 VALUES LESS THAN (738094) ,
PARTITION from202012 VALUES LESS THAN (738124) ,
PARTITION from202107 VALUES LESS THAN (738336) ,
PARTITION future VALUES LESS THAN MAXVALUE )
```

```php
use Jinraynor1\PartitionRotator\RotateMode;
use Jinraynor1\PartitionRotator\PartitionRotator;use Jinraynor1\PartitionRotator\RotateModeMonthly;

$username = "root";
$password = "";
$db = new \PDO("mysql:host=localhost;dbname=testdb",$username,$password);
$database_name ="testdb";
$table_name = "table_rotate_monthly";
$oldest_partition = new DateTime("1 year ago");
$newest_partition = new DateTime("today");
$rotate_mode = new RotateModeMonthly();
            
$partition = new PartitionRotator($db, $database_name. $table_name. $oldest_partition, $newest_partition);
$partition->rotate();
```        