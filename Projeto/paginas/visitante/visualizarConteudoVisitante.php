<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\visitante\visualizarConteudoVisitante.php
include "../../baseDados/basedados.php";

if (!isset($_GET['id'])) {
    header("Location: paginaInicio.php");
    exit();
}

$idConteudo = intval($_GET['id']);

// Buscar informações do conteúdo
$query = "SELECT c.*, u.nome AS nome_utilizador, 
          AVG(a.nota) AS avaliacao_media,
          COUNT(a.id_avaliacao) AS total_avaliacoes,
          d.nome AS nome_disciplina
          FROM conteudos c
          JOIN utilizadores u ON c.id_utilizador = u.id_utilizador
          JOIN disciplinas d ON c.id_disciplina = d.id_disciplina
          LEFT JOIN avaliacoes a ON c.id_conteudo = a.id_conteudo
          WHERE c.id_conteudo = $idConteudo
          GROUP BY c.id_conteudo";
$result = mysqli_query($conn, $query);
$conteudo = mysqli_fetch_assoc($result);

if (!$conteudo) {
    header("Location: paginaInicio.php");
    exit();
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
    <title><?php echo htmlspecialchars($conteudo['titulo']); ?> | QuickNote</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --warning: #f8961e;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        }
        body { font-family: 'Inter', sans-serif; background: #f5f7fb; color: var(--dark); margin: 0; }
        header { background: #fff; box-shadow: var(--box-shadow); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .header-left { display: flex; align-items: center; gap: 1rem; }
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
            transition: 0.2s;
        }
        .back-btn:hover { background: var(--light-gray); }
        .container { max-width: 900px; margin: 60px auto 2rem; padding: 0 2rem; }
        .content-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .content-title { font-size: 1.8rem; color: var(--dark); margin-bottom: 0.5rem; }
        .content-meta { color: var(--gray); font-size: 0.95rem; margin-bottom: 1rem; }
        .content-description { margin: 1.5rem 0; color: var(--dark);}
        .content-preview {
            margin: 2rem 0;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            padding: 1rem;
            min-height: 200px;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .content-preview img { max-width: 100%; max-height: 400px; object-fit: contain; }
        .content-preview iframe { width: 100%; height: 400px; border: none; }
        .rating-section { margin: 1.5rem 0; padding: 1.5rem; background: var(--light); border-radius: var(--border-radius);}
        .rating-stars { color: var(--warning); font-size: 1.3rem; }
        .rating-value { font-weight: 600; color: var(--dark);}
        .rating-count { color: var(--gray); font-size: 0.9rem;}
        .comments-section { margin-top: 2rem; }
        .section-title { font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--dark);}
        .comment { margin-bottom: 1.5rem; padding: 1rem; border-radius: var(--border-radius); background: var(--light);}
        .comment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;}
        .comment-author { font-weight: 600; color: var(--dark);}
        .comment-date { color: var(--gray); font-size: 0.8rem;}
        .comment-text { color: var(--dark); font-size: 0.95rem; line-height: 1.6;}
        @media (max-width: 768px) {
            header { padding: 0.8rem 1rem;}
            .container { padding: 0 1rem; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">
            <button class="back-btn" onclick="window.location.href='paginaInicio.php'">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div style="font-size:1.1rem; font-weight:600; color:var(--primary); margin-left:10px;">Voltar</div>
        </div>
        <div>
            <a href="login.php" class="btn btn-outline" style="margin-right: 1rem;">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="registo.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Registrar
            </a>
        </div>
    </header>
    <div class="container">
        <div class="content-container">
            <h1 class="content-title"><?php echo htmlspecialchars($conteudo['titulo']); ?></h1>
            <div class="content-meta">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($conteudo['nome_utilizador']); ?></span> |
                <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($conteudo['data_upload'])); ?></span> |
                <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($conteudo['nome_disciplina']); ?></span>
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
                    } else {
                        echo '<div style="color:var(--gray);text-align:center;">Pré-visualização não disponível</div>';
                    }
                } else {
                    echo '<div style="color:var(--danger);text-align:center;">Arquivo não encontrado</div>';
                }
                ?>
            </div>
            <div class="rating-section">
                <div class="rating-stars">
                    <?php
                    $media = $conteudo['avaliacao_media'] ? number_format($conteudo['avaliacao_media'], 1) : 'N/A';
                    $total = $conteudo['total_avaliacoes'] ?? 0;
                    for ($i = 1; $i <= 5; $i++) {
                        echo '<i class="fas fa-star'.($conteudo['avaliacao_media'] >= $i ? '':'-o').'"></i>';
                    }
                    ?>
                </div>
                <div class="rating-value">
                    <?php echo $media; ?> de 5
                </div>
                <div class="rating-count">
                    (<?php echo $total; ?> avaliações)
                </div>
                <div style="color:var(--gray);font-size:0.95rem;margin-top:0.5rem;">
                    <i class="fas fa-info-circle"></i> Só utilizadores registados podem avaliar.
                </div>
            </div>
        </div>
        <div class="comments-section">
            <h3 class="section-title">Comentários</h3>
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
                    <p>Nenhum comentário ainda.</p>
                </div>
            <?php endif; ?>
            <div style="color:var(--gray);font-size:0.95rem;margin-top:1rem;">
                <i class="fas fa-info-circle"></i> Só utilizadores registados podem comentar.
            </div>
        </div>
    </div>
</body>
</html>