<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\buscarMensagens.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (!isset($_SESSION['id_utilizador'])) {
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit();
}

$id_utilizador = $_SESSION['id_utilizador'];
$tipo = $_GET['tipo'];
$id = $_GET['id'];

if ($tipo === "privado") {
    $queryMensagens = "SELECT 
                        m.mensagem AS texto, 
                        IF(m.id_remetente = '$id_utilizador', 'enviada', 'recebida') AS tipo
                       FROM mensagens m
                       WHERE (m.id_remetente = '$id_utilizador' AND m.id_destinatario = '$id')
                          OR (m.id_remetente = '$id' AND m.id_destinatario = '$id_utilizador')
                       ORDER BY m.data_envio ASC";
    $resultMensagens = mysqli_query($conn, $queryMensagens);

    $queryNome = "SELECT nome FROM utilizadores WHERE id_utilizador = '$id'";
    $resultNome = mysqli_query($conn, $queryNome);
    $nome = mysqli_fetch_assoc($resultNome)['nome'];
} else if ($tipo === "grupo") {
    $queryMensagens = "SELECT 
                        m.mensagem AS texto, 
                        IF(m.id_remetente = '$id_utilizador', 'enviada', 'recebida') AS tipo
                       FROM mensagens m
                       WHERE m.id_grupo = '$id'
                       ORDER BY m.data_envio ASC";
    $resultMensagens = mysqli_query($conn, $queryMensagens);

    $queryNome = "SELECT nome_grupo AS nome FROM grupos WHERE id_grupo = '$id'";
    $resultNome = mysqli_query($conn, $queryNome);
    $nome = mysqli_fetch_assoc($resultNome)['nome'];
}

$mensagens = [];
while ($mensagem = mysqli_fetch_assoc($resultMensagens)) {
    $mensagens[] = $mensagem;
}

echo json_encode(["success" => true, "nome" => $nome, "mensagens" => $mensagens]);
