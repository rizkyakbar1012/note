<?php
/**
 * API untuk menyimpan dan memuat data spreadsheet notes ke file JSON
 * Tanpa database - menggunakan file JSON sebagai storage
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Path file JSON untuk menyimpan data
$dataFile = __DIR__ . '/data.json';

// Fungsi untuk membaca data dari file JSON
function loadData($file) {
    if (!file_exists($file)) {
        // Jika file belum ada, return array kosong dengan struktur default
        return [
            'data' => [],
            'last_updated' => date('Y-m-d H:i:s'),
            'version' => 1
        ];
    }
    
    $content = file_get_contents($file);
    if ($content === false) {
        return [
            'data' => [],
            'last_updated' => date('Y-m-d H:i:s'),
            'version' => 1
        ];
    }
    
    $decoded = json_decode($content, true);
    if ($decoded === null) {
        return [
            'data' => [],
            'last_updated' => date('Y-m-d H:i:s'),
            'version' => 1
        ];
    }
    
    return $decoded;
}

// Fungsi untuk menyimpan data ke file JSON
function saveData($file, $data) {
    $dataToSave = [
        'data' => $data,
        'last_updated' => date('Y-m-d H:i:s'),
        'version' => 1
    ];
    
    $json = json_encode($dataToSave, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Tulis ke file dengan lock untuk mencegah race condition
    $result = file_put_contents($file, $json, LOCK_EX);
    
    return $result !== false;
}

// Handle request
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Load data dari file JSON
    $fileData = loadData($dataFile);
    
    echo json_encode([
        'success' => true,
        'data' => $fileData['data'],
        'last_updated' => $fileData['last_updated'] ?? date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
} elseif ($method === 'POST') {
    // Simpan data ke file JSON
    $input = file_get_contents('php://input');
    $postData = json_decode($input, true);
    
    if ($postData === null || !isset($postData['data'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data format'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $clientData = $postData['data'];
    
    // Validasi: pastikan data adalah array 2D
    if (!is_array($clientData) || (count($clientData) > 0 && !is_array($clientData[0]))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Data must be a 2D array'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Simpan data langsung dari client (overwrite, tanpa merge)
    // Data yang dikirim dari client adalah data terbaru yang ingin disimpan
    $saved = saveData($dataFile, $clientData);
    
    if ($saved) {
        echo json_encode([
            'success' => true,
            'message' => 'Data saved successfully',
            'data' => $clientData,
            'last_updated' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save data'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ], JSON_UNESCAPED_UNICODE);
}
?>
