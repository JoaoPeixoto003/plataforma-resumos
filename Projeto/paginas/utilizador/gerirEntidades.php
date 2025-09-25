<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\gerirEntidades.php
session_start();
include "../../baseDados/basedados.php";

// Verificar se é utilizador autenticado e com nível de utilizador
if (!isset($_SESSION["id_utilizador"]) || $_SESSION["nivel"] != 1) {
    header("Location: ../visitante/login.php");
    exit();
}


// Buscar escolas para os selects
$escolas = mysqli_query($conn, "SELECT * FROM escolas WHERE ativa = 1");

$mensagem = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nova_escola'])) {
        $nome = mysqli_real_escape_string($conn, $_POST['nome_escola']);
        if (mysqli_query($conn, "INSERT INTO escolas (nome, ativa) VALUES ('$nome', 1)")) {
            $mensagem = "Escola criada com sucesso!";
        } else {
            $mensagem = "Erro ao criar escola.";
        }
    }
    if (isset($_POST['novo_curso'])) {
        $nome = mysqli_real_escape_string($conn, $_POST['nome_curso']);
        $id_escola = intval($_POST['escola_curso']);
        if (mysqli_query($conn, "INSERT INTO cursos (nome, id_escola) VALUES ('$nome', $id_escola)")) {
            $mensagem = "Curso criado com sucesso!";
        } else {
            $mensagem = "Erro ao criar curso.";
        }
    }
    if (isset($_POST['nova_disciplina'])) {
        $nome = mysqli_real_escape_string($conn, $_POST['nome_disciplina']);
        $id_curso = intval($_POST['curso_disciplina']);
        if (mysqli_query($conn, "INSERT INTO disciplinas (nome, id_curso) VALUES ('$nome', $id_curso)")) {
            $mensagem = "Disciplina criada com sucesso!";
        } else {
            $mensagem = "Erro ao criar disciplina.";
        }
    }
    // Atualizar escolas após inserção
    $escolas = mysqli_query($conn, "SELECT * FROM escolas WHERE ativa = 1");
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Gerir Escolas, Cursos e Disciplinas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 40px auto;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .btn {
            background: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        .message {
            margin: 10px 0;
            color: green;
        }

        .voltar {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            text-decoration: underline;
        }
    </style>
    <script>
        function carregarCursos() {
            const escolaId = document.getElementById("escola_disciplina").value;
            const cursoSelect = document.getElementById("curso_disciplina");
            cursoSelect.innerHTML = '<option value="">Selecione um curso</option>';
            if (escolaId) {
                fetch('buscarCursos.php?escola_id=' + escolaId)
                    .then(response => response.json())
                    .then(cursos => {
                        cursos.forEach(curso => {
                            const option = document.createElement("option");
                            option.value = curso.id_curso;
                            option.textContent = curso.nome;
                            cursoSelect.appendChild(option);
                        });
                    });
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <h2>Gerir Escolas, Cursos e Disciplinas</h2>
        <?php if ($mensagem): ?>
            <div class="message"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <!-- Criar Escola -->
        <form method="POST">
            <label for="nome_escola">Nova Escola</label>
            <input type="text" name="nome_escola" id="nome_escola" required>
            <button type="submit" name="nova_escola" class="btn">Criar Escola</button>
        </form>
        <hr>

        <!-- Criar Curso -->
        <form method="POST">
            <label for="escola_curso">Escolha a Escola</label>
            <select name="escola_curso" id="escola_curso" required>
                <option value="">Selecione uma escola</option>
                <?php mysqli_data_seek($escolas, 0);
                while ($esc = mysqli_fetch_assoc($escolas)): ?>
                    <option value="<?= $esc['id_escola'] ?>"><?= htmlspecialchars($esc['nome']) ?></option>
                <?php endwhile; ?>
            </select>
            <label for="nome_curso">Novo Curso</label>
            <input type="text" name="nome_curso" id="nome_curso" required>
            <button type="submit" name="novo_curso" class="btn">Criar Curso</button>
        </form>
        <hr>

        <!-- Criar Disciplina -->
        <form method="POST">
            <label for="escola_disciplina">Escolha a Escola</label>
            <select name="escola_disciplina" id="escola_disciplina" onchange="carregarCursos()" required>
                <option value="">Selecione uma escola</option>
                <?php mysqli_data_seek($escolas, 0);
                while ($esc = mysqli_fetch_assoc($escolas)): ?>
                    <option value="<?= $esc['id_escola'] ?>"><?= htmlspecialchars($esc['nome']) ?></option>
                <?php endwhile; ?>
            </select>
            <label for="curso_disciplina">Escolha o Curso</label>
            <select name="curso_disciplina" id="curso_disciplina" required>
                <option value="">Selecione um curso</option>
            </select>
            <label for="nome_disciplina">Nova Disciplina</label>
            <input type="text" name="nome_disciplina" id="nome_disciplina" required>
            <button type="submit" name="nova_disciplina" class="btn">Criar Disciplina</button>
        </form>
        <a href="uploadConteudo.php" class="voltar">Voltar ao upload de conteúdos</a>
    </div>
</body>

</html>