<?php
$content = file_get_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php');

$content = str_replace('$item->tipe', '$results[\'result_header\']->tipe', $content);
$content = str_replace('$item->id_supplier', '$results[\'result_header\']->id_supplier', $content);
$content = str_replace('$item->nm_supplier', '$results[\'result_header\']->nm_supplier', $content);
$content = str_replace('$item->ids', '$item->id', $content);

// Change jumlah to nilai_invoice
$content = str_replace('$item->jumlah', '$item->nilai_invoice', $content);
$content = preg_replace('/\$item->total_pph \?\? 0/', '($item->nilai_pph ?? 0)', $content);
$content = preg_replace('/\$item->total_ppn \?\? 0/', '($item->nilai_ppn ?? 0)', $content);
$content = str_replace('$item->admin_bank', '0', $content); // Admin bank is in header, not detail

file_put_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php', $content);
echo "Done";
