<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\uploadConteudo.php
session_start();
include "../../baseDados/basedados.php";

// Verificar se é admin
if (!isset($_SESSION["id_utilizador"]) || $_SESSION["nivel"] != 1) {
    header("Location: ../visitante/login.php");
    exit();
}


// Buscar escolas
$escolas = mysqli_query($conn, "SELECT * FROM escolas WHERE ativa = 1");

// Processar o formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['titulo']) && !empty($_POST['descricao']) && !empty($_POST['disciplina'])) {
        $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
        $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
        $id_disciplina = intval($_POST['disciplina']);
        $id_utilizador = $_SESSION['id_utilizador'];
        $data_upload = date("Y-m-d H:i:s");

        if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] == 0) {
            $ficheiroNome = $_FILES['ficheiro']['name'];
            $ficheiroTmp = $_FILES['ficheiro']['tmp_name'];
            $ficheiroDestino = "../../uploads/" . $ficheiroNome;

            if (!is_dir("../../uploads")) {
                mkdir("../../uploads", 0777, true);
            }

            if (move_uploaded_file($ficheiroTmp, $ficheiroDestino)) {
                $query = "INSERT INTO conteudos (titulo, descricao, formato, data_upload, id_utilizador, id_disciplina) 
                          VALUES ('$titulo', '$descricao', '$ficheiroNome', '$data_upload', '$id_utilizador', '$id_disciplina')";
                if (mysqli_query($conn, $query)) {
                    echo "<script>alert('Conteúdo enviado com sucesso!'); window.location.href = 'paginaInicioUtilizador.php';</script>";
                } else {
                    echo "<script>alert('Erro ao enviar o conteúdo.');</script>";
                }
            } else {
                echo "<script>alert('Erro ao fazer upload do ficheiro.');</script>";
            }
        } else {
            echo "<script>alert('Por favor, selecione um ficheiro para enviar.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Upload de Conteúdos</title>
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
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
        }

        .btn-cancel {
            background: #ccc;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .btn-upload {
            background: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .btn-cancel:hover {
            background: #bbb;
        }

        .btn-upload:hover {
            background: #218838;
        }

        .btn-link {
            background: none;
            border: none;
            color: #007bff;
            text-decoration: underline;
            cursor: pointer;
            margin-top: 20px;
            font-size: 1rem;
            display: inline-block;
        }

        .btn-link:hover {
            color: #0056b3;
        }
    </style>
    <script>
        function carregarCursos() {
            const escolaId = document.getElementById("escola").value;
            const cursoSelect = document.getElementById("curso");
            cursoSelect.innerHTML = '<option value="">Selecione um curso</option>';
            document.getElementById('disciplina').innerHTML = '<option value="">Selecione uma disciplina</option>';
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

        function carregarDisciplinas() {
            const cursoId = document.getElementById("curso").value;
            const disciplinaSelect = document.getElementById("disciplina");
            disciplinaSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';
            if (cursoId) {
                fetch('buscarDisciplinas.php?curso_id=' + cursoId)
                    .then(response => response.json())
                    .then(disciplinas => {
                        disciplinas.forEach(disciplina => {
                            const option = document.createElement("option");
                            option.value = disciplina.id_disciplina;
                            option.textContent = disciplina.nome;
                            disciplinaSelect.appendChild(option);
                        });
                    });
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <h2>Upload de Conteúdos</h2>
        <form action="uploadConteudo.php" method="POST" enctype="multipart/form-data">
            <label for="titulo">Título*</label>
            <input type="text" id="titulo" name="titulo" required>

            <label for="descricao">Descrição*</label>
            <textarea id="descricao" name="descricao" rows="4" required></textarea>

            <label for="escola">Escola*</label>
            <select id="escola" name="escola" onchange="carregarCursos()" required>
                <option value="">Selecione uma escola</option>
                <?php mysqli_data_seek($escolas, 0);
                while ($esc = mysqli_fetch_assoc($escolas)): ?>
                    <option value="<?= $esc['id_escola'] ?>"><?= htmlspecialchars($esc['nome']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="curso">Curso*</label>
            <select id="curso" name="curso" onchange="carregarDisciplinas()" required>
                <option value="">Selecione um curso</option>
            </select>

            <label for="disciplina">Disciplina*</label>
            <select id="disciplina" name="disciplina" required>
                <option value="">Selecione uma disciplina</option>
            </select>

            <label for="ficheiro">Upload de um ficheiro*</label>
            <input type="file" id="ficheiro" name="ficheiro" required>

            <div class="buttons">
                <button type="button" class="btn-cancel" onclick="window.location.href='paginaInicioUtilizador.php'">Cancelar</button>
                <button type="submit" class="btn-upload">Upload</button>
            </div>
        </form>
        <a href="gerirEntidades.php" class="btn-link">Criar nova Escola, Curso ou Disciplina</a>
    </div>
</body>

</html>