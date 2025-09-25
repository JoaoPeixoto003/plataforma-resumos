<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\buscarCursos.php
include "../../baseDados/basedados.php";
header('Content-Type: application/json');
$id_escola = intval($_GET['escola_id']);
$res = mysqli_query($conn, "SELECT id_curso, nome FROM cursos WHERE id_escola = $id_escola");
$cursos = [];
while ($row = mysqli_fetch_assoc($res)) {
    $cursos[] = $row;
}
echo json_encode($cursos);
mysqli_close($conn);
