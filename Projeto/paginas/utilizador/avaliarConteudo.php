<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\avaliarConteudo.php
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
    $nota = $_POST['avaliacao'];

    // Verificar se o utilizador já avaliou o conteúdo
    $queryVerificar = "SELECT * FROM avaliacoes WHERE id_utilizador = '$id_utilizador' AND id_conteudo = '$id_conteudo'";
    $resultVerificar = mysqli_query($conn, $queryVerificar);

    if (mysqli_num_rows($resultVerificar) > 0) {
        // Atualizar avaliação existente
        $queryAtualizar = "UPDATE avaliacoes SET nota = '$nota' WHERE id_utilizador = '$id_utilizador' AND id_conteudo = '$id_conteudo'";
        mysqli_query($conn, $queryAtualizar);
    } else {
        // Inserir nova avaliação
        $queryInserir = "INSERT INTO avaliacoes (id_utilizador, id_conteudo, nota) VALUES ('$id_utilizador', '$id_conteudo', '$nota')";
        mysqli_query($conn, $queryInserir);
    }

    // Calcular a nova média
    $queryMedia = "SELECT AVG(nota) AS media FROM avaliacoes WHERE id_conteudo = '$id_conteudo'";
    $resultMedia = mysqli_query($conn, $queryMedia);
    $media = mysqli_fetch_assoc($resultMedia)['media'];

    echo json_encode(["success" => true, "media" => number_format($media, 1)]);
    exit();
}

echo json_encode(["success" => false, "message" => "Método inválido."]);
