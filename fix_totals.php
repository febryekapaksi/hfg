<?php
$content = file_get_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php');

$replacements = [
    '<?= number_format($total_payment, 2) ?>' => '<?= number_format($results[\'result_header\']->total_payment, 2) ?>',
    '<?= number_format($total_ppn, 2) ?>' => '<?= number_format($results[\'result_header\']->total_ppn, 2) ?>',
    '<?= number_format($total_pph, 2) ?>' => '<?= number_format($results[\'result_header\']->total_pph, 2) ?>',
    '<?= number_format($total_payment + $total_ppn - $total_pph + $ttl_bank_charge, 2) ?>' => '<?= number_format($results[\'result_header\']->payment_bank, 2) ?>'
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// Since payment_bank is exactly the grand total:
// Let's also check if total_payment_bank is printed anywhere else.

file_put_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php', $content);
echo "Done";
