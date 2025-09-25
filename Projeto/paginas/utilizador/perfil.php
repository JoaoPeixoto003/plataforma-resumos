<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

if (isset($_SESSION["nivel"]) && $_SESSION["nivel"] != 1) {
    header("Location: erro.php");
    exit();
} else if (!isset($_SESSION["nivel"])) {
    header("Location: erro.php");
    exit();
}

$id_utilizador = $_SESSION["id_utilizador"];
$nome = $_SESSION['nome'];
$email = $_SESSION['email'];
$id_escola = $_SESSION['id_escola'];
$id_curso = $_SESSION['id_curso'];

// Obter nome da escola
$sql_escola = "SELECT nome FROM escolas WHERE id_escola = '$id_escola'";
$result_escola = mysqli_query($conn, $sql_escola);
$escola = ($result_escola && mysqli_num_rows($result_escola) > 0) ? mysqli_fetch_assoc($result_escola)['nome'] : "Não encontrada";

// Obter nome do curso
$sql_curso = "SELECT nome FROM cursos WHERE id_curso = '$id_curso'";
$result_curso = mysqli_query($conn, $sql_curso);
$curso = ($result_curso && mysqli_num_rows($result_curso) > 0) ? mysqli_fetch_assoc($result_curso)['nome'] : "Não encontrado";

// Obter estatísticas do usuário (exemplo)
$sql_stats = "SELECT 
    (SELECT COUNT(*) FROM conteudos WHERE id_utilizador = '$id_utilizador') as total_conteudos,
    (SELECT COUNT(*) FROM amizades WHERE (id_utilizador1 = '$id_utilizador' OR id_utilizador2 = '$id_utilizador') AND status = 'aceite') as total_amigos";
