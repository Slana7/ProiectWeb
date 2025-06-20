<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/Database.php';
require_once __DIR__ . '/src/controllers/PropertyController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = Database::connect();
$userId = $_SESSION['user_id'];
$exportType = $_GET['type'] ?? '';

if (!in_array($exportType, ['favorites', 'my_properties'])) {
    $_SESSION['flash_message'] = 'Invalid export type.';
    header("Location: profile.php");
    exit;
}

$data = [];
$filename = '';

if ($exportType === 'favorites') {
    $stmt = $conn->prepare("
        SELECT p.id, p.title, p.description, p.price, p.area, p.status, p.posted_at as created_at,
               ST_Y(p.location::geometry) as lat, ST_X(p.location::geometry) as lng,
               u.name as owner_name, u.email as owner_email
        FROM saved_properties sp
        JOIN properties p ON sp.property_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE sp.user_id = :uid
        ORDER BY p.id DESC
    ");
    $stmt->execute(['uid' => $userId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else if ($exportType === 'my_properties') {
    $data = getUserProperties($userId);
}

foreach ($data as &$property) {
    $facilityStmt = $conn->prepare("
        SELECT f.name 
        FROM property_facility pf 
        JOIN facilities f ON pf.facility_id = f.id 
        WHERE pf.property_id = :pid
    ");
    $facilityStmt->execute(['pid' => $property['id']]);
    $facilities = $facilityStmt->fetchAll(PDO::FETCH_COLUMN);
    $property['facilities'] = implode(', ', $facilities);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $exportType === 'favorites' ? 'Favorite Properties' : 'My Properties' ?> - <?= APP_NAME ?></title>    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <style>
        @media print {
            body { margin: 0; font-size: 12pt; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .property { break-inside: avoid; }
            .sidebar { display: none !important; }
            .dashboard-layout { display: block !important; }
            .main-content { padding: 10px !important; }
            .mobile-overlay { display: none !important; }
        }
        
        .print-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .print-controls {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        
        .print-btn {
            background-color: #42a5f5;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .print-btn:hover {
            background-color: #1e88e5;
            color: white;
            text-decoration: none;
        }
        
        .back-btn {
            background-color: #6c757d;
        }
        
        .back-btn:hover {
            background-color: #545b62;
        }
        
        .export-header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
        }
        
        .export-property {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .export-property:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        
        .export-property-title { 
            font-size: 1.25rem;
            font-weight: bold; 
            color: #1e2a38; 
            margin-top: 0;
            margin-bottom: 10px; 
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .export-property-details { 
            margin-bottom: 8px; 
        }
        
        .export-property-price { 
            font-weight: bold; 
            color: #42a5f5; 
            font-size: 1.1rem;
        }
        
        .export-facilities { 
            font-style: italic; 
            color: #666; 
        }
        
        .export-footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .export-property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
    </style>    <script>
        function printPage() {
            window.print();
        }
        
        window.onload = function() {
            document.getElementById('printBtn').focus();
        }
    </script>
</head>
<body>
    <?php include_once 'public/includes/dashboard_header.php'; ?>
    
    <header class="top-bar">
        <h1>Export Preview</h1>
    </header>
    
    <div class="print-header no-print">
        <h2>📄 Print Preview</h2>
        <p>Use the controls below to print or save as PDF</p>
    </div>
    
    <div class="print-controls no-print">
        <button id="printBtn" class="print-btn" onclick="printPage()">🖨️ Print / Save as PDF</button>
        <a href="profile.php" class="print-btn back-btn">← Back to Profile</a>
    </div>
    
    <div class="export-header">
        <h1><?= $exportType === 'favorites' ? 'My Favorite Properties' : 'My Properties' ?></h1>
        <p>Exported on <?= date('F j, Y') ?></p>
        <p>Total Properties: <?= count($data) ?></p>
    </div>
    
    <?php if (empty($data)): ?>
        <div class="export-property">
            <p style="text-align: center; font-style: italic; color: #666;">No properties found.</p>
        </div>
    <?php else: ?>
        <div class="export-property-grid">
            <?php foreach ($data as $property): ?>
                <div class="export-property">
                    <div class="export-property-title"><?= htmlspecialchars($property['title']) ?></div>
                    <div class="export-property-details"><strong>Description:</strong> <?= htmlspecialchars($property['description']) ?></div>
                    <div class="export-property-details export-property-price"><strong>Price:</strong> €<?= number_format($property['price']) ?></div>
                    <div class="export-property-details"><strong>Area:</strong> <?= $property['area'] ?> m²</div>
                    <div class="export-property-details"><strong>Status:</strong> <?= $property['status'] === 'for_sale' ? 'For Sale' : 'For Rent' ?></div>
                    <?php if (!empty($property['facilities'])): ?>
                        <div class="export-property-details export-facilities"><strong>Facilities:</strong> <?= htmlspecialchars($property['facilities']) ?></div>
                    <?php endif; ?>
                    <?php if (isset($property['owner_name'])): ?>
                        <div class="export-property-details"><strong>Owner:</strong> <?= htmlspecialchars($property['owner_name']) ?> (<?= htmlspecialchars($property['owner_email']) ?>)</div>
                    <?php endif; ?>
                    <div class="export-property-details"><strong>Posted:</strong> <?= date('F j, Y', strtotime($property['created_at'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
      <div class="export-footer">
        <p>Generated by REM - Real Estate Management System</p>
        <p>Report generated on <?= date('F j, Y \a\t H:i', time()) ?> (Europe/Bucharest)</p>
    </div>

    <?php include_once 'public/includes/dashboard_footer.php'; ?>
</body>
</html>
