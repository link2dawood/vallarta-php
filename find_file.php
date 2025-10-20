$file = $_GET['f'] ?? '';
$dir = _DIR_;

// Scan directory for PHP files
$files = glob($dir . '/*.php');

foreach($files as $f) {
    $name = pathinfo($f, PATHINFO_FILENAME);
    if(strcasecmp($name, $file) === 0) {
        include $f;
        exit;
    }
}

http_response_code(404);
echo "File not found";