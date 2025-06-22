<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../controllers/ExportController.php';
require_once __DIR__ . '/../services/PropertyService.php';
require_once __DIR__ . '/../db/Database.php';


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

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$data = ExportController::getExportData($userId, $type);
$filename = ExportController::getExportFilename($userId, $type);

switch ($format) {
    case 'csv':
        header('Content-Type: text/csv; charset=UTF-8');
        if (!$isAjax) {
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        }
        echo ExportController::generateCSV($data);
        break;

    case 'json':
        header('Content-Type: application/json; charset=UTF-8');
        if (!$isAjax) {
            header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        }
        echo ExportController::generateJSON($data);
        break;

    case 'pdf':
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(ExportController::getPDFData($data, $type));
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported format']);
        break;
}
