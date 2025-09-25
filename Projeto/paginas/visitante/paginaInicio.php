<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";


// Verificar se o usuário já está logado
if (isset($_SESSION["id_utilizador"]) && $_SESSION["id_utilizador"] > 0) {
    header("Location: ../utilizador/paginaInicioUtilizador.php");
    exit();
}

$queryConteudos = "SELECT c.id_conteudo, c.titulo, c.descricao, c.formato, c.data_upload, u.nome AS nome_utilizador, 
                   AVG(a.nota) AS avaliacao_media
                   FROM conteudos c
                   JOIN utilizadores u ON c.id_utilizador = u.id_utilizador
                   LEFT JOIN avaliacoes a ON c.id_conteudo = a.id_conteudo
                   GROUP BY c.id_conteudo
                   ORDER BY c.data_upload DESC LIMIT 6";
$resultConteudos = mysqli_query($conn, $queryConteudos);

// Verificar erros na consulta
if (!$resultConteudos) {
    die("Erro na consulta: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> QuickNote</title>
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

        /* Header */
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

        /* Conteúdo Principal */
        .main-content {
            margin-top: 80px;
            padding: 2rem;
        }

        .hero-section {
            text-align: center;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .hero-description {
            font-size: 1.2rem;
            color: var(--gray);
            max-width: 800px;
            margin: 0 auto 2rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .content-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1.5rem 1.5rem 0;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .card-description {
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            color: var(--gray);
            font-size: 0.85rem;
            padding: 0 1.5rem;
            margin-bottom: 1rem;
        }

        .card-actions {
            display: flex;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--light-gray);
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

        .rating {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: var(--warning);
            font-weight: 500;
        }

        /* Sobre Nós */
        .about-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin: 3rem 0;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
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

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .about-text {
            font-size: 1.1rem;
            line-height: 1.7;
            color: var(--dark);
        }

        .about-image {
            background: var(--light-gray);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .team-members {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .team-member {
            text-align: center;
        }

        .member-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--light-gray);
            margin: 0 auto 1rem;
            overflow: hidden;
        }

        .member-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .member-role {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Call to Action */
        .cta-section {
            text-align: center;
            padding: 3rem 0;
            background: var(--primary);
            color: white;
            border-radius: var(--border-radius);
            margin: 2rem 0;
        }

        .cta-title {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .cta-description {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 2rem;
            text-align: center;
            margin-top: 3rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .footer-link {
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-link:hover {
            color: var(--accent);
        }

        .copyright {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            header {
                padding: 0.8rem 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-description {
                font-size: 1rem;
            }

            .about-content {
                grid-template-columns: 1fr;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <h1>QuickNote</h1>
        </div>

        <div class="user-actions">
            <a href="login.php" class="btn btn-outline">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="registo.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Registrar
            </a>
        </div>
    </header>

    <div class="main-content">
        <!-- Seção Hero -->
        <section class="hero-section">
            <h1 class="hero-title">Acesse Recursos Acadêmicos</h1>
            <p class="hero-description">
                A QuickNote conecta estudantes e educadores para o compartilhamento de materiais de estudo,
                trabalhos e recursos educacionais de qualidade.
            </p>
            <a href="registo.php" class="btn btn-primary" style="padding: 0.8rem 2rem;">
                <i class="fas fa-user-plus"></i> Criar Conta Gratuita
            </a>
        </section>

        <!-- Conteúdos Recentes -->
        <h2 class="section-title">Conteúdos Recentes</h2>
        <div class="content-grid">
            <?php while ($conteudo = mysqli_fetch_assoc($resultConteudos)) { ?>
                <div class="content-card" id="conteudo-<?php echo $conteudo['id_conteudo']; ?>">
                    <div class="card-header" onclick="window.location.href='visualizarConteudoVisitante.php?id=<?php echo $conteudo['id_conteudo']; ?>'" style="cursor: pointer;">
                        <h3 class="card-title"><?php echo htmlspecialchars($conteudo['titulo']); ?></h3>
                        <p class="card-description"><?php echo htmlspecialchars($conteudo['descricao']); ?></p>
                    </div>

                    <div class="card-meta">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($conteudo['nome_utilizador']); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($conteudo['data_upload']); ?></span>
                    </div>

                    <div class="card-actions">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <?php
                            $media = $conteudo['avaliacao_media'];
                            echo is_null($media) ? 'N/A' : number_format((float)$media, 1);
                            ?>
                        </div>


                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Sobre Nós -->
        <section class="about-section">
            <h2 class="section-title">Sobre Nós</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>
                        A QuickNote foi criada em 2025 com o objetivo de facilitar o compartilhamento
                        de recursos educacionais entre estudantes e professores de instituições de ensino superior.
                    </p>
                    <p>
                        A nossa missão é democratizar o acesso a materiais de qualidade e promover a colaboração
                        entre membros da comunidade acadêmica.
                    </p>
                </div>
                <div class="about-image">
                    <i class="fas fa-graduation-cap" style="font-size: 5rem; color: var(--primary);"></i>
                </div>
            </div>

            <h3 style="margin-top: 2rem; color: var(--primary);">Nossa Equipa</h3>
            <div class="team-members">
                <div class="team-member">
                    <div class="member-image">
                        <i class="fas fa-user" style="font-size: 3rem; line-height: 120px; color: var(--gray);"></i>
                    </div>
                    <h4 class="member-name">João Peixoto</h4>
                    <p class="member-role">Fundador & Desenvolvedor</p>
                </div>

            </div>
        </section>

        <!-- Call to Action -->
        <section class="cta-section">
            <h2 class="cta-title">Pronto para fazer parte da nossa comunidade?</h2>
            <p class="cta-description">
                Registre-se agora para começar a compartilhar e acessar recursos acadêmicos de qualidade.
            </p>
            <a href="registo.php" class="btn btn-outline" style="background: white; color: var(--primary); padding: 0.8rem 2rem;">
                <i class="fas fa-user-plus"></i> Criar Conta
            </a>
        </section>
    </div>

    <footer>
        <div class="footer-links">
            <a href="#" class="footer-link">Termos de Serviço</a>
            <a href="#" class="footer-link">Política de Privacidade</a>
            <a href="#" class="footer-link">Contato</a>
            <a href="#" class="footer-link">Ajuda</a>
        </div>
        <p class="copyright">© 2023 Plataforma Acadêmica. Todos os direitos reservados.</p>
    </footer>
</body>

</html>