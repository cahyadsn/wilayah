<?php
$start = microtime(true);
require_once 'apps/inc/db.php';
$query=$db->prepare("SELECT kode,nama FROM wilayah WHERE CHAR_LENGTH(kode)=2 ORDER BY nama");
$query->execute();
while ($data=$query->fetchObject());
$end = microtime(true);
echo "Time: " . ($end - $start) . "\n";
