<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\paginaInicioUtilizador.php
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

// Filtros
$escolas = mysqli_query($conn, "SELECT * FROM escolas WHERE ativa = 1");
$cursos = mysqli_query($conn, "SELECT * FROM cursos");
$disciplinas = mysqli_query($conn, "SELECT * FROM disciplinas");

$filtro_escola = isset($_GET['escola']) ? intval($_GET['escola']) : '';
$filtro_curso = isset($_GET['curso']) ? intval($_GET['curso']) : '';
$filtro_disciplina = isset($_GET['disciplina']) ? intval($_GET['disciplina']) : '';

// Query dinâmica para conteúdos
$queryConteudos = "SELECT c.id_conteudo, c.titulo, c.descricao, c.formato, c.data_upload, u.nome AS nome_utilizador, 
                   AVG(a.nota) AS avaliacao_media, e.nome AS escola, cu.nome AS curso, d.nome AS disciplina
                   FROM conteudos c
                   JOIN utilizadores u ON c.id_utilizador = u.id_utilizador
                   JOIN disciplinas d ON c.id_disciplina = d.id_disciplina
                   JOIN cursos cu ON d.id_curso = cu.id_curso
                   JOIN escolas e ON cu.id_escola = e.id_escola
                   LEFT JOIN avaliacoes a ON c.id_conteudo = a.id_conteudo
                   WHERE 1 ";

if ($filtro_escola) $queryConteudos .= " AND e.id_escola = $filtro_escola ";
if ($filtro_curso) $queryConteudos .= " AND cu.id_curso = $filtro_curso ";
if ($filtro_disciplina) $queryConteudos .= " AND d.id_disciplina = $filtro_disciplina ";

