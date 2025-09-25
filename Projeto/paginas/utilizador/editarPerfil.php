<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";


// Verificar se o utilizador está logado e tem permissões adequadas
if (!isset($_SESSION["id_utilizador"]) || !isset($_SESSION["nivel"]) || $_SESSION["nivel"] != 1) {
    header("Location: erro.php");
    exit();
}

// Recuperar os dados da sessão
$id_utilizador = $_SESSION["id_utilizador"];
$nome = $_SESSION['nome'];
$email = $_SESSION['email'];
$id_escola = $_SESSION['id_escola'];
$id_curso = $_SESSION['id_curso'];
$data_nascimento = $_SESSION['data_nascimento'] ?? '';
$senha = $_SESSION['senha'];

// Obter lista de escolas e cursos para os dropdowns
$escolas = [];
$cursos = [];
$sql_escolas = "SELECT id_escola, nome FROM escolas";
$result_escolas = mysqli_query($conn, $sql_escolas);
while ($row = mysqli_fetch_assoc($result_escolas)) {
    $escolas[$row['id_escola']] = $row['nome'];
}

$sql_cursos = "SELECT id_curso, nome FROM cursos";
$result_cursos = mysqli_query($conn, $sql_cursos);
while ($row = mysqli_fetch_assoc($result_cursos)) {
    $cursos[$row['id_curso']] = $row['nome'];
}

// Atualizar o perfil ao submeter o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome = mysqli_real_escape_string($conn, $_POST['nome'] ?? $nome);
    $novo_email = mysqli_real_escape_string($conn, $_POST['email'] ?? $email);
    $nova_escola = mysqli_real_escape_string($conn, $_POST['id_escola'] ?? $id_escola);
    $novo_curso = mysqli_real_escape_string($conn, $_POST['id_curso'] ?? $id_curso);
    $nova_data_nascimento = mysqli_real_escape_string($conn, $_POST['data_nascimento'] ?? $data_nascimento);

    // Só atualiza a senha se foi fornecida uma nova
    $nova_senha = $senha;
    if (!empty($_POST['senha'])) {
        $nova_senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    }

    // Atualizar os dados no banco de dados
    $sql_update = "UPDATE utilizadores 
                   SET 
                       nome = '$novo_nome', 
                       email = '$novo_email', 
                       id_escola = '$nova_escola', 
                       id_curso = '$novo_curso', 
                       data_nascimento = '$nova_data_nascimento', 
                       senha = '$nova_senha' 
                   WHERE id_utilizador = '$id_utilizador'";

    if (mysqli_query($conn, $sql_update)) {
        // Atualizar as variáveis de sessão
        $_SESSION['nome'] = $novo_nome;
        $_SESSION['email'] = $novo_email;
        $_SESSION['id_escola'] = $nova_escola;
        $_SESSION['id_curso'] = $novo_curso;
        $_SESSION['data_nascimento'] = $nova_data_nascimento;
        $_SESSION['senha'] = $nova_senha;

        // Redirecionar para o perfil
        header("Location: perfil.php");
        exit();
    } else {
        $erro = "Erro ao atualizar o perfil: " . mysqli_error($conn);
    }
}

