<?php
$mysqli = new mysqli("localhost", "root", "", "db_hfg");
$result = $mysqli->query("SELECT id, no_coil, status FROM warehouse_stock_coil LIMIT 5");
while ($row = $result->fetch_assoc()) { print_r($row); }
