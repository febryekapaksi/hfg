<?php
$content = file_get_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php');

// Find the start of script tag and remove unnecessary calculations
// We only need basic select2 init and number format, maybe nothing else
$start_script = strpos($content, '<script type="text/javascript">');

// We can simply remove everything inside the script except a few document.ready basics, or just clear the script block entirely
// Let's replace the script tag completely
$script_content = <<<HTML
<script type="text/javascript">
$(document).ready(function() {
um').autoNumeric();
});
</script>
HTML;

$new_content = substr($content, 0, $start_script) . $script_content;

file_put_contents('/var/www/middle74/hfg/application/modules/pembayaran_material/views/view_payment_new.php', $new_content);
echo "Done strip js";
