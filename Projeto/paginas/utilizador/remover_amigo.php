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

$userID = $_SESSION["id_utilizador"];

// Verifica se o ID do amigo foi passado
if (isset($_POST['id_amigo'])) {
    $idAmigo = mysqli_real_escape_string($conn, $_POST['id_amigo']);

    // Remover a amizade da tabela 'amizades' onde o status é 'aceito'
    $queryRemover = "DELETE FROM amizades
                     WHERE (id_utilizador1 = $userID AND id_utilizador2 = $idAmigo OR id_utilizador1 = $idAmigo AND id_utilizador2 = $userID)
                     AND status = 'aceite'";

    if (mysqli_query($conn, $queryRemover)) {
        header("Location: listaAmigos.php?msg=Amigo removido com sucesso");
    } else {
        echo "Erro ao remover o amigo: " . mysqli_error($conn);
    }
} else {
    echo "Parâmetros inválidos.";
}
