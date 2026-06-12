<?php
include 'config.php';

$type   = $_POST['type']; // 'jalan' atau 'parsil'
$nama   = mysqli_real_escape_string($conn, $_POST['nama']);
$status = $_POST['status'];
$coords = $_POST['coords'];
$nilai  = $_POST['nilai'];

if ($type == 'jalan') {
    $sql = "INSERT INTO data_jalan (nama_jalan, status_jalan, geojson_data, panjang_meter) 
            VALUES ('$nama', '$status', '$coords', '$nilai')";
} else {
    $sql = "INSERT INTO data_parsil (nomor_parsil, status_sertifikat, geojson_data, luas_m2) 
            VALUES ('$nama', '$status', '$coords', '$nilai')";
}

if (mysqli_query($conn, $sql)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
}
?>