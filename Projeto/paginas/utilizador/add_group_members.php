<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

// Verificar se é admin
if (!isset($_SESSION["id_utilizador"]) || $_SESSION["nivel"] != 1) {
    header("Location: ../visitante/login.php");
    exit();
}

if (!isset($_SESSION["id_utilizador"]) || !isset($_POST['id_grupo']) || !isset($_POST['membros'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

$grupo_id = mysqli_real_escape_string($conn, $_POST['id_grupo']);
$user_id = $_SESSION['id_utilizador'];
$membros = json_decode($_POST['membros']);

// Verificar se o usuário é admin do grupo
$check = "SELECT 1 FROM grupos WHERE id_grupo = '$grupo_id' AND id_criador = '$user_id'";
if (!mysqli_num_rows(mysqli_query($conn, $check))) {
    echo json_encode(['success' => false, 'message' => 'Apenas o criador pode adicionar membros']);
    exit();
}

// Adicionar cada membro
$success = true;
foreach ($membros as $membro_id) {
    $membro_id = mysqli_real_escape_string($conn, $membro_id);
    $insert = "INSERT INTO membros_grupo (id_grupo, id_utilizador) VALUES ('$grupo_id', '$membro_id')";
    if (!mysqli_query($conn, $insert)) {
        $success = false;
    }
}

echo json_encode(['success' => $success, 'message' => $success ? 'Membros adicionados' : 'Erro ao adicionar alguns membros']);
