<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\buscarDisciplinas.php
include "../../baseDados/basedados.php";
header('Content-Type: application/json');
$id_curso = intval($_GET['curso_id']);
$res = mysqli_query($conn, "SELECT id_disciplina, nome FROM disciplinas WHERE id_curso = $id_curso");
$disciplinas = [];
while ($row = mysqli_fetch_assoc($res)) {
    $disciplinas[] = $row;
}
echo json_encode($disciplinas);
mysqli_close($conn);
