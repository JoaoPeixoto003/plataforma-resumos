<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\visualizarConteudo.php
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


if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$idConteudo = $_GET['id'];

// Buscar informações do conteúdo
$query = "SELECT c.*, u.nome AS nome_utilizador, 
          AVG(a.nota) AS avaliacao_media,
          COUNT(a.id_avaliacao) AS total_avaliacoes,
          d.nome AS nome_disciplina,
          esc.nome AS nome_escola,
          cur.nome AS nome_curso
          FROM conteudos c
          JOIN utilizadores u ON c.id_utilizador = u.id_utilizador
          JOIN disciplinas d ON c.id_disciplina = d.id_disciplina
          JOIN cursos cur ON d.id_curso = cur.id_curso
          JOIN escolas esc ON cur.id_escola = esc.id_escola
          LEFT JOIN avaliacoes a ON c.id_conteudo = a.id_conteudo
          WHERE c.id_conteudo = $idConteudo
          GROUP BY c.id_conteudo";

$result = mysqli_query($conn, $query);
$conteudo = mysqli_fetch_assoc($result);

if (!$conteudo) {
    header("Location: index.php");
    exit();
}

// Verificar se o usuário já avaliou este conteúdo
$avaliacaoUsuario = 0;
if (isset($_SESSION["id_utilizador"])) {
    $idUtilizador = $_SESSION["id_utilizador"];
    $queryAvaliacao = "SELECT nota FROM avaliacoes WHERE id_conteudo = $idConteudo AND id_utilizador = $idUtilizador";
    $resultAvaliacao = mysqli_query($conn, $queryAvaliacao);
    if ($resultAvaliacao && mysqli_num_rows($resultAvaliacao) > 0) {
        $avaliacaoUsuario = mysqli_fetch_assoc($resultAvaliacao)['nota'];
    }
}

// Buscar comentários
$queryComentarios = "SELECT co.*, u.nome 
                     FROM comentarios co
                     JOIN utilizadores u ON co.id_utilizador = u.id_utilizador
                     WHERE co.id_conteudo = $idConteudo
                     ORDER BY co.data_comentario DESC";
