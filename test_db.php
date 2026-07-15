<?php
$mysqli = new mysqli("localhost", "root", "", "hfg");
if ($mysqli->connect_errno) {
    // If root fails, try with actual DB config
    require '/var/www/middle74/hfg/application/config/database.php';
    $mysqli = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
}
$res = $mysqli->query("SELECT payment_id FROM payment_approve_details ORDER BY id DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    echo $row['payment_id'] . "\n";
}
echo "--- payment_approve ---\n";
$res2 = $mysqli->query("SELECT id, id_payment FROM payment_approve ORDER BY id DESC LIMIT 5");
while($row2 = $res2->fetch_assoc()) {
    echo $row2['id'] . " : " . $row2['id_payment'] . "\n";
}
