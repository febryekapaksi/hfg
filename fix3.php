<?php
$content = file_get_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/controllers/Pembayaran_material.php');
// Add get_payment_details
if (strpos($content, '$get_payment_details = []') === false) {
    $search = "\$bank_charge = 0;";
    $replace = "
                \$get_payment_details = [];
                if (\$get_payment_header) {
                        \$get_payment_details = \$this->db
                                ->get_where('payment_approve_details', ['payment_id' => \$get_payment_header->id_payment])
                                ->result();
                }
                \$bank_charge = 0;";
    $content = str_replace($search, $replace, $content);
}

// Update the result array
$content = preg_replace("/'result_payment'\s*=>\s*\\\$get_payment,/", "'result_payment' => \$get_payment_details,", $content);

file_put_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/controllers/Pembayaran_material.php', $content);
echo "Done";
