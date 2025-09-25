<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\admin\aceitar_report.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (!isset($_SESSION['id_utilizador'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$id_report = intval($_POST['id_report'] ?? 0);

if (!$id_report) {
    echo json_encode(['success' => false, 'message' => 'ID do report inválido']);
    exit;
}

// Buscar o id_conteudo do report
$res = mysqli_query($conn, "SELECT id_conteudo FROM reports WHERE id_report = $id_report");
if (!$res || !($row = mysqli_fetch_assoc($res))) {
    echo json_encode(['success' => false, 'message' => 'Report não encontrado']);
    exit;
}
$id_conteudo = intval($row['id_conteudo']);

// Apagar comentários e favoritos do conteúdo
mysqli_query($conn, "DELETE FROM comentarios WHERE id_conteudo = $id_conteudo");
mysqli_query($conn, "DELETE FROM favoritos WHERE id_conteudo = $id_conteudo");

// Apagar todos os reports desse conteúdo
mysqli_query($conn, "DELETE FROM reports WHERE id_conteudo = $id_conteudo");

// Apagar o conteúdo
mysqli_query($conn, "DELETE FROM conteudos WHERE id_conteudo = $id_conteudo");

echo json_encode(['success' => true]);
