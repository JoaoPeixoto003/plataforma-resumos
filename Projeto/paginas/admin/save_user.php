<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\admin\save_user.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] < 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = intval($data['id'] ?? 0);
$nome = mysqli_real_escape_string($conn, $data['nome'] ?? '');
$email = mysqli_real_escape_string($conn, $data['email'] ?? '');
$tipo = mysqli_real_escape_string($conn, $data['tipo'] ?? '');

if ($id && $nome && $email && $tipo) {
    $sql = "UPDATE utilizadores SET nome='$nome', email='$email', tipo='$tipo' WHERE id_utilizador=$id";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar utilizador']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
}
