<?php
$content = file_get_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php');

$search = "foreach (\$results['result_payment'] as \$item_check) {
        // Cek status LOI dari tr_purchase_order
        if (in_array(\$item_check->tipe, ['invoice_dp', 'invoice_import', 'invoice_local'])) {";
$replace = "foreach (\$results['result_payment'] as \$item_check) {
        // Cek status LOI dari tr_purchase_order
        if (in_array(\$results['result_header']->tipe, ['invoice_dp', 'invoice_import', 'invoice_local'])) {";

$content = str_replace($search, $replace, $content);

// Also fix fallback
$search2 = "if (\$item_check->tipe === 'invoice_import') {";
$replace2 = "if (\$results['result_header']->tipe === 'invoice_import') {";
$content = str_replace($search2, $replace2, $content);

file_put_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php', $content);
echo "Done";
