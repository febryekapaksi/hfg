<?php
$content = file_get_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/form_payment_new.php');

// Replace form_payment_new variables with view_payment_new variables
$content = preg_replace('/date\(\'Y-m-d\'\)/', '$results[\'result_header\']->tgl_bayar', $content);
$content = str_replace('<textarea name="keterangan_pembayaran"', '<textarea name="keterangan_pembayaran" readonly', $content);
$content = preg_replace('/<\/textarea>/', '<?= $results[\'result_header\']->keterangan_pembayaran ?></textarea>', $content);

// Set bank value
$content = str_replace('<option value="\' . $item_bank->no_perkiraan . \'">\' . $item_bank->no_perkiraan', '<option value="\' . $item_bank->no_perkiraan . \'" \' . ($item_bank->no_perkiraan == $results[\'result_header\']->coa_bank ? \'selected\' : \'\') . \'>\' . $item_bank->no_perkiraan', $content);
$content = str_replace('class="form-control form-control-sm bank"', 'class="form-control form-control-sm bank" disabled', $content);

// Set mata uang
$content = str_replace('<option value="\' . $item_mata_uang->kode . \'">\'', '<option value="\' . $item_mata_uang->kode . \'" \' . ($item_mata_uang->kode == $results[\'result_header\']->mata_uang ? \'selected\' : \'\') . \'>\'', $content);
$content = str_replace('class="form-control form-control-sm mata_uang"', 'class="form-control form-control-sm mata_uang" disabled', $content);

// Set payment_bank
$content = str_replace('value="0"', 'value="<?= number_format($results[\'result_header\']->payment_bank, 2) ?>" readonly', $content);

// Set kurs_payment
$content = str_replace('value="" disabled', 'value="<?= number_format($results[\'result_header\']->kurs_payment, 2) ?>" readonly disabled', $content);

// Bank charge
$content = str_replace('value="<?= $ttl_bank_charge ?>"', 'value="<?= number_format($results[\'bank_charge\'], 2) ?>" readonly', $content);
$content = str_replace('class="form-control form-control-sm text-right auto_num bank_charge"', 'class="form-control form-control-sm text-right auto_num bank_charge" readonly', $content);

// Make other inputs readonly
$content = str_replace('class="form-control form-control-sm text-right auto_num nilai_pph', 'class="form-control form-control-sm text-right auto_num nilai_pph" readonly', $content);
$content = str_replace('class="form-control form-control-sm text-right auto_num change_nilai_ppn nilai_ppn', 'class="form-control form-control-sm text-right auto_num change_nilai_ppn nilai_ppn" readonly', $content);
$content = str_replace('class="form-control form-control-sm chosen"', 'class="form-control form-control-sm chosen" disabled', $content);

// Hide submit button and upload doc
$content = preg_replace('/<button type="submit".*?<\/button>/s', '', $content);
$content = preg_replace('/<input type="file".*?>/s', '<?php if(isset($results[\'result_header\']->link_doc) && $results[\'result_header\']->link_doc != \'\') { ?><a href="<?= base_url(\'assets/expense/\' . $results[\'result_header\']->link_doc) ?>" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-download"></i> Download Dokumen</a><?php } ?>', $content);

// Remove form tags
$content = str_replace('<form action="" id="frm-data" enctype="multipart/form-data">', '', $content);
$content = str_replace('</form>', '', $content);
$content = str_replace('id="tgl_bayar"', 'id="tgl_bayar" readonly disabled', $content);

file_put_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php', $content);
echo "Done";
