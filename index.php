<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>WebGIS Jalan & Parsil</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --primary-navy: #0F2027; /* Gelap & Elegan */
            --secondary-navy: #203A43;
            --accent-blue: #2C5364;
            --bg-color: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-main: #1E293B;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            background: var(--bg-color); 
            color: var(--text-main);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-navy), var(--secondary-navy));
            color: white;
            padding: 20px 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .control-panel, .panel, .table-container { 
            background: var(--card-bg); 
            padding: 25px 30px; 
            border-radius: 16px; 
            margin-bottom: 25px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex; 
            gap: 20px; 
            align-items: center; 
            flex-wrap: wrap;
        }

        .control-panel:hover, .panel:hover, .table-container:hover {
            box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-width: 200px;
        }

        .input-group strong {
            font-size: 14px;
            color: var(--secondary-navy);
            font-weight: 600;
        }

        input, select { 
            padding: 12px 16px; 
            border: 1px solid var(--border-color); 
            border-radius: 8px; 
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--text-main);
            background-color: var(--bg-color);
            transition: all 0.3s ease;
            outline: none;
            width: 100%;
            box-sizing: border-box;
        }

        input:focus, select:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(44, 83, 100, 0.15);
            background-color: #FFF;
        }

        #map { 
            height: 600px; 
            width: 100%; 
            border-radius: 16px; 
            border: none; 
            box-shadow: 0 10px 25px rgba(15, 32, 39, 0.1); 
            z-index: 1;
            margin-bottom: 25px;
        }

        /* Leaflet Overrides */
        .leaflet-control-layers, .leaflet-bar {
            border: none !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
            border-radius: 12px !important;
        }
        .leaflet-control-layers { padding: 8px !important; font-family: 'Inter', sans-serif; }
        .leaflet-bar { overflow: hidden; }
        .leaflet-bar a {
            border-bottom: 1px solid #eee !important;
            color: var(--primary-navy) !important;
            width: 32px !important;
            height: 32px !important;
            line-height: 32px !important;
        }
        .leaflet-bar a:hover {
            background-color: #f8f9fa !important;
            color: var(--accent-blue) !important;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .leaflet-popup-content { font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.5; }
        .leaflet-popup-content b { color: var(--primary-navy); font-size: 15px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>🗺️ Manajemen Data Geospasial (Polygon & Polyline)</h2>
    </div>

    <div class="container">
        <div class="control-panel">
            <div class="input-group">
                <strong>Mode Operasi:</strong>
                <select id="mode_input">
                    <option value="jalan">Manajemen Jalan (Line)</option>
                    <option value="parsil">Parsil Tanah (Polygon)</option>
                </select>
            </div>
            <div class="input-group">
                <strong>Identitas Data:</strong>
                <input type="text" id="nama_input" placeholder="Masukkan Nama Jalan / No Parsil">
            </div>
            <div class="input-group">
                <strong>Status Kepemilikan/Jalan:</strong>
                <select id="status_input"></select>
            </div>
        </div>

        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

    <script>
        var map = L.map('map').setView([-0.02, 109.34], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Konfigurasi Status & Warna
        const config = {
            jalan: {
                status: ['Nasional', 'Provinsi', 'Kabupaten'],
                warna: { 'Nasional': 'red', 'Provinsi': 'blue', 'Kabupaten': 'green' }
            },
            parsil: {
                status: ['SHM', 'HGB', 'HGU', 'HP'],
                warna: { 'SHM': '#f1c40f', 'HGB': '#9b59b6', 'HGU': '#e67e22', 'HP': '#1abc9c' }
            }
        };

        // Update Dropdown Status Otomatis
        const modeSelect = document.getElementById('mode_input');
        const statusSelect = document.getElementById('status_input');

        function updateStatusOptions() {
            const mode = modeSelect.value;
            statusSelect.innerHTML = config[mode].status.map(s => `<option value="${s}">${s}</option>`).join('');
        }
        modeSelect.onchange = updateStatusOptions;
        updateStatusOptions();

        // Fitur Drawing
        var drawnItems = new L.FeatureGroup().addTo(map);
        var drawControl = new L.Control.Draw({
            draw: { circle: false, marker: false, circlemarker: false, rectangle: true, polyline: true, polygon: true },
            edit: { featureGroup: drawnItems }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (e) {
            var layer = e.layer;
            var mode = modeSelect.value;
            var nama = document.getElementById('nama_input').value;
            var status = statusSelect.value;

            if(!nama) return alert("Isi Nama/Nomor dulu!");

            let nilaiHitung = 0;
            let latlngs = layer.getLatLngs();

            // LOGIKA PERHITUNGAN OTOMATIS
            if (mode === 'jalan') {
                // Hitung Panjang (Linear distance)
                for (let i = 0; i < latlngs.length - 1; i++) {
                    nilaiHitung += latlngs[i].distanceTo(latlngs[i+1]);
                }
            } else {
                // Hitung Luas (Area menggunakan Turf.js)
                var geojson = layer.toGeoJSON();
                nilaiHitung = turf.area(geojson);
            }

            // AJAX Simpan ke PHP
            var fd = new FormData();
            fd.append('type', mode);
            fd.append('nama', nama);
            fd.append('status', status);
            fd.append('coords', JSON.stringify(latlngs));
            fd.append('nilai', nilaiHitung.toFixed(2));

            fetch('save.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    layer.setStyle({ color: config[mode].warna[status], fillColor: config[mode].warna[status] });
                    let unit = mode === 'jalan' ? ' Meter' : ' m²';
                    layer.bindPopup(`<b>${nama}</b><br>Status: ${status}<br>Nilai: ${nilaiHitung.toFixed(2)}${unit}`).openPopup();
                    drawnItems.addLayer(layer);
                    alert("Berhasil disimpan!");
                }
            });
        });

        // Load data lama dari database (Jalan)
        <?php
        $q1 = mysqli_query($conn, "SELECT * FROM data_jalan");
        while($r = mysqli_fetch_assoc($q1)) {
            echo "L.polyline({$r['geojson_data']}, {color: config.jalan.warna['{$r['status_jalan']}']}).addTo(map).bindPopup('<b>{$r['nama_jalan']}</b><br>Panjang: {$r['panjang_meter']} m');";
        }
        // Load data lama dari database (Parsil)
        $q2 = mysqli_query($conn, "SELECT * FROM data_parsil");
        while($r = mysqli_fetch_assoc($q2)) {
            echo "L.polygon({$r['geojson_data']}, {color: config.parsil.warna['{$r['status_sertifikat']}'], fillColor: config.parsil.warna['{$r['status_sertifikat']}']}).addTo(map).bindPopup('<b>{$r['nomor_parsil']}</b><br>Luas: {$r['luas_m2']} m²');";
        }
        ?>
    </script>
</body>
</html>