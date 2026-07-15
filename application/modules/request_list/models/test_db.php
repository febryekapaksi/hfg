<?php
$mysqli = new mysqli("localhost", "hirobolt", "kduwh&*#&hhshd873j=", "db_hfg_dev", 8732);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("DESC tr_spk_material_detail");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
$mysqli->close();