// Fechar a conexão com o banco de dados
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma | Editar Perfil</title>
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
            line-height: 1.6;
        }

        /* Header Moderno */
        header {
            background: white;
            box-shadow: var(--box-shadow);
            padding: 0.8rem 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary);
            cursor: pointer;
            transition: var(--transition);
        }

        .menu-toggle:hover {
            color: var(--primary-dark);
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-actions .btn {
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
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

        /* Sidebar Moderno */
        .sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 999;
            transition: var(--transition);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .sidebar-header h3 {
            color: var(--primary);
            font-size: 1.3rem;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            cursor: pointer;
        }

        .nav-menu {
            list-style: none;
            flex: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1rem;
            color: var(--dark);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .nav-link i {
            width: 24px;
            text-align: center;
        }

        /* Conteúdo Principal */
        .main-content {
            margin-top: 80px;
            padding: 2rem;
            transition: var(--transition);
        }

        /* Formulário de Edição Moderno */
        .edit-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .edit-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .edit-header h2 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .edit-header p {
            color: var(--gray);
        }

        .edit-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
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

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }

        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 0.95rem;
            text-decoration: none;
            border: none;
        }

        .btn-save {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .btn-save:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }

        .btn-cancel {
            background: var(--light-gray);
            color: var(--dark);
        }

        .btn-cancel:hover {
            background: #e2e6ea;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            cursor: pointer;
        }

        .error-message {
            color: var(--danger);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .edit-form {
                grid-template-columns: 1fr;
            }

            header {
                padding: 0.8rem 1rem;
            }

            .logo h1 {
                display: none;
            }

            .main-content {
                padding: 1rem;
            }

            .edit-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1>QuickNote</h1>
        </div>

        <div class="user-actions">
            <a href="perfil.php" class="btn btn-outline">
                <i class="fas fa-user"></i>
                <span class="desktop-only">Perfil</span>
            </a>
            <a href="logout.php" class="btn btn-primary">
                <i class="fas fa-sign-out-alt"></i>
                <span class="desktop-only">Sair</span>
            </a>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Menu</h3>
            <button class="close-btn" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="paginaInicioUtilizador.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Página Inicial</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="perfil.php" class="nav-link active">
                    <i class="fas fa-user"></i>
                    <span>Meu Perfil</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="listaAmigos.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Amigos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="mensagens.php" class="nav-link">
                    <i class="fas fa-comments"></i>
                    <span>Mensagens</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="favoritos.php" class="nav-link">
                    <i class="fas fa-star"></i>
                    <span>Favoritos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="meusConteudos.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Meus Conteúdos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="calendario.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendário</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Conteúdo Principal -->
    <div class="main-content" id="mainContent">
        <div class="edit-container">
            <div class="edit-header">
                <h2>Editar Perfil</h2>
                <p>Atualize suas informações pessoais</p>
            </div>

            <form method="POST" action="editarPerfil.php" class="edit-form">
                <div class="form-group">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="form-control"
                        value="<?= htmlspecialchars($nome) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="form-group">
                    <label for="id_escola" class="form-label">Escola</label>
                    <select id="id_escola" name="id_escola" class="form-control" required>
                        <?php foreach ($escolas as $id => $nome_escola): ?>
                            <option value="<?= $id ?>" <?= $id == $id_escola ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nome_escola) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_curso" class="form-label">Curso</label>
                    <select id="id_curso" name="id_curso" class="form-control" required>
                        <?php foreach ($cursos as $id => $nome_curso): ?>
                            <option value="<?= $id ?>" <?= $id == $id_curso ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nome_curso) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" class="form-control"
                        value="<?= htmlspecialchars($data_nascimento) ?>">
                </div>

                <div class="form-group password-toggle">
                    <label for="senha" class="form-label">Nova Senha (deixe em branco para não alterar)</label>
                    <input type="password" id="senha" name="senha" class="form-control">
                    <i class="fas fa-eye password-toggle-icon" onclick="togglePassword()"></i>
                </div>

                <div class="form-actions">
                    <a href="perfil.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>

                <?php if (isset($erro)): ?>
                    <div class="form-group full-width">
                        <div class="error-message" style="display: block;">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="fecharTodosModais()"></div>

    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');

            if (sidebar.classList.contains('open')) {
                mainContent.style.marginLeft = '300px';
            } else {
                mainContent.style.marginLeft = '0';
            }
        }

        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('senha');
            const icon = document.querySelector('.password-toggle-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Fechar todos os modais
        function fecharTodosModais() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('active');
            document.getElementById('mainContent').style.marginLeft = '0';
        }
    </script>
</body>

</html>