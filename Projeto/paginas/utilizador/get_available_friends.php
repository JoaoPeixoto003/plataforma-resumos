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

// Verificar se o usuário é admin do grupo
$check = "SELECT 1 FROM grupos WHERE id_grupo = '$grupo_id' AND id_criador = '$user_id'";
if (!mysqli_num_rows(mysqli_query($conn, $check))) {
    echo json_encode(['error' => 'Apenas o criador pode adicionar membros']);
    exit();
}

// Buscar amigos que não estão no grupo
$query = "SELECT u.id_utilizador, u.nome 
          FROM amizades a
          JOIN utilizadores u ON (a.id_utilizador1 = u.id_utilizador OR a.id_utilizador2 = u.id_utilizador)
          WHERE (a.id_utilizador1 = '$user_id' OR a.id_utilizador2 = '$user_id') 
            AND u.id_utilizador != '$user_id'
            AND a.status = 'aceite'
            AND u.id_utilizador NOT IN 
                (SELECT id_utilizador FROM membros_grupo WHERE id_grupo = '$grupo_id')";
$result = mysqli_query($conn, $query);

$amigos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $amigos[] = $row;
}

echo json_encode(['amigos' => $amigos]);
?>