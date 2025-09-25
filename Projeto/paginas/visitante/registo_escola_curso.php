<?php
session_start();
include "../../baseDados/basedados.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["nova_escola"])) {
        // Registrar nova escola
        $nome_escola = mysqli_real_escape_string($conn, $_POST["nome_escola"]);
        $sql = "INSERT INTO escolas (nome, ativa) VALUES ('$nome_escola', 1)";
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Escola registrada com sucesso!";
        } else {
            $erro = "Erro ao registrar escola: " . mysqli_error($conn);
        }
    } elseif (isset($_POST["novo_curso"])) {
        // Registrar novo curso
        $id_escola = (int)$_POST["escola"];
        $nome_curso = mysqli_real_escape_string($conn, $_POST["nome_curso"]);
        $sql = "INSERT INTO cursos (nome, id_escola) VALUES ('$nome_curso', $id_escola)";
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Curso registrado com sucesso!";
        } else {
            $erro = "Erro ao registrar curso: " . mysqli_error($conn);
        }
    } elseif (isset($_POST["nova_disciplina"])) {
        // Registrar nova disciplina
        $id_curso = (int)$_POST["curso"];
        $nome_disciplina = mysqli_real_escape_string($conn, $_POST["nome_disciplina"]);
        $sql = "INSERT INTO disciplinas (nome, id_curso) VALUES ('$nome_disciplina', $id_curso)";
        if (mysqli_query($conn, $sql)) {
            $mensagem = "Disciplina registrada com sucesso!";
        } else {
            $erro = "Erro ao registrar disciplina: " . mysqli_error($conn);
        }
    }
}

$escolas = mysqli_query($conn, "SELECT * FROM escolas WHERE ativa = TRUE");
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Escola, Curso ou Disciplina</title>
    <style>
        /* Estilos básicos */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 4px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .disciplinas-container {
            margin-top: 1rem;
            padding: 1rem;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Registrar Escola, Curso ou Disciplina</h1>

        <?php if (isset($mensagem)): ?>
            <div class="message success"><?= htmlspecialchars($mensagem) ?></div>
        <?php elseif (isset($erro)): ?>
            <div class="message error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <!-- Formulário para registrar nova escola -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="nome_escola" class="form-label">Nome da Nova Escola</label>
                <input type="text" id="nome_escola" name="nome_escola" class="form-control" required>
            </div>
            <button type="submit" name="nova_escola" class="btn">Registrar Escola</button>
        </form>

        <hr>

        <!-- Formulário para registrar novo curso -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="escola" class="form-label">Selecione a Escola</label>
                <select id="escola" name="escola" class="form-control" required onchange="carregarCursos(this.value)">
                    <option value="">Selecione uma escola</option>
                    <?php while ($escola = mysqli_fetch_assoc($escolas)): ?>
                        <option value="<?= $escola['id_escola'] ?>"><?= htmlspecialchars($escola['nome']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="nome_curso" class="form-label">Nome do Novo Curso</label>
                <input type="text" id="nome_curso" name="nome_curso" class="form-control" required>
            </div>
            <button type="submit" name="novo_curso" class="btn">Registrar Curso</button>
        </form>

        <hr>

        <!-- Formulário para registrar nova disciplina -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="curso" class="form-label">Selecione o Curso</label>
                <select id="curso" name="curso" class="form-control" required onchange="carregarDisciplinas(this.value)">
                    <option value="">Selecione um curso</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nome_disciplina" class="form-label">Nome da Nova Disciplina</label>
                <input type="text" id="nome_disciplina" name="nome_disciplina" class="form-control" required>
            </div>
            <button type="submit" name="nova_disciplina" class="btn">Registrar Disciplina</button>
        </form>

        <button type="button" class="btn" style="margin-top: 1rem;" onclick="window.location.href='registo.php'">Voltar</button>

        <!-- Container para exibir disciplinas -->
        <div id="disciplinasContainer" class="disciplinas-container" style="display: none;">
            <h3>Disciplinas do Curso</h3>
            <ul id="disciplinasLista"></ul>
        </div>
    </div>

    <script>
        function carregarCursos(idEscola) {
            const cursoSelect = document.getElementById('curso');
            cursoSelect.innerHTML = '<option value="">Selecione um curso</option>';

            if (!idEscola) return;

            fetch(`cursos.php?escola=${idEscola}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(curso => {
                        const option = document.createElement('option');
                        option.value = curso.id_curso;
                        option.textContent = curso.nome;
                        cursoSelect.appendChild(option);
                    });
                });
        }

        function carregarDisciplinas(idCurso) {
            const disciplinasContainer = document.getElementById('disciplinasContainer');
            const lista = document.getElementById('disciplinasLista');
            lista.innerHTML = '';

            if (!idCurso) {
                disciplinasContainer.style.display = 'none';
                return;
            }

            fetch(`disciplinas.php?curso=${idCurso}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        data.forEach(disciplina => {
                            const li = document.createElement('li');
                            li.textContent = disciplina.nome;
                            lista.appendChild(li);
                        });
                        disciplinasContainer.style.display = 'block';
                    } else {
                        disciplinasContainer.style.display = 'none';
                    }
                });
        }
    </script>
</body>

</html>