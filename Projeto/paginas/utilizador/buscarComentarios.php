<?php
include "../../baseDados/basedados.php";

if (isset($_GET['id_conteudo'])) {
    $id_conteudo = $_GET['id_conteudo'];

    $query = "SELECT c.texto, u.nome 
              FROM comentarios c
              JOIN utilizadores u ON c.id_utilizador = u.id_utilizador
              WHERE c.id_conteudo = '$id_conteudo'
              ORDER BY c.data_comentario DESC";
    $result = mysqli_query($conn, $query);

    $comentarios = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comentarios[] = $row;
    }

    echo json_encode($comentarios);
}
