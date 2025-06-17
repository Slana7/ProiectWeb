<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/PropertyController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$conn = Database::connect();
$userId = $_SESSION['user_id'];
$exportType = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (!in_array($exportType, ['favorites', 'my_properties'])) {
    $_SESSION['flash_message'] = 'Invalid export type.';
    header("Location: ../../profile.php");
    exit;
}

if (!in_array($format, ['csv', 'json', 'pdf'])) {
    $_SESSION['flash_message'] = 'Invalid export format.';
    header("Location: ../../profile.php");
    exit;
}

$userStmt = $conn->prepare("SELECT name FROM users WHERE id = :id");
$userStmt->execute(['id' => $userId]);
$userName = $userStmt->fetchColumn();
$safeUserName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $userName);

$data = [];
$filename = '';

if ($exportType === 'favorites') {
    $stmt = $conn->prepare("
        SELECT p.id, p.title, p.description, p.price, p.area, p.status, p.created_at,
               ST_Y(p.location::geometry) as lat, ST_X(p.location::geometry) as lng,
               u.name as owner_name, u.email as owner_email
        FROM saved_properties sp
        JOIN properties p ON sp.property_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE sp.user_id = :uid
        ORDER BY sp.created_at DESC
    ");
    $stmt->execute(['uid' => $userId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $filename = $safeUserName . '_favorite_properties_' . date('Y-m-d');
} else if ($exportType === 'my_properties') {
    $data = getUserProperties($userId);
    $filename = $safeUserName . '_my_properties_' . date('Y-m-d');
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

switch ($format) {
    case 'csv':
        exportCSV($data, $filename);
        break;
    case 'json':
        exportJSON($data, $filename);
        break;
    case 'pdf':
        exportPDF($data, $filename, $exportType);
        break;
}

function exportCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        $headers = [
            'ID', 'Title', 'Description', 'Price (€)', 'Area (m²)', 
            'Status', 'Latitude', 'Longitude', 'Facilities', 'Created At'
        ];
        
        if (isset($data[0]['owner_name'])) {
            $headers[] = 'Owner Name';
            $headers[] = 'Owner Email';
        }
        
        fputcsv($output, $headers);
        
        foreach ($data as $row) {
            $csvRow = [
                $row['id'],
                $row['title'],
                $row['description'],
                $row['price'],
                $row['area'],
                $row['status'] === 'for_sale' ? 'For Sale' : 'For Rent',
                $row['lat'] ?? '',
                $row['lng'] ?? '',
                $row['facilities'] ?? '',
                $row['created_at']
            ];
            
            if (isset($row['owner_name'])) {
                $csvRow[] = $row['owner_name'];
                $csvRow[] = $row['owner_email'];
            }
            
            fputcsv($output, $csvRow);
        }
    }
    
    fclose($output);
    exit;
}

function exportJSON($data, $filename) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    
    $exportData = [
        'export_date' => date('Y-m-d H:i:s'),
        'total_properties' => count($data),
        'properties' => $data
    ];
    
    echo json_encode($exportData, JSON_PRETTY_PRINT);
    exit;
}

function exportPDF($data, $filename, $exportType) {
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?= $exportType === 'favorites' ? 'Favorite Properties' : 'My Properties' ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
            .property { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .property-title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px; }
            .property-details { margin-bottom: 8px; }
            .price { font-weight: bold; color: #2c5aa0; }
            .facilities { font-style: italic; color: #666; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><?= $exportType === 'favorites' ? 'My Favorite Properties' : 'My Properties' ?></h1>
            <p>Exported on <?= date('F j, Y') ?></p>
            <p>Total Properties: <?= count($data) ?></p>
        </div>
        
        <?php if (empty($data)): ?>
            <p>No properties found.</p>
        <?php else: ?>
            <?php foreach ($data as $property): ?>
                <div class="property">
                    <div class="property-title"><?= htmlspecialchars($property['title']) ?></div>
                    <div class="property-details"><strong>Description:</strong> <?= htmlspecialchars($property['description']) ?></div>
                    <div class="property-details price"><strong>Price:</strong> €<?= number_format($property['price']) ?></div>
                    <div class="property-details"><strong>Area:</strong> <?= $property['area'] ?> m²</div>
                    <div class="property-details"><strong>Status:</strong> <?= $property['status'] === 'for_sale' ? 'For Sale' : 'For Rent' ?></div>
                    <?php if (!empty($property['facilities'])): ?>
                        <div class="property-details facilities"><strong>Facilities:</strong> <?= htmlspecialchars($property['facilities']) ?></div>
                    <?php endif; ?>
                    <?php if (isset($property['owner_name'])): ?>
                        <div class="property-details"><strong>Owner:</strong> <?= htmlspecialchars($property['owner_name']) ?> (<?= htmlspecialchars($property['owner_email']) ?>)</div>
                    <?php endif; ?>
                    <div class="property-details"><strong>Posted:</strong> <?= date('F j, Y', strtotime($property['created_at'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="footer">
            <p>Generated by REM - Real Estate Management System</p>
        </div>
    </body>
    </html>
    <?php
    $html = ob_get_clean();
    
    echo $html;
    exit;
}

$_SESSION['flash_message'] = 'Export completed successfully.';
header("Location: ../../profile.php");
exit;
?>
