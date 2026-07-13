<?php require_once 'index.php'; $ci =& get_instance(); $query = $ci->db->query('DESCRIBE request_payment'); print_r($query->result()); ?>
