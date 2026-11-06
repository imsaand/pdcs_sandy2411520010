
<?php

function sendResponse($status, $message, $data = null, $httpCode = 200) {

    http_response_code($httpCode);

    header("Content-Type: application/json; charset=UTF-8");

    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    exit;
}
