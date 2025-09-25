<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\admin\get_user.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] < 2) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
$res = mysqli_query($conn, "SELECT * FROM utilizadores WHERE id_utilizador = $id");
if ($res && $user = mysqli_fetch_assoc($res)) {
    echo json_encode($user);
} else {
    echo json_encode(['error' => 'Utilizador não encontrado']);
}
