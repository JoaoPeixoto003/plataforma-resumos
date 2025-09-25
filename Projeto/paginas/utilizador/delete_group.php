<?php
session_start();
include "../../baseDados/basedados.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_utilizador'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];
$id_grupo = intval($_POST['id_grupo'] ?? 0);

if ($id_grupo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Grupo inválido.']);
    exit;
}

// Verifica se é o criador do grupo
$res = mysqli_query($conn, "SELECT id_criador FROM grupos WHERE id_grupo = $id_grupo");
$row = mysqli_fetch_assoc($res);
if (!$row || $row['id_criador'] != $id_utilizador) {
    echo json_encode(['success' => false, 'message' => 'Apenas o criador pode apagar o grupo.']);
    exit;
}

// Remove todos os membros do grupo
mysqli_query($conn, "DELETE FROM membros_grupo WHERE id_grupo = $id_grupo");

// Remove o grupo
$del = mysqli_query($conn, "DELETE FROM grupos WHERE id_grupo = $id_grupo");

if ($del) {
    echo json_encode(['success' => true]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao apagar grupo.']);
    exit;
}

mysqli_close($conn);
