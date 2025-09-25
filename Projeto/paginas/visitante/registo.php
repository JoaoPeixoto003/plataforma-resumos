<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\visitante\registo.php
session_start();
include "../../baseDados/basedados.php";

if (isset($_SESSION["nivel"]) && $_SESSION["nivel"] > 0) {
    header("Location: erro.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar e sanitizar inputs
    $nome = mysqli_real_escape_string($conn, $_POST["nome"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $data_nascimento = mysqli_real_escape_string($conn, $_POST["data_nascimento"]);
    $tipo = $_POST["tipo"];

    // Guardar na sessão
    $_SESSION["nome"] = $nome;
    $_SESSION["email"] = $email;
    $_SESSION["senha"] = $senha;
    $_SESSION["data_nascimento"] = $data_nascimento;
    $_SESSION["tipo"] = $tipo;

    if ($tipo == "aluno") {
        $id_escola = (int)$_POST["escola"];
        $_SESSION["escola"] = $id_escola;
        header("Location: registo_curso.php");
    } else {
        // Registro como não-aluno
        $sql = "INSERT INTO utilizadores (nome, email, senha, data_nascimento, tipo, nivel) 
                VALUES ('$nome', '$email', '$senha', '$data_nascimento', 'nao_aluno', 1)";

        if (mysqli_query($conn, $sql)) {
            session_unset();
            session_destroy();
            header("Location: paginaInicio.php");
        } else {
            $erro = "Erro ao registar: " . mysqli_error($conn);
        }
    }
    exit();
}

$escolas = mysqli_query($conn, "SELECT * FROM escolas WHERE ativa = TRUE");
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo - Plataforma</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .register-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h1 {
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

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .radio-group {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .school-fields {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: var(--light);
            border-radius: var(--border-radius);
        }

        .error-message {
            color: #dc3545;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .register-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Criar Conta</h1>
            <p>Preencha os dados para se registar na plataforma</p>
        </div>

        <form method="POST" action="registo.php">
            <div class="form-group">
                <label for="nome" class="form-label">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" id="senha" name="senha" class="form-control" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required max="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de Utilizador</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="tipo" value="aluno" checked onchange="toggleSchoolFields(true)">
                        <span>Aluno</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="tipo" value="nao_aluno" onchange="toggleSchoolFields(false)">
                        <span>Não Aluno</span>
                    </label>
                </div>
            </div>

            <div id="schoolFields" class="school-fields">
                <div class="form-group">
                    <label for="escola" class="form-label">Escola</label>
                    <select id="escola" name="escola" class="form-control">
                        <option value="">Selecione sua escola</option>
                        <?php while ($escola = mysqli_fetch_assoc($escolas)): ?>
                            <option value="<?= $escola['id_escola'] ?>"><?= htmlspecialchars($escola['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <?php if (isset($erro)): ?>
                <div class="error-message"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-block">Continuar</button>
        </form>

        <form action="registo_escola_curso.php" method="GET" style="margin-top: 1rem;">
            <button type="submit" class="btn btn-primary btn-block">Registrar Nova Escola ou Curso</button>
        </form>

        <form action="paginaInicio.php" method="GET" style="margin-top: 1rem;">
            <button type="submit" class="btn btn-primary btn-block">Voltar</button>
        </form>

        <div class="login-link">
            Já tem uma conta? <a href="login.php">Faça login</a>
        </div>
    </div>

    <script>
        function toggleSchoolFields(show) {
            const schoolFields = document.getElementById('schoolFields');
            const escolaSelect = document.getElementById('escola');

            if (show) {
                schoolFields.style.display = 'block';
                escolaSelect.required = true;
            } else {
                schoolFields.style.display = 'none';
                escolaSelect.required = false;
            }
        }

        // Mostrar campos da escola por padrão
        document.addEventListener('DOMContentLoaded', function() {
            toggleSchoolFields(true);
        });
    </script>
</body>

</html>