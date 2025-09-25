<?php
include "../../baseDados/basedados.php";

if (isset($_GET['curso'])) {
    $id_curso = (int)$_GET['curso'];
    $sql = "SELECT nome FROM disciplinas WHERE id_curso = $id_curso";
    $result = mysqli_query($conn, $sql);

    $disciplinas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $disciplinas[] = $row;
    }

    echo json_encode($disciplinas);
}
?>