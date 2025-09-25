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
    $id_pasta = $_POST['id_pasta'];
    $id_utilizador = $_SESSION["id_utilizador"];

    // Verificar se a pasta existe e pertence ao utilizador
    $checkQuery = "SELECT id_pasta FROM pastas_favoritos WHERE id_pasta = $id_pasta AND id_utilizador =  $id_utilizador";
    $stmt = mysqli_query($conn, $checkQuery);
    if (mysqli_num_rows($stmt) == 0) {
        echo json_encode(['success' => false, 'message' => 'Pasta não encontrada ou não pertence ao utilizador']);
        exit();
    }

    // Remover a pasta e os favoritos associados
    $deleteQuery = "DELETE FROM pastas_favoritos WHERE id_pasta = $id_pasta AND id_utilizador = $id_utilizador";
    $stmt = mysqli_query($conn, $deleteQuery);
    if ($stmt) {
        // Remover os favoritos associados à pasta
        $deleteFavQuery = "DELETE FROM favoritos WHERE id_pasta = $id_pasta AND id_utilizador = $id_utilizador";
        mysqli_query($conn, $deleteFavQuery);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover a pasta']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

mysqli_close($conn);
?>
