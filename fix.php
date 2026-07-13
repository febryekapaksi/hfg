<?php
$content = file_get_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/controllers/Pembayaran_material.php');
// We need to modify view_payment_new to fetch details
$search = "                \$get_payment_header = \$this->db
                        ->select('a.*')
                        ->from('payment_approve a')
                        ->where_in('a.id', \$id_payment)
                        ->group_by('a.id_payment')
                        ->get()
                        ->row();";
$replace = "                \$get_payment_header = \$this->db
                        ->select('a.*')
                        ->from('payment_approve a')
                        ->where_in('a.id', \$id_payment)
                        ->group_by('a.id_payment')
                        ->get()
                        ->row();

                // Fetch details
                \$get_payment_details = [];
                if (\$get_payment_header) {
                        \$get_payment_details = \$this->db
                                ->get_where('payment_approve_details', ['payment_id' => \$get_payment_header->id_payment])
                                ->result();
                }";
$content = str_replace($search, $replace, $content);

// Update data array
$search2 = "                        'result_payment' => \$get_payment,";
$replace2 = "                        'result_payment' => \$get_payment_details,";
$content = str_replace($search2, $replace2, $content);

file_put_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/controllers/Pembayaran_material.php', $content);
echo "Done";
