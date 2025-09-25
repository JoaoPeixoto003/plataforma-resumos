<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\adicionarFavorito.php
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

header('Content-Type: application/json');

if (!isset($_SESSION['id_utilizador'])) {
    echo json_encode(['success' => false, 'message' => 'Precisa de login.']);
    exit;
}

$id_utilizador = $_SESSION['id_utilizador'];
$id_conteudo = intval($_POST['id_conteudo']);
$id_pasta = intval($_POST['id_pasta'] ?? 0);

// Verifica se já existe
$check = mysqli_query($conn, "SELECT * FROM favoritos WHERE id_utilizador = $id_utilizador AND id_conteudo = $id_conteudo");
if (mysqli_num_rows($check) > 0) {
    // Atualiza pasta se já existir
    mysqli_query($conn, "UPDATE favoritos SET id_pasta = $id_pasta WHERE id_utilizador = $id_utilizador AND id_conteudo = $id_conteudo");
    echo json_encode(['success' => true]);
    exit;
}

// Adiciona favorito
$sql = "INSERT INTO favoritos (id_utilizador, id_conteudo, id_pasta) VALUES ($id_utilizador, $id_conteudo, $id_pasta)";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao guardar favorito.']);
}
// Fecha a conexão
mysqli_close($conn);
