<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ExportController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (!in_array($type, ['favorites', 'my_properties'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid export type']);
    exit;
}
if (!in_array($format, ['csv', 'json', 'pdf'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid export format']);
    exit;
}

$data = ExportController::getExportData($userId, $type);
$filename = ExportController::getExportFilename($userId, $type);

switch ($format) {
    case 'csv':
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        echo ExportController::generateCSV($data);
        break;
    case 'json':
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        echo ExportController::generateJSON($data);
        break;
    case 'pdf':
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(ExportController::getPDFData($data, $type));
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported format']);
        exit;
}