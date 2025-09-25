<?php
header('Content-Type: application/json');
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

// Recebe e valida os dados do POST
$id_utilizador = $_SESSION['id_utilizador'];
$id_conteudo = isset($_POST['id_conteudo']) ? intval($_POST['id_conteudo']) : 0;
$descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';

if (!$id_conteudo || empty($descricao)) {
    echo json_encode(['success' => false, 'message' => 'Dados em falta']);
    exit;
}

// Insere o report na base de dados
$stmt = $conn->prepare("INSERT INTO reports (id_utilizador, id_conteudo, tipo, descricao, status) VALUES (?, ?, 'conteudo', ?, 'pendente')");
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da query']);
    exit;
}
$stmt->bind_param("iis", $id_utilizador, $id_conteudo, $descricao);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao inserir report']);
}
$stmt->close();
$conn->close();
