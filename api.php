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

// Fungsi untuk merge data (gabungkan data dari server dengan data baru)
function mergeData($serverData, $clientData) {
    // Jika server kosong, gunakan data client
    if (empty($serverData)) {
        return $clientData;
    }
    
    // Jika client kosong, gunakan data server
    if (empty($clientData)) {
        return $serverData;
    }
    
    // Merge strategy: ambil yang lebih besar (lebih banyak baris/kolom)
    // Jika sama besar, prioritaskan data server (yang sudah ada)
    $serverRows = count($serverData);
    $clientRows = count($clientData);
    
    // Tentukan jumlah kolom maksimal
    $maxCols = 0;
    foreach ($serverData as $row) {
        if (is_array($row) && count($row) > $maxCols) {
            $maxCols = count($row);
        }
    }
    foreach ($clientData as $row) {
        if (is_array($row) && count($row) > $maxCols) {
            $maxCols = count($row);
        }
    }
    
    // Merge: ambil nilai dari client jika tidak kosong, jika kosong ambil dari server
    $merged = [];
    $maxRows = max($serverRows, $clientRows);
    
    for ($r = 0; $r < $maxRows; $r++) {
        $mergedRow = [];
        $serverRow = isset($serverData[$r]) ? $serverData[$r] : [];
        $clientRow = isset($clientData[$r]) ? $clientData[$r] : [];
        
        for ($c = 0; $c < $maxCols; $c++) {
            $serverVal = isset($serverRow[$c]) ? trim($serverRow[$c]) : '';
            $clientVal = isset($clientRow[$c]) ? trim($clientRow[$c]) : '';
            
            // Prioritaskan client jika ada isinya, jika kosong ambil dari server
            if (!empty($clientVal)) {
                $mergedRow[] = $clientVal;
            } elseif (!empty($serverVal)) {
                $mergedRow[] = $serverVal;
            } else {
                $mergedRow[] = '';
            }
        }
        
        $merged[] = $mergedRow;
    }
    
    return $merged;
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
    
    // Load data yang sudah ada di server
    $fileData = loadData($dataFile);
    $serverData = $fileData['data'] ?? [];
    
    // Merge data (gabungkan dengan data yang sudah ada)
    $mergedData = mergeData($serverData, $clientData);
    
    // Simpan data yang sudah di-merge
    $saved = saveData($dataFile, $mergedData);
    
    if ($saved) {
        echo json_encode([
            'success' => true,
            'message' => 'Data saved successfully',
            'data' => $mergedData,
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
