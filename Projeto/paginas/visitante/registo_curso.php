<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (isset($_SESSION["nivel"]) && $_SESSION["nivel"] > 0) {
    header("Location: erro.php");
    exit();
}

if (!isset($_SESSION["nome"]) || !isset($_SESSION["escola"])) {
    header("Location: registo.php");
    exit();
}

$nome = $_SESSION["nome"];
$email = $_SESSION["email"];
$senha = $_SESSION["senha"];
$data_nascimento = $_SESSION["data_nascimento"];
$id_escola = $_SESSION["escola"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_curso = (int)$_POST["curso"];

    $sql = "INSERT INTO utilizadores (nome, email, senha, data_nascimento, id_escola, id_curso, tipo, nivel) 
            VALUES ('$nome', '$email', '$senha', '$data_nascimento', '$id_escola', '$id_curso', 'aluno', 1)";

    if (mysqli_query($conn, $sql)) {
        session_unset();
        session_destroy();
        header("Location: paginaInicio.php");
        exit();
    } else {
        $erro = "Erro ao registar: " . mysqli_error($conn);
    }
}

$cursos = mysqli_query($conn, "SELECT * FROM cursos WHERE id_escola = $id_escola");
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Curso - Plataforma</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .course-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .course-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .course-header h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: rgba(67, 97, 238, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .error-message {
            color: #dc3545;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .course-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="course-container">
        <div class="course-header">
            <h1>Selecione seu Curso</h1>
            <p>Escolha o curso que você está frequentando</p>
        </div>

        <form method="POST" action="registo_curso.php">
            <div class="form-group">
                <label for="curso" class="form-label">Curso</label>
                <select id="curso" name="curso" class="form-control" required>
                    <option value="">Selecione seu curso</option>
                    <?php while ($curso = mysqli_fetch_assoc($cursos)): ?>
                        <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php if (isset($erro)): ?>
                <div class="error-message"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="action-buttons">
                <button type="submit" class="btn btn-primary btn-block">Completar Registro</button>
                <a href="registo.php" class="btn btn-outline btn-block">Voltar</a>
            </div>
        </form>
    </div>
</body>

</html>
<?php
mysqli_close($conn);
?>