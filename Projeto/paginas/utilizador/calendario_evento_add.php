<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";
header('Content-Type: application/json');

if (!isset($_SESSION["id_utilizador"])) {
    echo json_encode(['success' => false]);
    exit();
}
$id_utilizador = $_SESSION["id_utilizador"];
$data = $_POST['data'] ?? '';
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$tipo = $_POST['tipo'] ?? 'nota';

if (!$data || !$titulo) {
    echo json_encode(['success' => false]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO calendario_eventos (id_utilizador, data, titulo, descricao, tipo) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $id_utilizador, $data, $titulo, $descricao, $tipo);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
