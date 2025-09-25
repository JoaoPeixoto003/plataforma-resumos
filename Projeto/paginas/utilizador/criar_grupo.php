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

if (!isset($_SESSION["id_utilizador"]) || !isset($_POST['nome_grupo'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

$nome_grupo = mysqli_real_escape_string($conn, $_POST['nome_grupo']);
$id_criador = $_SESSION['id_utilizador'];
$membros = json_decode($_POST['membros']);

// Inserir o grupo
$query = "INSERT INTO grupos (nome_grupo, id_criador) VALUES ('$nome_grupo', '$id_criador')";
if (mysqli_query($conn, $query)) {
    $id_grupo = mysqli_insert_id($conn);

    // Adicionar o criador como membro
    mysqli_query($conn, "INSERT INTO membros_grupo (id_grupo, id_utilizador) VALUES ('$id_grupo', '$id_criador')");

    // Adicionar os membros selecionados
    foreach ($membros as $membro) {
        $membro = mysqli_real_escape_string($conn, $membro);
        mysqli_query($conn, "INSERT INTO membros_grupo (id_grupo, id_utilizador) VALUES ('$id_grupo', '$membro')");
    }

    echo json_encode(['success' => true, 'message' => 'Grupo criado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao criar grupo']);
}