$resultComentarios = mysqli_query($conn, $queryComentarios);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($conteudo['titulo']); ?> | Plataforma</title>
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
            padding: 1rem 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-btn {
            background: var(--light);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: var(--light-gray);
        }

        .header-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        .container {
            max-width: 1200px;
            margin: 80px auto 2rem;
            padding: 0 2rem;
        }

        .content-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .content-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
        }

        .content-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .content-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .content-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .content-description {
            margin: 1.5rem 0;
            line-height: 1.7;
            color: var(--dark);
        }

        .content-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .content-preview {
            margin: 2rem 0;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            padding: 1rem;
            min-height: 300px;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .content-preview img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
        }

        .content-preview iframe {
            width: 100%;
            height: 500px;
            border: none;
        }

        .comments-section {
            margin-top: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--dark);
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary);
        }

        .comment {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: var(--border-radius);
            background: var(--light);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .comment-author {
            font-weight: 600;
            color: var(--dark);
        }

        .comment-date {
            color: var(--gray);
            font-size: 0.8rem;
        }

        .comment-text {
            color: var(--dark);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .comment-form {
            margin-top: 2rem;
            background: var(--light);
            padding: 1.5rem;
            border-radius: var(--border-radius);
        }

        .comment-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            resize: none;
            font-family: inherit;
            margin-bottom: 1rem;
            transition: var(--transition);
            min-height: 100px;
        }

        .comment-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        .rating-section {
            margin: 1.5rem 0;
            padding: 1.5rem;
            background: var(--light);
            border-radius: var(--border-radius);
        }

        .rating-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .rating-stars {
            display: flex;
            gap: 0.3rem;
        }

        .rating-star {
            font-size: 1.5rem;
            color: var(--light-gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .rating-star.active,
        .rating-star.hover {
            color: var(--warning);
        }

        .rating-value {
            font-weight: 600;
            color: var(--dark);
        }

        .rating-count {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .rating-message {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .file-icon {
            font-size: 1.2rem;
        }

        /* Modal Favoritar */
        .modal {
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

        .modal .modal-content {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            min-width: 320px;
            max-width: 95vw;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            position: relative;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            position: absolute;
            top: 1rem;
            right: 1rem;
            cursor: pointer;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2999;
            display: none;
        }

        .overlay.active {
            display: block;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
                margin-top: 70px;
            }

            .content-actions {
                flex-direction: column;
            }

            .content-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            header {
                padding: 0.8rem 1rem;
            }

            .header-title {
                display: none;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="header-left">
            <button class="back-btn" onclick="window.location.href='paginaInicioUtilizador.php'">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="header-title">Voltar para conteúdos</div>
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
    <div class="container">
        <div class="content-container">
            <div class="content-header">
                <h1 class="content-title"><?php echo htmlspecialchars($conteudo['titulo']); ?></h1>
                <div class="content-meta">
                    <span class="content-meta-item">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($conteudo['nome_utilizador']); ?>
                    </span>
                    <span class="content-meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('d/m/Y H:i', strtotime($conteudo['data_upload'])); ?>
                    </span>
                    <span class="content-meta-item">
                        <i class="fas fa-book"></i>
                        <?php echo htmlspecialchars($conteudo['nome_disciplina']); ?>
                    </span>
                    <span class="content-meta-item">
                        <i class="fas fa-graduation-cap"></i>
                        <?php echo htmlspecialchars($conteudo['nome_curso']); ?>
                    </span>
                    <span class="content-meta-item">
                        <i class="fas fa-school"></i>
                        <?php echo htmlspecialchars($conteudo['nome_escola']); ?>
                    </span>
                </div>
                <div class="file-info">
                    <i class="fas fa-file-alt file-icon"></i>
                    <span><?php echo strtoupper(htmlspecialchars(pathinfo($conteudo['formato'], PATHINFO_EXTENSION))); ?> •
                        <?php
                        $filePath = "../../uploads/" . htmlspecialchars($conteudo['formato']);
                        if (file_exists($filePath)) {
                            $fileSize = filesize($filePath);
                            if ($fileSize < 1024) {
                                echo $fileSize . ' bytes';
                            } elseif ($fileSize < 1048576) {
                                echo round($fileSize / 1024, 1) . ' KB';
                            } else {
                                echo round($fileSize / 1048576, 1) . ' MB';
                            }
                        } else {
                            echo 'Tamanho desconhecido';
                        }
                        ?>
                    </span>
                </div>
            </div>
            <div class="content-description">
                <p><?php echo htmlspecialchars($conteudo['descricao']); ?></p>
            </div>
            <div class="content-preview">
                <?php
                $filePath = "../../uploads/" . htmlspecialchars($conteudo['formato']);
                $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (file_exists($filePath)) {
                    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo '<img src="' . $filePath . '" alt="Preview do conteúdo">';
                    } elseif ($fileExtension === 'pdf') {
                        echo '<iframe src="' . $filePath . '"></iframe>';
                    } elseif (in_array($fileExtension, ['doc', 'docx'])) {
                        echo '<div class="unsupported-file">';
                        echo '<i class="fas fa-file-word" style="font-size: 3rem; color: #2b579a; margin-bottom: 1rem;"></i>';
                        echo '<p>Documento Word - Faça o download para visualizar</p>';
                        echo '</div>';
                    } else {
                        echo '<div class="unsupported-file">';
                        echo '<i class="fas fa-file" style="font-size: 3rem; color: var(--gray); margin-bottom: 1rem;"></i>';
                        echo '<p>Pré-visualização não disponível - Faça o download para visualizar</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="unsupported-file">';
                    echo '<i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning); margin-bottom: 1rem;"></i>';
                    echo '<p>Arquivo não encontrado</p>';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="content-actions">
                <a href="../../uploads/<?php echo htmlspecialchars($conteudo['formato']); ?>" download class="btn btn-primary">
                    <i class="fas fa-download"></i> Baixar
                </a>
                <button class="btn btn-outline btn-favoritar" data-id="<?php echo $conteudo['id_conteudo']; ?>">
                    <i class="fas fa-heart"></i> Favoritar
                </button>
                <button class="btn btn-outline" onclick="document.getElementById('commentInput').focus()">
                    <i class="fas fa-comment"></i> Comentar
                </button>
            </div>
        </div>
        <div class="rating-section">
            <h3 class="section-title">Avaliações</h3>
            <div class="rating-container">
                <div class="rating-stars" id="ratingStars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star rating-star <?php echo $i <= $avaliacaoUsuario ? 'active' : ''; ?>"
                            data-value="<?php echo $i; ?>"></i>
                    <?php endfor; ?>
                </div>
                <div>
                    <div class="rating-value">
                        <?php
                        echo $conteudo['avaliacao_media'] !== null
                            ? number_format($conteudo['avaliacao_media'], 1)
                            : 'N/A';
                        ?> de 5
                    </div>
                    <div class="rating-count">
                        (<?php echo $conteudo['total_avaliacoes']; ?> avaliações)
                    </div>
                </div>
            </div>
            <div class="rating-message" id="ratingMessage">
                <?php
                if ($avaliacaoUsuario > 0) {
                    echo "Você avaliou este conteúdo com $avaliacaoUsuario estrelas. Pode clicar nas estrelas para alterar sua avaliação.";
                } else {
                    echo "Clique nas estrelas para avaliar este conteúdo.";
                }
                ?>
            </div>
        </div>
        <div class="comments-section">
            <h3 class="section-title">Comentários</h3>
            <div id="commentsList">
                <?php if (mysqli_num_rows($resultComentarios) > 0): ?>
                    <?php while ($comentario = mysqli_fetch_assoc($resultComentarios)): ?>
                        <div class="comment">
                            <div class="comment-header">
                                <span class="comment-author"><?php echo htmlspecialchars($comentario['nome']); ?></span>
                                <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comentario['data_comentario'])); ?></span>
                            </div>
                            <p class="comment-text"><?php echo htmlspecialchars($comentario['texto']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="comment">
                        <p>Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                    </div>
                <?php endif; ?>
            </div>
            <form class="comment-form" id="commentForm" method="POST" action="adicionarComentario.php">
                <input type="hidden" name="id_conteudo" value="<?php echo $conteudo['id_conteudo']; ?>">
                <textarea id="commentInput" class="comment-input" name="comentario" placeholder="Adicione um comentário..." required></textarea>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Enviar Comentário
                </button>
            </form>
        </div>
    </div>
    <!-- Overlay para modal Favoritar -->
    <div class="overlay" id="modalOverlay" onclick="fecharFavoritarModal()"></div>
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
                        // Buscar pastas do utilizador
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
    <script>
        // Avaliação por estrelas (pode alterar sempre)
        const ratingStars = document.querySelectorAll('.rating-star');
        const ratingMessage = document.getElementById('ratingMessage');
        let currentHover = 0;
        let selectedRating = <?php echo $avaliacaoUsuario; ?>;

        ratingStars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = parseInt(star.getAttribute('data-value'));
                rateContent(<?php echo $conteudo['id_conteudo']; ?>, rating);
            });

            star.addEventListener('mouseover', () => {
                const rating = parseInt(star.getAttribute('data-value'));
                currentHover = rating;
                updateStars();

                if (ratingMessage) {
                    const messages = [
                        "Péssimo",
                        "Ruim",
                        "Regular",
                        "Bom",
                        "Excelente"
                    ];
                    ratingMessage.textContent = messages[rating - 1];
                }
            });

            star.addEventListener('mouseout', () => {
                currentHover = 0;
                updateStars();

                if (ratingMessage) {
                    if (selectedRating > 0) {
                        ratingMessage.textContent = "Você avaliou este conteúdo com " + selectedRating + " estrelas. Pode clicar nas estrelas para alterar sua avaliação.";
                    } else {
                        ratingMessage.textContent = "Clique nas estrelas para avaliar este conteúdo.";
                    }
                }
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

        function updateStars() {
            ratingStars.forEach(star => {
                const value = parseInt(star.getAttribute('data-value'));
                star.classList.toggle('active', value <= selectedRating);
                star.classList.toggle('hover', value <= currentHover && currentHover > 0);
            });
        }

        function rateContent(idConteudo, rating) {
            const formData = new FormData();
            formData.append('id_conteudo', idConteudo);
            formData.append('avaliacao', rating);

            fetch('avaliarConteudo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        selectedRating = rating;
                        updateStars();

                        if (ratingMessage) {
                            ratingMessage.textContent = "Você avaliou este conteúdo com " + selectedRating + " estrelas. Pode clicar nas estrelas para alterar sua avaliação.";
                        }

                        // Atualizar a média e contagem
                        if (data.media && data.total) {
                            document.querySelector('.rating-value').textContent = data.media + ' de 5';
                            document.querySelector('.rating-count').textContent = '(' + data.total + ' avaliações)';
                        }
                    } else {
                        alert('Erro ao enviar avaliação: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocorreu um erro ao enviar a avaliação.');
                });
        }

        // Comentários AJAX
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const comentario = form.comentario.value.trim();
            if (!comentario) return;

            const formData = new FormData(form);

            fetch('adicionarComentario.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Adiciona o comentário no topo
                        const commentsList = document.getElementById('commentsList');
                        const newComment = document.createElement('div');
                        newComment.className = 'comment';
                        newComment.innerHTML = `
                        <div class="comment-header">
                            <span class="comment-author"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                            <span class="comment-date">Agora</span>
                        </div>
                        <p class="comment-text">${comentario.replace(/</g, "&lt;")}</p>
                    `;
                        commentsList.insertBefore(newComment, commentsList.firstChild);

                        form.comentario.value = '';
                    } else {
                        alert(data.message || 'Erro ao enviar comentário.');
                    }
                })
                .catch(() => alert('Erro ao enviar comentário.'));
        });
    </script>
</body>

</html>