<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$active = filter_var($input['active'], FILTER_VALIDATE_BOOLEAN);

$_SESSION['modo_editor'] = $active;

echo json_encode(['success' => true, 'active' => $_SESSION['modo_editor']]);
