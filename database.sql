DROP DATABASE IF EXISTS gis_polygon;
CREATE DATABASE gis_polygon;
USE gis_polygon;

-- Tabel 1: Manajemen Jalan (Line)
CREATE TABLE data_jalan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_jalan VARCHAR(255),
    status_jalan ENUM('Nasional', 'Provinsi', 'Kabupaten'),
    geojson_data TEXT,
    panjang_meter FLOAT
);

-- Tabel 2: Manajemen Parsil Tanah (Polygon)
CREATE TABLE data_parsil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_parsil VARCHAR(100),
    status_sertifikat ENUM('SHM', 'HGB', 'HGU', 'HP'),
    geojson_data TEXT,
    luas_m2 FLOAT
);