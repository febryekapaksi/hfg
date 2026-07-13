<?php
$_SERVER['SERVER_ADDR'] = '127.0.0.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
define('ENVIRONMENT', 'development');
require_once 'index.php';
$CI =& get_instance();
$db_acc = $CI->load->database('accounting', TRUE);
$query = $db_acc->query("SELECT field, field_no_reff FROM master_oto_jurnal_detail WHERE kode_master_jurnal = 'JV005'");
if($query) {
    print_r($query->result_array());
}