$queryConteudos .= " GROUP BY c.id_conteudo ORDER BY c.data_upload DESC";
$resultConteudos = mysqli_query($conn, $queryConteudos);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma | Página Principal</title>
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
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .fa-spin,
        .fas.fa-spinner.fa-spin {
            animation: spin 1s linear infinite;
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

        .search-container {
            flex: 1;
            max-width: 600px;
            margin: 0 2rem;
            position: relative;
        }

        .search-bar {
            width: 100%;
            padding: 0.7rem 1rem;
            padding-left: 3rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--light);
        }

        .search-bar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
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
            z-index: 2001;
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

        .filters-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
        }

        .filters-bar select {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--light-gray);
            font-size: 1rem;
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
            min-height: 320px;
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
            font-size: 1.3rem;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            color: var(--primary);
        }

        .report-btn {
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .report-btn:hover {
            background: #c82333;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: var(--warning);
            font-weight: 500;
        }

        /* Modais */
        .modal,
        .comments-modal,
        .rating-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.4);
            z-index: 3000;
        }

        .modal.open,
        .comments-modal.open,
        .rating-modal.open {
            display: flex;
        }

        .modal .modal-content,
        .comments-modal>div,
        .rating-modal>div {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            min-width: 320px;
            max-width: 95vw;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            position: relative;
        }

        .close-modal,
        .close-comments {
            background: none;
            border: none;
            font-size: 1.5rem;
            position: absolute;
            top: 1rem;
            right: 1rem;
            cursor: pointer;
        }

        .stars-container {
            display: flex;
            gap: 0.3rem;
            font-size: 2rem;
            margin: 1rem 0;
            justify-content: center;
        }

        .star {
            color: var(--light-gray);
            cursor: pointer;
            transition: color 0.2s;
        }

        .star.active,
        .star.fade-in {
            color: var(--warning);
        }

        .comment-author {
            font-weight: bold;
            color: var(--primary);
        }

        .comment-text {
            margin-bottom: 1rem;
        }

        .comment {
            background: var(--light);
            border-radius: 8px;
            padding: 0.7rem 1rem;
            margin-bottom: 0.7rem;
        }

        .comment-form textarea {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #eee;
            padding: 0.7rem;
            margin-bottom: 1rem;
            resize: vertical;
        }

        .rating-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .upload-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            font-size: 2rem;
            box-shadow: 0 4px 16px rgba(67, 97, 238, 0.18);
            cursor: pointer;
            z-index: 4000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-btn:hover {
            background: var(--primary-dark);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        @media (max-width: 768px) {
            header {
                padding: 0.8rem 1rem;
            }

            .search-container {
                margin: 0 1rem;
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

            .comments-modal,
            .rating-modal {
                max-width: 100%;
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
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-bar" placeholder="Pesquisar conteúdos..." id="search">
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
            <button class="close-btn" onclick="fecharSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="paginaInicioUtilizador.php" class="nav-link active">
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

    <!-- Filtros -->
    <div class="main-content" id="mainContent">
        <form class="filters-bar" method="get" id="filtrosForm">
            <select name="escola" id="filtroEscola" onchange="this.form.submit()">
                <option value="">Todas as Escolas</option>
                <?php while ($esc = mysqli_fetch_assoc($escolas)): ?>
                    <option value="<?= $esc['id_escola'] ?>" <?= $filtro_escola == $esc['id_escola'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($esc['nome']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="curso" id="filtroCurso" onchange="this.form.submit()">
                <option value="">Todos os Cursos</option>
                <?php mysqli_data_seek($cursos, 0);
                while ($cur = mysqli_fetch_assoc($cursos)): ?>
                    <?php if (!$filtro_escola || $cur['id_escola'] == $filtro_escola): ?>
                        <option value="<?= $cur['id_curso'] ?>" <?= $filtro_curso == $cur['id_curso'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cur['nome']) ?>
                        </option>
                    <?php endif; ?>
                <?php endwhile; ?>
            </select>
            <select name="disciplina" id="filtroDisciplina" onchange="this.form.submit()">
                <option value="">Todas as Disciplinas</option>
                <?php mysqli_data_seek($disciplinas, 0);
                while ($disc = mysqli_fetch_assoc($disciplinas)): ?>
                    <?php
                    $curso_ok = !$filtro_curso || $disc['id_curso'] == $filtro_curso;
                    $escola_ok = !$filtro_escola || in_array($disc['id_curso'], array_column(mysqli_fetch_all(mysqli_query($conn, "SELECT id_curso FROM cursos WHERE id_escola = " . intval($filtro_escola)), MYSQLI_ASSOC), 'id_curso'));
                    ?>
                    <?php if ($curso_ok && $escola_ok): ?>
                        <option value="<?= $disc['id_disciplina'] ?>" <?= $filtro_disciplina == $disc['id_disciplina'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($disc['nome']) ?>
                        </option>
                    <?php endif; ?>
                <?php endwhile; ?>
            </select>
        </form>

        <div class="content-grid">
            <?php while ($conteudo = mysqli_fetch_assoc($resultConteudos)) { ?>
                <div class="content-card fade-in" id="conteudo-<?php echo $conteudo['id_conteudo']; ?>">
                    <div class="card-header" onclick="window.location.href='visualizarConteudo.php?id=<?php echo $conteudo['id_conteudo']; ?>'" style="cursor: pointer;">
                        <h3 class="card-title"><?php echo htmlspecialchars($conteudo['titulo']); ?></h3>
                        <p class="card-description"><?php echo htmlspecialchars($conteudo['descricao']); ?></p>
                    </div>
                    <div class="card-meta">
                        <span><i class="fas fa-university"></i> <?= htmlspecialchars($conteudo['escola']) ?></span>
                        <span><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($conteudo['curso']) ?></span>
                        <span><i class="fas fa-book"></i> <?= htmlspecialchars($conteudo['disciplina']) ?></span>
                        <span><i class="fas fa-user"></i> <?= htmlspecialchars($conteudo['nome_utilizador']); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($conteudo['data_upload']); ?></span>
                    </div>
                    <div class="card-actions">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <span id="media-<?php echo $conteudo['id_conteudo']; ?>">
                                <?php
                                echo $conteudo['avaliacao_media'] !== null ? number_format($conteudo['avaliacao_media'], 1) : 'N/A';
                                ?>
                            </span>
                        </div>
                        <div class="action-buttons">
                            <button class="action-btn" onclick="abrirComentarios(<?php echo $conteudo['id_conteudo']; ?>)">
                                <i class="fas fa-comment"></i>
                                <span class="desktop-only">Comentar</span>
                            </button>
                            <button class="action-btn" onclick="abrirAvaliacao(<?php echo $conteudo['id_conteudo']; ?>)">
                                <i class="fas fa-star"></i>
                                <span class="desktop-only">Avaliar</span>
                            </button>
                            <a href="../../uploads/<?php echo htmlspecialchars($conteudo['formato']); ?>" download class="action-btn">
                                <i class="fas fa-download"></i>
                                <span class="desktop-only">Baixar</span>
                            </a>
                            <button class="action-btn btn-favoritar" data-id="<?php echo $conteudo['id_conteudo']; ?>">
                                <i class="fas fa-heart"></i>
                                <span class="desktop-only">Favoritar</span>
                            </button>
                            <button class="report-btn" title="Reportar conteúdo" onclick="abrirReport(<?php echo $conteudo['id_conteudo']; ?>, event)">
                                <i class="fas fa-flag"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Modal de Comentários -->
    <div class="comments-modal" id="commentsModal">
        <div class="comments-header">
            <h4>Comentários</h4>
            <button class="close-comments" onclick="fecharComentarios()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="comments-list" id="commentsList"></div>
        <form class="comment-form" id="commentForm">
            <input type="hidden" name="id_conteudo" id="idConteudo">
            <textarea class="comment-input" name="comentario" placeholder="Adicione um comentário..." required></textarea>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-paper-plane"></i> Enviar Comentário
            </button>
        </form>
    </div>

    <!-- Modal de Avaliação -->
    <div class="rating-modal" id="ratingModal">
        <div class="rating-header">
            <h4>Avaliar Conteúdo</h4>
            <p>Selecione sua avaliação</p>
        </div>
        <form id="ratingForm">
            <input type="hidden" name="id_conteudo" id="idConteudoAvaliacao">
            <div class="stars-container" id="starsContainer">
                <i class="fas fa-star star" data-value="1"></i>
                <i class="fas fa-star star" data-value="2"></i>
                <i class="fas fa-star star" data-value="3"></i>
                <i class="fas fa-star star" data-value="4"></i>
                <i class="fas fa-star star" data-value="5"></i>
            </div>
            <input type="hidden" name="avaliacao" id="ratingValue" required>
            <div class="rating-actions">
                <button type="button" class="btn btn-outline" onclick="fecharAvaliacao()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
            </div>
        </form>
    </div>

    <!-- Modal de Report -->
    <div class="rating-modal" id="reportModal" style="display:none;">
        <div class="rating-header">
            <h4>Reportar Conteúdo</h4>
            <p>Descreva o motivo do report</p>
        </div>
        <form id="reportForm">
            <input type="hidden" name="id_conteudo" id="idConteudoReport">
            <textarea name="descricao" required placeholder="Descreva o motivo..." style="width:100%;height:80px;border-radius:8px;border:1px solid #eee;padding:0.7rem;margin-bottom:1rem;"></textarea>
            <div class="rating-actions">
                <button type="button" class="btn btn-outline" onclick="fecharReport()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar Report</button>
            </div>
        </form>
    </div>

    <!-- Botão de Upload -->
    <button class="upload-btn" onclick="window.location.href='uploadConteudo.php'">
        <i class="fas fa-plus"></i>
    </button>

    <!-- Modal Favoritar -->
    <div class="modal" id="favoritarModal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Guardar nos Favoritos</h3>
                <button class="close-modal" onclick="fecharFavoritarModal()">&times;</button>
            </div>
            <form id="favoritarForm">
                <input type="hidden" name="id_conteudo" id="favIdConteudo">
                <div class="form-group">
                    <label for="favPasta">Escolha a pasta</label>
                    <select name="id_pasta" id="favPasta" class="form-control">
                        <option value="0">Sem Pasta</option>
                        <?php
                        $pastasFavoritos = mysqli_query($conn, "SELECT * FROM pastas_favoritos WHERE id_utilizador = {$_SESSION['id_utilizador']}");
                        while ($pasta = mysqli_fetch_assoc($pastasFavoritos)) {
                            echo '<option value="' . $pasta['id_pasta'] . '">' . htmlspecialchars($pasta['nome']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="fecharFavoritarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overlay para Sidebar -->
    <div class="overlay" id="sidebarOverlay" onclick="fecharSidebar()"></div>
    <!-- Overlay para Modais -->
    <div class="overlay" id="modalOverlay" onclick="fecharTodosModais()"></div>

    <script>
        let currentRating = 0;

        // Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active');
            if (sidebar.classList.contains('open')) {
                mainContent.style.marginLeft = '300px';
            } else {
                mainContent.style.marginLeft = '0';
            }
        }

        function fecharSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('active');
            document.getElementById('mainContent').style.marginLeft = '0';
        }

        // Filtro de Conteúdos
        document.getElementById('search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.content-card');
            cards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const description = card.querySelector('.card-description').textContent.toLowerCase();
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Comentários
        function abrirComentarios(idConteudo) {
            const modal = document.getElementById('commentsModal');
            const overlay = document.getElementById('modalOverlay');
            const commentsList = document.getElementById('commentsList');
            const idConteudoInput = document.getElementById('idConteudo');
            commentsList.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Carregando comentários...</div>';
            idConteudoInput.value = idConteudo;
            fetch(`buscarComentarios.php?id_conteudo=${idConteudo}`)
                .then(response => response.json())
                .then(data => {
                    commentsList.innerHTML = '';
                    if (data.length === 0) {
                        commentsList.innerHTML = '<p>Nenhum comentário ainda. Seja o primeiro a comentar!</p>';
                        return;
                    }
                    data.forEach(comentario => {
                        const commentDiv = document.createElement('div');
                        commentDiv.className = 'comment fade-in';
                        commentDiv.innerHTML = `
                            <p class="comment-author">${comentario.nome}</p>
                            <p class="comment-text">${comentario.texto}</p>
                        `;
                        commentsList.appendChild(commentDiv);
                    });
                })
                .catch(error => {
                    commentsList.innerHTML = '<p>Ocorreu um erro ao carregar os comentários.</p>';
                    console.error('Error:', error);
                });
            modal.classList.add('open');
            overlay.classList.add('active');
        }

        function fecharComentarios() {
            document.getElementById('commentsModal').classList.remove('open');
            document.getElementById('modalOverlay').classList.remove('active');
        }
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            submitButton.disabled = true;
            fetch('adicionarComentario.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const commentsList = document.getElementById('commentsList');
                        const commentDiv = document.createElement('div');
                        commentDiv.className = 'comment fade-in';
                        commentDiv.innerHTML = `
                            <p class="comment-author">${data.nome || 'Você'}</p>
                            <p class="comment-text">${formData.get('comentario')}</p>
                        `;
                        commentsList.insertBefore(commentDiv, commentsList.firstChild);
                        this.reset();
                    } else {
                        alert('Erro ao enviar comentário: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocorreu um erro ao enviar o comentário.');
                })
                .finally(() => {
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
        });

        // Abrir modal ao clicar no botão Favoritar
        document.querySelectorAll('.btn-favoritar').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                document.getElementById('favIdConteudo').value = this.getAttribute('data-id');
                document.getElementById('favoritarModal').style.display = 'flex';
                document.getElementById('modalOverlay').classList.add('active');
            });
        });

        function fecharFavoritarModal() {
            document.getElementById('favoritarModal').style.display = 'none';
            document.getElementById('modalOverlay').classList.remove('active');
        }

        // Submeter favorito via AJAX
        document.getElementById('favoritarForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('adicionarFavorito.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        fecharFavoritarModal();
                        alert('Conteúdo guardado nos favoritos!');
                    } else {
                        alert(data.message || 'Erro ao guardar favorito.');
                    }
                })
                .catch(() => alert('Erro ao guardar favorito.'));
        });

        // Avaliação
        function abrirAvaliacao(idConteudo) {
            document.getElementById('idConteudoAvaliacao').value = idConteudo;
            document.getElementById('ratingModal').classList.add('open');
            document.getElementById('modalOverlay').classList.add('active');
            currentRating = 0;
            updateStars();
        }

        function fecharAvaliacao() {
            document.getElementById('ratingModal').classList.remove('open');
            document.getElementById('modalOverlay').classList.remove('active');
        }
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                currentRating = parseInt(this.getAttribute('data-value'));
                document.getElementById('ratingValue').value = currentRating;
                updateStars();
            });
            star.addEventListener('mouseover', function() {
                const hoverRating = parseInt(this.getAttribute('data-value'));
                highlightStars(hoverRating);
            });
            star.addEventListener('mouseout', function() {
                updateStars();
            });
        });

        function highlightStars(rating) {
            document.querySelectorAll('.star').forEach(star => {
                if (parseInt(star.getAttribute('data-value')) <= rating) {
                    star.classList.add('active', 'fade-in');
                } else {
                    star.classList.remove('active', 'fade-in');
                }
            });
        }

        function updateStars() {
            document.querySelectorAll('.star').forEach(star => {
                if (parseInt(star.getAttribute('data-value')) <= currentRating) {
                    star.classList.add('active', 'fade-in');
                } else {
                    star.classList.remove('active', 'fade-in');
                }
            });
        }
        document.getElementById('ratingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (currentRating === 0) {
                alert('Por favor, selecione uma avaliação.');
                return;
            }
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            submitButton.disabled = true;
            fetch('avaliarConteudo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const mediaSpan = document.getElementById(`media-${formData.get('id_conteudo')}`);
                        if (mediaSpan) {
                            mediaSpan.innerHTML = data.media;
                        }
                        fecharAvaliacao();
                    } else {
                        alert('Erro ao enviar avaliação: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocorreu um erro ao enviar a avaliação.');
                })
                .finally(() => {
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
        });

        // Report
        function abrirReport(idConteudo, e) {
            e.stopPropagation();
            document.getElementById('idConteudoReport').value = idConteudo;
            document.getElementById('reportModal').style.display = 'block';
            document.getElementById('modalOverlay').classList.add('active');
        }

        function fecharReport() {
            document.getElementById('reportModal').style.display = 'none';
            document.getElementById('modalOverlay').classList.remove('active');
        }

        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('reportarConteudo.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    alert('Report enviado com sucesso!');
                    fecharReport();
                } else {
                    alert('Erro ao enviar report: ' + (data.message || ''));
                }
            });
        });

        function fecharTodosModais() {
            document.getElementById('commentsModal').classList.remove('open');
            document.getElementById('ratingModal').classList.remove('open');
            fecharReport();
            fecharFavoritarModal();
            document.getElementById('modalOverlay').classList.remove('active');
        }
    </script>
</body>

</html>