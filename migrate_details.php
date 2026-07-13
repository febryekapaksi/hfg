<?php require_once 'index.php'; $ci =& get_instance(); print_r($ci->db->query('DESCRIBE payment_approve_details')->result()); ?>
