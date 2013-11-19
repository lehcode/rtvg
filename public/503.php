<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Retry-After: Wed, 3 Apr 2013 23:01:00 GMT');
$d = date("D, j M Y H:i:s", strtotime('+1 month'));
header('Retry-After: '.$d.' GMT');
?>
<h1>Ремонт</h1>


