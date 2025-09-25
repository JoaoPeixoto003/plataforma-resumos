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

// Buscar conteúdos do utilizador
$query = "SELECT c.id_conteudo, c.titulo, c.descricao, c.formato, c.data_upload, d.nome AS disciplina, cu.nome AS curso, e.nome AS escola, 
                 AVG(a.nota) AS avaliacao_media
          FROM conteudos c
          JOIN disciplinas d ON c.id_disciplina = d.id_disciplina
          JOIN cursos cu ON d.id_curso = cu.id_curso
          JOIN escolas e ON cu.id_escola = e.id_escola
          LEFT JOIN avaliacoes a ON c.id_conteudo = a.id_conteudo
          WHERE c.id_utilizador = $id_utilizador
          GROUP BY c.id_conteudo
          ORDER BY c.data_upload DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Conteúdos | Plataforma</title>
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
            --danger: #f72585;
            --warning: #f8961e;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .content-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            min-height: 260px;
            position: relative;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1.5rem 1.5rem 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-bottom: 1px solid var(--light-gray);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-description {
            color: #f8f9fa;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
            color: var(--gray);
            font-size: 0.9rem;
            padding: 0 1.5rem;
            margin-bottom: 1rem;
        }

        .card-meta span {
            background: var(--light-gray);
            border-radius: 6px;
            padding: 0.2rem 0.7rem;
        }

        .card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--light-gray);
            margin-top: auto;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .action-btn:hover {
            color: var(--primary);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1rem;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: var(--warning);
            font-weight: 500;
        }

        .empty-msg {
            text-align: center;
            color: var(--gray);
            font-size: 1.1rem;
            margin-top: 3rem;
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

        .modal input,
        .modal textarea,
        .modal select {
            padding: 0.7rem;
            border-radius: 6px;
            border: 1px solid var(--light-gray);
        }

        .modal .btn {
            width: 100%;
        }

        @media (max-width: 768px) {
            header {
                padding: 0.8rem 1rem;
            }

            .logo h1 {
                display: none;
            }

            .main-content {
                padding: 1rem;
            }

            .content-grid {
                grid-template-columns: 1fr;
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
                <a href="perfil.php" class="nav-link">
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
                <a href="meusConteudos.php" class="nav-link active">
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

    <div class="main-content" id="mainContent">
        <h2 style="margin-bottom:2rem;color:var(--primary);">Meus Conteúdos</h2>
        <div class="content-grid">
            <?php if (mysqli_num_rows($result) == 0): ?>
                <div class="empty-msg">Você ainda não fez upload de nenhum conteúdo.</div>
            <?php else: ?>
                <?php while ($c = mysqli_fetch_assoc($result)): ?>
                    <div class="content-card" id="conteudo-<?= $c['id_conteudo'] ?>">
                        <div class="card-header" onclick="window.location.href='visualizarConteudo.php?id=<?= $c['id_conteudo'] ?>'" style="cursor:pointer;">
                            <h3 class="card-title"><?= htmlspecialchars($c['titulo']) ?></h3>
                            <p class="card-description"><?= htmlspecialchars($c['descricao']) ?></p>
                        </div>
                        <div class="card-meta">
                            <span><i class="fas fa-university"></i> <?= htmlspecialchars($c['escola']) ?></span>
                            <span><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($c['curso']) ?></span>
                            <span><i class="fas fa-book"></i> <?= htmlspecialchars($c['disciplina']) ?></span>
                            <span><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($c['data_upload']) ?></span>
                        </div>
                        <div class="card-actions">
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <span>
                                    <?php
                                    // Corrige o erro deprecated do number_format
                                    echo $c['avaliacao_media'] !== null ? number_format($c['avaliacao_media'], 1) : 'N/A';
                                    ?>
                                </span>
                            </div>
                            <div>
                                <a href="editarConteudo.php?id=<?= $c['id_conteudo'] ?>" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <button class="action-btn btn-danger" onclick="confirmarApagar(<?= $c['id_conteudo'] ?>)" title="Apagar">
                                    <i class="fas fa-trash"></i> Apagar
                                </button>
                                <a href="../../uploads/<?= htmlspecialchars($c['formato']) ?>" download class="action-btn" title="Baixar">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal-bg" id="modalApagar">
        <div class="modal" style="position:relative;">
            <button class="close-modal" onclick="fecharModalApagar()">&times;</button>
            <h2>Apagar Conteúdo</h2>
            <p>Tem certeza que deseja apagar este conteúdo? Esta ação não pode ser desfeita.</p>
            <form method="post" id="formApagar">
                <input type="hidden" name="id_conteudo" id="idConteudoApagar">
                <button type="button" class="btn btn-outline" onclick="fecharModalApagar()">Cancelar</button>
                <button type="submit" class="btn btn-danger">Apagar</button>
            </form>
        </div>
    </div>

    <div class="overlay" id="overlay" onclick="fecharTodosModais()"></div>

    <script>
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

        function confirmarApagar(id) {
            document.getElementById('idConteudoApagar').value = id;
            document.getElementById('modalApagar').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        }

        function fecharModalApagar() {
            document.getElementById('modalApagar').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
        }

        function fecharTodosModais() {
            document.getElementById('sidebar').classList.remove('open');
            fecharModalApagar();
            document.getElementById('mainContent').style.marginLeft = '0';
        }
        document.getElementById('formApagar').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('idConteudoApagar').value;
            fetch('apagarConteudo.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        id_conteudo: id
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('conteudo-' + id).remove();
                        fecharModalApagar();
                    } else {
                        alert('Erro ao apagar conteúdo.');
                    }
                });
        });
    </script>
</body>

</html>