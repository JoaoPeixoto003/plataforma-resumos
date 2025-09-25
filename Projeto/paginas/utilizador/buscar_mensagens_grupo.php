<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (isset($_GET["id_grupo"]) && isset($_SESSION["id_utilizador"])) {
    $id_grupo = $_GET["id_grupo"];
    $id_utilizador = $_SESSION["id_utilizador"];
    
    // Verificar se o usuário é membro do grupo
    $query_membro = "SELECT 1 FROM membros_grupo WHERE id_grupo = '$id_grupo' AND id_utilizador = '$id_utilizador'";
    $result_membro = mysqli_query($conn, $query_membro);
    
    if (mysqli_num_rows($result_membro) > 0) {
        // Buscar mensagens do grupo com nome do remetente
        $query = "SELECT m.*, u.nome as remetente_nome 
                 FROM mensagens m
                 JOIN utilizadores u ON m.id_remetente = u.id_utilizador
                 WHERE m.id_grupo = '$id_grupo'
                 ORDER BY m.data_envio ASC";
        $result = mysqli_query($conn, $query);
        
        $mensagens = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $mensagens[] = [
                'id_remetente' => $row['id_remetente'],
                'remetente_nome' => $row['remetente_nome'],
                'mensagem' => $row['mensagem'],
                'data_envio' => date('H:i', strtotime($row['data_envio']))
            ];
        }
        
        echo json_encode(["success" => true, "mensagens" => $mensagens]);
    } else {
        echo json_encode(["success" => false, "message" => "Você não é membro deste grupo"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Parâmetros inválidos"]);
}
?>