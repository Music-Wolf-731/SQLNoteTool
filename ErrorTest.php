<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
echo 'what?';
unset($_SESSION['customer']);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=notetool;charset=utf8', 'NoteToolController', 'ToolMaker');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo 'Database connection successful';
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// 其餘代碼...
?>
