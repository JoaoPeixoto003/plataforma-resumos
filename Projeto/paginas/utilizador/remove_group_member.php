<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (isset($_SESSION["nivel"]) && $_SESSION["nivel"] != 1) {
    header("Location: erro.php");
    exit();
} else if (!isset($_SESSION["nivel"])) {
    header("Location: erro.php");
    exit();
}

if (!isset($_SESSION["id_utilizador"]) || !isset($_POST['id_grupo']) || !isset($_POST['id_utilizador'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

$grupo_id = mysqli_real_escape_string($conn, $_POST['id_grupo']);
$member_id = mysqli_real_escape_string($conn, $_POST['id_utilizador']);
$user_id = $_SESSION['id_utilizador'];

// Verificar se o usuário é admin do grupo
$check = "SELECT 1 FROM grupos WHERE id_grupo = '$grupo_id' AND id_criador = '$user_id'";
if (!mysqli_num_rows(mysqli_query($conn, $check))) {
    echo json_encode(['success' => false, 'message' => 'Apenas o criador pode remover membros']);
    exit();
}

// Não permitir remover a si mesmo
if ($member_id == $user_id) {
    echo json_encode(['success' => false, 'message' => 'Você não pode se remover do grupo']);
    exit();
}

// Remover membro
$delete = "DELETE FROM membros_grupo WHERE id_grupo = '$grupo_id' AND id_utilizador = '$member_id'";
$result = mysqli_query($conn, $delete);

echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Membro removido' : 'Erro ao remover membro']);
?>