<?php
session_start();
include "../../baseDados/basedados.php";

if (isset($_SESSION["nivel"]) && $_SESSION["nivel"] > 0) {
    header("Location: ../erro.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senhaArmazenada = $_POST['senha'];

    $query = "SELECT * FROM utilizadores WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $senha = $user['senha'];
        $_SESSION['senha'] = $senha;


        if ($user['nivel'] == 1) {

            if (password_verify($senhaArmazenada, $senha)) {
                $_SESSION['id_utilizador'] = $user['id_utilizador'];
                $_SESSION['nome'] = $user['nome'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['id_escola'] = $user['id_escola'];
                $_SESSION['id_curso'] = $user['id_curso'];
                $_SESSION['nivel'] = $user['nivel'];
                $_SESSION['data_nascimento'] = $user['data_nascimento'];

                header("Location: ../utilizador/paginaInicioUtilizador.php");
                exit();
            } else {
                $error = "Senha incorreta. Tente novamente.";
            }
        }elseif ($user['nivel'] == 2) {
            if (password_verify($senhaArmazenada, $senha)) {
                $_SESSION['id_utilizador'] = $user['id_utilizador'];
                $_SESSION['nome'] = $user['nome'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nivel'] = $user['nivel'];

                header("Location: ../admin/admin.php");
                exit();
            } else {
                $error = "Senha incorreta. Tente novamente.";
            }
        } else {
            $error = "Tipo de utilizador inválido.";
        }
    } else {
        $error = "Email não encontrado. Tente novamente.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Plataforma Académica</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
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
            display: flex;
            min-height: 100vh;
            line-height: 1.6;
        }

        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .logo {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1;
        }

        .logo i {
            font-size: 2.5rem;
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .login-left h2 {
            font-size: 2.2rem;
            margin-bottom: 1rem;
            font-weight: 700;
            z-index: 1;
        }

        .login-left p {
            max-width: 500px;
            opacity: 0.9;
            margin-bottom: 3rem;
            z-index: 1;
        }

        .login-right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-form-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            position: relative;
        }

        .login-form-container h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: var(--dark);
            text-align: center;
        }

        .login-form-container p {
            color: var(--gray);
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 3rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        .btn {
            width: 100%;
            padding: 0.8rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 1rem;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
            color: var(--gray);
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: var(--danger);
            background-color: rgba(247, 37, 133, 0.1);
            padding: 0.8rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }

        @media (max-width: 992px) {
            .login-left {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .login-form-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-left">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                <h1>QuickNote</h1>
            </div>
            <h2>Bem-vindo de volta!</h2>
            <p>Acesse sua conta para explorar conteúdos acadêmicos, conectar-se com colegas e colaborar em projetos.</p>
        </div>

        <div class="login-right">
            <div class="login-form-container">
                <h2>Entrar na conta</h2>
                <p>Use seu email e senha para acessar a plataforma</p>

                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="senha" name="senha" class="form-control" placeholder="Sua senha" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </form>

                <div class="login-footer">
                    <p>Não tem uma conta? <a href="registo.php">Crie uma agora</a></p>
                    <p>ou</p>
                    <a href="paginaInicio.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Voltar à página inicial
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>