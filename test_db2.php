<?php
define('BASEPATH', '1');
require '/var/www/middle74/hfg/application/config/database.php';
$mysqli = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

$res = $mysqli->query("DESC payment_approve");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
