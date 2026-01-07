<?php
// Simulate session
session_start();
$_SESSION['usuario_id'] = 6; // Mocking 'Sandra'
$_GET['action'] = 'poll';

// Include the API file
require 'api/notifications.php';
