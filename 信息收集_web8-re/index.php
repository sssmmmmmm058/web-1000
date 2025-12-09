<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strncmp($requestPath, '/.svn', 5) === 0) {
    $flag = getenv('GZCTF_FLAG') ?: 'Flag not found (GZCTF_FLAG environment variable not set)';
    header('Content-Type: text/plain; charset=utf-8');
    echo $flag;
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo '404 Not Found';
exit;
?>