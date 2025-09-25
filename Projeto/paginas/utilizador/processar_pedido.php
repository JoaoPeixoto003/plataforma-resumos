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

// Verifica se o ID de amizade e a ação foram passados
if (isset($_POST['id_amizade']) && isset($_POST['acao'])) {
    $idAmizade = mysqli_real_escape_string($conn, $_POST['id_amizade']);
    $acao = $_POST['acao'];

    // Se a ação for "aceitar", inserimos o pedido na tabela de amizades
    if ($acao == 'aceitar') {
        $queryAceitar = "UPDATE amizades
                         SET status = 'aceite' 
                         WHERE id_amizade = $idAmizade AND id_utilizador2 = $userID";

        if (mysqli_query($conn, $queryAceitar)) {
            header("Location: pedidosAmizade.php?msg=Pedido aceite ");
        } else {
            echo "Erro ao aceitar o pedido: " . mysqli_error($conn);
        }

    // Se a ação for "recusar", excluímos o pedido de amizade
    } else if ($acao == 'recusar') {
        $queryRecusar = "DELETE FROM amizades
                         WHERE id_amizade = $idAmizade AND id_utilizador2 = $userID";

        if (mysqli_query($conn, $queryRecusar)) {
            header("Location: pedidosAmizade.php?msg=Pedido recusado com sucesso");
        } else {
            echo "Erro ao recusar o pedido: " . mysqli_error($conn);
        }
    }
} else {
    echo "Parâmetros inválidos.";
}
?>