$result_stats = mysqli_query($conn, $sql_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Foto de perfil
$foto_perfil = "../../uploads/perfil_$id_utilizador.png";
if (!file_exists($foto_perfil)) {
    $foto_perfil = "../../uploads/default-profile.png";
}

// Upload da foto
$upload_erro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["nova_foto"])) {
    $target_dir = "../../uploads/";
    $target_file = $target_dir . "perfil_" . $id_utilizador . ".png";
    $imageFileType = strtolower(pathinfo($_FILES["nova_foto"]["name"], PATHINFO_EXTENSION));
    $check = getimagesize($_FILES["nova_foto"]["tmp_name"]);
    if ($check !== false) {
        if ($_FILES["nova_foto"]["size"] > 2 * 1024 * 1024) {
            $upload_erro = "A imagem não pode ter mais de 2MB.";
        } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            $upload_erro = "Apenas ficheiros JPG, JPEG ou PNG são permitidos.";
        } else {
            move_uploaded_file($_FILES["nova_foto"]["tmp_name"], $target_file);
            header("Location: perfil.php");
            exit();
        }
    } else {
        $upload_erro = "O ficheiro não é uma imagem válida.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma | Meu Perfil</title>
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

        .main-content {
            margin-top: 80px;
            padding: 2rem;
            transition: var(--transition);
        }

        .profile-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            max-width: 1000px;
            margin: 0 auto;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2.5rem;
            text-align: center;
            position: relative;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            transform: skewY(-4deg);
            transform-origin: top left;
        }

        .profile-header-content {
            position: relative;
            z-index: 1;
        }

        .profile-header h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .profile-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .profile-body {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        .profile-sidebar {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        .profile-photo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: var(--box-shadow);
            margin-top: -100px;
            background-color: var(--light);
            position: relative;
            z-index: 2;
            transition: var(--transition);
            cursor: pointer;
        }

        .profile-photo:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .change-photo-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .change-photo-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }

        .profile-stats {
            width: 100%;
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
        }

        .stats-title {
            font-size: 1rem;
            color: var(--gray);
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 0.8rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.3rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--gray);
            font-weight: 500;
        }

        .profile-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .profile-section {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
        }

        .section-title {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .section-title i {
            font-size: 1.1rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .detail-label {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 500;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            padding: 0.5rem 0;
            border-bottom: 1px dashed var(--light-gray);
        }

        .profile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-edit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .btn-edit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }

        .modal-bg {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-bg.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal {
            background: #fff;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            min-width: 320px;
            max-width: 95vw;
        }

        .modal h2 {
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .modal .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            position: absolute;
            top: 1rem;
            right: 1rem;
            cursor: pointer;
        }

        .modal form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .modal input[type="file"] {
            padding: 0.5rem;
        }

        .modal .btn {
            width: 100%;
        }

        .modal .error {
            color: var(--danger);
            font-size: 0.95rem;
            text-align: center;
        }

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

        @media (max-width: 900px) {
            .profile-body {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                align-items: center;
                margin-top: -80px;
            }

            .profile-photo {
                width: 160px;
                height: 160px;
                margin-top: -80px;
            }

            .profile-stats {
                max-width: 400px;
                margin: 0 auto;
            }
        }

        @media (max-width: 600px) {
            header {
                padding: 0.8rem 1rem;
            }

            .logo h1 {
                display: none;
            }

            .main-content {
                padding: 1rem;
            }

            .profile-header {
                padding: 2rem 1.5rem;
            }

            .profile-header h2 {
                font-size: 1.6rem;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .profile-actions {
                justify-content: center;
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
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-header-content">
                    <h2><?= htmlspecialchars($nome) ?></h2>
                    <p>Membro desde <?= date('Y') ?></p>
                </div>
            </div>
            <div class="profile-body">
                <div class="profile-sidebar">
                    <img src="<?= $foto_perfil ?>?v=<?= time() ?>" alt="Foto de Perfil" class="profile-photo" onclick="abrirModalFoto()">
                    <button class="change-photo-btn" onclick="abrirModalFoto()">
                        <i class="fas fa-camera"></i> Alterar Foto
                    </button>
                    <div class="profile-stats">
                        <h3 class="stats-title">Minhas Estatísticas</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-value"><?= $stats['total_conteudos'] ?? 0 ?></div>
                                <div class="stat-label">Conteúdos</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $stats['total_amigos'] ?? 0 ?></div>
                                <div class="stat-label">Amigos</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-content">
                    <div class="profile-section">
                        <h3 class="section-title"><i class="fas fa-user-circle"></i> Informações Pessoais</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Nome Completo</span>
                                <span class="detail-value"><?= htmlspecialchars($nome) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?= htmlspecialchars($email) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Data de Nascimento</span>
                                <span class="detail-value">Não definido</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Telefone</span>
                                <span class="detail-value">Não definido</span>
                            </div>
                        </div>
                    </div>
                    <div class="profile-section">
                        <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Informações Acadêmicas</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Instituição</span>
                                <span class="detail-value"><?= htmlspecialchars($escola) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Curso</span>
                                <span class="detail-value"><?= htmlspecialchars($curso) ?></span>
                            </div>

                        </div>
                    </div>
                    <div class="profile-actions">
                        <a href="editarPerfil.php" class="btn-edit">
                            <i class="fas fa-edit"></i> Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Upload de Foto -->
    <div class="modal-bg" id="modalFoto">
        <div class="modal" style="position:relative;">
            <button class="close-modal" onclick="fecharModalFoto()">&times;</button>
            <h2>Alterar Foto de Perfil</h2>
            <?php if (!empty($upload_erro)): ?>
                <div class="error"><?= htmlspecialchars($upload_erro) ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="nova_foto" accept="image/png, image/jpeg" required>
                <button type="submit" class="btn btn-primary">Guardar Foto</button>
            </form>
            <p style="font-size:0.95rem; color:var(--gray); margin-top:1rem;">Apenas PNG ou JPG até 2MB.</p>
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

        // Modal de foto
        function abrirModalFoto() {
            document.getElementById('modalFoto').classList.add('active');
            document.getElementById('modalFoto').style.display = 'flex';
            document.getElementById('overlay').classList.add('active');
        }

        function fecharModalFoto() {
            document.getElementById('modalFoto').classList.remove('active');
            document.getElementById('modalFoto').style.display = 'none';
            document.getElementById('overlay').classList.remove('active');
        }

        // Fechar todos os modais
        function fecharTodosModais() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('active');
            document.getElementById('mainContent').style.marginLeft = '0';
            fecharModalFoto();
        }
    </script>
</body>

</html>