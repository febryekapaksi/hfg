<?php
$content = file_get_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/controllers/Pembayaran_material.php');

$search = "->get_where('payment_approve_details', ['payment_id' => \$get_payment_header->id_payment])";
$replace = "->get_where('payment_approve_details', ['payment_id' => \$get_payment_header->no_doc])";

$content = str_replace($search, $replace, $content);

file_put_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/controllers/Pembayaran_material.php', $content);
echo "Done";
