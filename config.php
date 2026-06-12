<?php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'gis_polygon';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) { die("Koneksi gagal: " . mysqli_connect_error()); }
?>
