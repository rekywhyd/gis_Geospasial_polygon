<?php
$conn = mysqli_connect("localhost", "root", "", "gis_polygon");
if (!$conn) { die("Koneksi gagal: " . mysqli_connect_error()); }
?>