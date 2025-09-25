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

header('Content-Type: application/json');

if (!isset($_SESSION["id_utilizador"])) {
    echo json_encode(['success' => false, 'msg' => 'Sem permissão']);
    exit();
}

$id_utilizador = $_SESSION["id_utilizador"];
$id_conteudo = isset($_POST['id_conteudo']) ? intval($_POST['id_conteudo']) : 0;

// Verifica se o conteúdo pertence ao usuário
$sql = "SELECT formato FROM conteudos WHERE id_conteudo = $id_conteudo AND id_utilizador = $id_utilizador";
$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
    echo json_encode(['success' => false, 'msg' => 'Conteúdo não encontrado']);
    exit();
}
$row = mysqli_fetch_assoc($res);
$formato = $row['formato'];

// Apaga o conteúdo do banco
if (mysqli_query($conn, "DELETE FROM conteudos WHERE id_conteudo = $id_conteudo AND id_utilizador = $id_utilizador")) {
    // Apaga o ficheiro se existir
    $file_path = "../../uploads/" . $formato;
    if ($formato && file_exists($file_path)) {
        @unlink($file_path);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Erro ao apagar']);
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
