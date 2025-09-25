<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";
header('Content-Type: application/json');

if (!isset($_SESSION["id_utilizador"])) {
    echo json_encode(['success' => false]);
    exit();
}
$id_utilizador = $_SESSION["id_utilizador"];
$id_evento = intval($_POST['id_evento'] ?? 0);

if ($id_evento > 0) {
    $sql = "DELETE FROM calendario_eventos WHERE id_evento = $id_evento AND id_utilizador = $id_utilizador";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
        exit();
    }
}
echo json_encode(['success' => false]);
