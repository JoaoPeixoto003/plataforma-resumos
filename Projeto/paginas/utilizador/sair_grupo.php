<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\sair_grupo.php
session_start();
include "../../baseDados/basedados.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id_utilizador'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];
$id_grupo = isset($_POST['id_grupo']) ? intval($_POST['id_grupo']) : 0;

if ($id_grupo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Grupo inválido.']);
    exit;
}

// Verifica se o utilizador é o criador do grupo
$query = "SELECT id_criador FROM grupos WHERE id_grupo = $id_grupo";
$res = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($res);

if ($row && $row['id_criador'] == $id_utilizador) {
    echo json_encode(['success' => false, 'message' => 'O criador não pode sair do grupo.']);
    exit;
}

// Remove o utilizador do grupo
$del = mysqli_query($conn, "DELETE FROM membros_grupo WHERE id_grupo = $id_grupo AND id_utilizador = $id_utilizador");

if ($del) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao sair do grupo.']);
}
