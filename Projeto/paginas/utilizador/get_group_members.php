<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (!isset($_SESSION["id_utilizador"]) || !isset($_GET['id_grupo'])) {
    echo json_encode(['error' => 'Acesso não autorizado']);
    exit();
}

$grupo_id = mysqli_real_escape_string($conn, $_GET['id_grupo']);
$user_id = $_SESSION['id_utilizador'];

// Verificar se o usuário é membro do grupo
$check = "SELECT 1 FROM membros_grupo WHERE id_grupo = '$grupo_id' AND id_utilizador = '$user_id'";
if (!mysqli_num_rows(mysqli_query($conn, $check))) {
    echo json_encode(['error' => 'Você não é membro deste grupo']);
    exit();
}

// Buscar membros do grupo
$query = "SELECT u.id_utilizador, u.nome 
          FROM membros_grupo mg
          JOIN utilizadores u ON mg.id_utilizador = u.id_utilizador
          WHERE mg.id_grupo = '$grupo_id'";
$result = mysqli_query($conn, $query);

$membros = [];
while ($row = mysqli_fetch_assoc($result)) {
    $membros[] = $row;
}

echo json_encode(['membros' => $membros]);
?>