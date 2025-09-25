<?php
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

$id_utilizador = $_SESSION["id_utilizador"];
$id_conteudo = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Buscar dados do conteúdo
$sql = "SELECT * FROM conteudos WHERE id_conteudo = $id_conteudo AND id_utilizador = $id_utilizador";
$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
    echo "<p style='color:red'>Conteúdo não encontrado ou sem permissão.</p>";
    exit();
}
$conteudo = mysqli_fetch_assoc($res);

// Buscar disciplinas para select
$disciplinas = mysqli_query($conn, "SELECT d.id_disciplina, d.nome, cu.nome AS curso 
    FROM disciplinas d 
    JOIN cursos cu ON d.id_curso = cu.id_curso 
    ORDER BY cu.nome, d.nome");

$erro = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $id_disciplina = intval($_POST['id_disciplina']);

    if (!$titulo || !$descricao || !$id_disciplina) {
        $erro = "Preencha todos os campos!";
    } else {
        $sqlUpdate = "UPDATE conteudos SET titulo='$titulo', descricao='$descricao', id_disciplina=$id_disciplina WHERE id_conteudo=$id_conteudo AND id_utilizador=$id_utilizador";
        if (mysqli_query($conn, $sqlUpdate)) {
            header("Location: meusConteudos.php?edit=ok");
            exit();
        } else {
            $erro = "Erro ao atualizar conteúdo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Conteúdo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f7fb; color: #212529; }
        .container { max-width: 500px; margin: 60px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 2rem; }
        h2 { color: #4361ee; margin-bottom: 1.5rem; }
        label { font-weight: 500; margin-top: 1rem; display: block; }
        input, textarea, select { width: 100%; padding: 0.7rem; border-radius: 7px; border: 1px solid #e9ecef; margin-top: 0.3rem; margin-bottom: 1rem; }
        .btn { background: #4361ee; color: #fff; border: none; padding: 0.7rem 2rem; border-radius: 7px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #3a56d4; }
        .erro { color: #f72585; margin-bottom: 1rem; }
        .back { display: inline-block; margin-bottom: 1rem; color: #4361ee; text-decoration: none; }
        .back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="meusConteudos.php" class="back">&larr; Voltar</a>
        <h2>Editar Conteúdo</h2>
        <?php if ($erro): ?><div class="erro"><?= $erro ?></div><?php endif; ?>
        <form method="post">
            <label for="titulo">Título</label>
            <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($conteudo['titulo']) ?>" required>

            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="4" required><?= htmlspecialchars($conteudo['descricao']) ?></textarea>

            <label for="id_disciplina">Disciplina</label>
            <select name="id_disciplina" id="id_disciplina" required>
                <option value="">Selecione...</option>
                <?php while($d = mysqli_fetch_assoc($disciplinas)): ?>
                    <option value="<?= $d['id_disciplina'] ?>" <?= $conteudo['id_disciplina']==$d['id_disciplina']?'selected':'' ?>>
                        <?= htmlspecialchars($d['curso']) ?> - <?= htmlspecialchars($d['nome']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="btn">Guardar Alterações</button>
        </form>
    </div>
</body>
</html>