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

    $deleteQuery = "DELETE FROM favoritos WHERE id_conteudo = ? AND id_utilizador = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "ii", $id_conteudo, $id_utilizador);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover favorito']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}

?>