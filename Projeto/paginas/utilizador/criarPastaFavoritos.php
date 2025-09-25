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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_utilizador = $_POST['id_utilizador'];
    $nome = trim($_POST['nome']);

    if (empty($nome)) {
        echo json_encode(['success' => false, 'message' => 'Nome da pasta não pode estar vazio']);
        exit();
    }

    // Verificar se já existe uma pasta com o mesmo nome para este utilizador
    $checkQuery = "SELECT id_pasta FROM pastas_favoritos WHERE id_utilizador = ? AND nome = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "is", $id_utilizador, $nome);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo json_encode(['success' => false, 'message' => 'Já existe uma pasta com este nome']);
        exit();
    }

    // Se for edição
    if (!empty($_POST['id_pasta'])) {
        $id_pasta = $_POST['id_pasta'];
        $updateQuery = "UPDATE pastas_favoritos SET nome = ? WHERE id_pasta = ? AND id_utilizador = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "sii", $nome, $id_pasta, $id_utilizador);
    } else {
        // Se for criação
        $insertQuery = "INSERT INTO pastas_favoritos (id_utilizador, nome) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "is", $id_utilizador, $nome);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar pasta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
