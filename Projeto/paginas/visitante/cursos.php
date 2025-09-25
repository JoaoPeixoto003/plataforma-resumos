<?php
include "../../baseDados/basedados.php";

if (isset($_GET['escola'])) {
    $id_escola = (int)$_GET['escola'];
    $sql = "SELECT id_curso, nome FROM cursos WHERE id_escola = $id_escola";
    $result = mysqli_query($conn, $sql);

    $cursos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $cursos[] = $row;
    }

    echo json_encode($cursos);
}
?>