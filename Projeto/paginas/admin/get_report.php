<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\admin\get_report.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (!isset($_SESSION['id_utilizador'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$query = "SELECT r.*, 
                 u.nome AS reportador,
                 ur.nome AS nome_reportado,
                 c.titulo AS titulo_conteudo,
                 a.nome AS admin_resposta
          FROM reports r
          LEFT JOIN utilizadores u ON r.id_utilizador = u.id_utilizador
          LEFT JOIN utilizadores ur ON r.id_utilizador_reportado = ur.id_utilizador
          LEFT JOIN conteudos c ON r.id_conteudo = c.id_conteudo
          LEFT JOIN utilizadores a ON r.id_admin_resposta = a.id_utilizador
          WHERE r.id_report = $id
          LIMIT 1";
$res = mysqli_query($conn, $query);
if ($res && $report = mysqli_fetch_assoc($res)) {
    echo json_encode($report);
} else {
    echo json_encode(['error' => 'Report não encontrado']);
}
