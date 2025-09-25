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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $idUtilizadorLogado = $_SESSION['id_utilizador'];
    $nomeAmigo = $_POST['friend-name'];

    // Verificar se o amigo existe
    $query = "SELECT id_utilizador FROM utilizadores WHERE nome = '$nomeAmigo'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $amigo = mysqli_fetch_assoc($result);
        $idAmigo = $amigo['id_utilizador'];

        // Verificar se já são amigos
        $query = "
        SELECT * FROM amizades
        WHERE 
            (id_utilizador1 = $idUtilizadorLogado AND id_utilizador2 = $idAmigo)
            OR (id_utilizador1 = $idAmigo AND id_utilizador2 = $idUtilizadorLogado)
    ";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['message'] = "Você já é amigo de $nomeAmigo.";
        } else {
            // Adicionar amigo
            $query = "
            INSERT INTO amizades (id_utilizador1, id_utilizador2, status)
            VALUES ($idUtilizadorLogado, $idAmigo, 'pendente')
        ";
            $result = mysqli_query($conn, $query);

            if ($result) {
                $_SESSION['message'] = "Pedido de amizade enviado para $nomeAmigo.";
            } else {
                $_SESSION['message'] = "Erro ao enviar pedido de amizade.";
            }
        }
    } else {
        $_SESSION['message'] = "Amigo não encontrado.";
    }

    header("Location: listaAmigos.php");
    exit();  // Certifique-se de usar exit após header para evitar o código posterior ser executado
}
