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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_conteudo = $_POST['id_conteudo'];
    $id_utilizador = $_POST['id_utilizador'];
    $id_pasta = $_POST['id_pasta'] == '0' ? NULL : $_POST['id_pasta'];

    // Verificar se o favorito existe
    $checkQuery = "SELECT id_favorito FROM favoritos WHERE id_conteudo = ? AND id_utilizador = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "ii", $id_conteudo, $id_utilizador);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 0) {
        echo json_encode(['success' => false, 'message' => 'Favorito não encontrado']);
        exit();
    }

    // Atualizar a pasta do favorito
    $updateQuery = "UPDATE favoritos SET id_pasta = ? WHERE id_conteudo = ? AND id_utilizador = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "iii", $id_pasta, $id_conteudo, $id_utilizador);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao mover favorito']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
