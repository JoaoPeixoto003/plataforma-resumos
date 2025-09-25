<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\enviarMensagem.php
session_start();
include "../../baseDados/basedados.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['id_utilizador'])) {
        echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
        exit();
    }

    $id_remetente = $_SESSION['id_utilizador'];
    $id_destinatario = $_POST['id_destinatario'] ?? null;
    $id_grupo = $_POST['id_grupo'] ?? null;
    $mensagem = trim($_POST['mensagem']);

    if (empty($mensagem)) {
        echo json_encode(["success" => false, "message" => "A mensagem não pode estar vazia."]);
        exit();
    }

    $queryInserirMensagem = "INSERT INTO mensagens (id_remetente, id_destinatario, id_grupo, mensagem) 
                             VALUES ('$id_remetente', " . ($id_destinatario ? "'$id_destinatario'" : "NULL") . ", " . ($id_grupo ? "'$id_grupo'" : "NULL") . ", '$mensagem')";
    if (mysqli_query($conn, $queryInserirMensagem)) {
        echo json_encode(["success" => true, "message" => "Mensagem enviada com sucesso."]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao enviar mensagem."]);
    }
    exit();
}

echo json_encode(["success" => false, "message" => "Método inválido."]);