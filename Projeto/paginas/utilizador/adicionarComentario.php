<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\adicionarComentario.php
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['id_utilizador'])) {
        echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
        exit();
    }

    $id_utilizador = $_SESSION['id_utilizador'];
    $id_conteudo = $_POST['id_conteudo'];
    $comentario = trim($_POST['comentario']);

    if (empty($comentario)) {
        echo json_encode(["success" => false, "message" => "O comentário não pode estar vazio."]);
        exit();
    }

    // Inserir o comentário no banco de dados
    $queryInserirComentario = "INSERT INTO comentarios (id_utilizador, id_conteudo, texto) VALUES ('$id_utilizador', '$id_conteudo', '$comentario')";
    if (mysqli_query($conn, $queryInserirComentario)) {
        echo json_encode(["success" => true, "message" => "Comentário adicionado com sucesso."]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao adicionar comentário."]);
    }
    exit();
}

echo json_encode(["success" => false, "message" => "Método inválido."]);
