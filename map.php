<?php
require_once __DIR__ . '/src/config/config.php';
session_start();

// Verifica daca utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map View - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <style>
        /* Eliminam padding-ul si marginile nedorite */
        body, html {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .main-content {
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden;
        }
        
        /* Stiluri pentru harta full-screen */
        #map {
            flex: 1;
            height: 100vh;
            width: 100%;
            transition: flex 0.3s ease;
        }

        /* Stiluri pentru panoul lateral cu proprietati */
        #property-sidebar {
            width: 300px;
            flex: 0 0 300px;
            padding: 1.5rem;
            background-color: white;
            border-left: 1px solid #ddd;
            overflow-y: auto;
            transition: all 0.3s ease;
        }
        
        #property-sidebar.hidden {
            margin-right: -300px;
            flex: 0 0 0;
            width: 0;
            padding: 0;
            overflow: hidden;
        }

        #property-sidebar h2 {
            margin-top: 0;
        }

        #property-list {
            list-style: none;
            padding-left: 0;
        }

        #property-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        /* Layout-ul principal al hartii */
        .map-layout {
            display: flex;
            height: 100vh;
            width: 100%;
            overflow: hidden;
        }
        
        /* Buton toggle pentru lista proprietati - vizibil mereu */
        .toggle-property-list {
            display: block;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: #42a5f5;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            cursor: pointer;
        }
        
        /* Ajustari pentru mobil */
        @media (max-width: 768px) {
            #map {
                height: 100vh;
                width: 100%;
            }
            
            #property-sidebar {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 40vh;
                transform: translateY(100%);
                transition: transform 0.3s;
                z-index: 999;
                border-left: none;
                border-top: 1px solid #ddd;
                flex: none;
                margin-right: 0;
            }
            
            #property-sidebar.mobile-visible {
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<?php include_once 'public/includes/dashboard_header.php'; ?>

<!-- Continut specific pentru pagina de harta -->
<div class="map-layout">
    <div id="map"></div>
    <!-- Panou lateral cu lista de proprietati -->
    <div id="property-sidebar">
        <h2>Available Properties</h2>
        <ul id="property-list"></ul>
    </div>
</div>
<!-- Buton pentru afisarea/ascunderea listei pe toate dimensiunile -->
<button class="toggle-property-list" id="toggle-list">
    &#9776;
</button>

<?php include_once 'public/includes/dashboard_footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>public/assets/js/map.js"></script>
</body>
</html>
