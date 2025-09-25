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


$userID = $_SESSION["id_utilizador"];
$query = "SELECT a.id_amizade, a.id_utilizador1, u.nome, u.email
          FROM amizades a
          JOIN utilizadores u ON a.id_utilizador1 = u.id_utilizador
          WHERE id_utilizador2 = $userID AND status = 'pendente'";

$resultado = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos de Amizade | Plataforma</title>
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

        /* Header - Consistente com outras páginas */
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
        }

        /* Sidebar - Consistente com outras páginas */
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

        /* Conteúdo principal */
        .main-content {
            margin-top: 80px;
            padding: 2rem;
            width: 100%;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Card de pedidos */
        .requests-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
        }

        .requests-card h1 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .requests-card h1 i {
            color: var(--primary);
        }

        /* Lista de pedidos */
        .requests-list {
            list-style: none;
        }

        .request-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .request-item:last-child {
            border-bottom: none;
        }

        .request-info {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex: 1;
        }

        .request-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .request-details {
            flex: 1;
        }

        .request-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }

        .request-email {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .request-actions {
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-accept {
            background-color: var(--success);
            color: white;
        }

        .btn-accept:hover {
            background-color: #3aa344;
        }

        .btn-decline {
            background-color: var(--danger);
            color: white;
        }

        .btn-decline:hover {
            background-color: #c82333;
        }

        /* Estado vazio */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--light-gray);
        }

        .empty-state p {
            font-size: 1.1rem;
        }

        /* Botão Voltar */
        .btn-back {
            margin-top: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-back:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .request-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
            }

            .request-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>

<body>
    <!-- Header consistente -->
    <header>
        <div class="logo">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1>QuickNote</h1>
        </div>

        <div class="user-actions">
            <a href="logout.php" class="btn btn-primary">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
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
                <a href="listaAmigos.php" class="nav-link active">
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

    <!-- Conteúdo principal -->
    <div class="main-content">
        <div class="requests-card">
            <h1><i class="fas fa-user-plus"></i> Pedidos de Amizade</h1>

            <?php if (mysqli_num_rows($resultado) > 0): ?>
                <ul class="requests-list">
                    <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                        <li class="request-item">
                            <div class="request-info">
                                <div class="request-avatar">
                                    <?= strtoupper(substr(htmlspecialchars($row["nome"]), 0, 1)) ?>
                                </div>
                                <div class="request-details">
                                    <div class="request-name"><?= htmlspecialchars($row["nome"]) ?></div>
                                    <div class="request-email"><?= htmlspecialchars($row["email"]) ?></div>
                                </div>
                            </div>
                            <div class="request-actions">
                                <!-- Formulário para aceitar -->
                                <form method="post" action="processar_pedido.php" style="display:inline;">
                                    <input type="hidden" name="id_amizade" value="<?= $row["id_amizade"] ?>">
                                    <input type="hidden" name="acao" value="aceitar">
                                    <button type="submit" class="action-btn btn-accept">
                                        <i class="fas fa-check"></i> Aceitar
                                    </button>
                                </form>
                                <!-- Formulário para recusar -->
                                <form method="post" action="processar_pedido.php" style="display:inline;">
                                    <input type="hidden" name="id_amizade" value="<?= $row["id_amizade"] ?>">
                                    <input type="hidden" name="acao" value="recusar">
                                    <button type="submit" class="action-btn btn-decline">
                                        <i class="fas fa-times"></i> Recusar
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-envelope-open-text"></i>
                    <p>Não há pedidos de amizade pendentes</p>
                </div>
            <?php endif; ?>

            <a href="listaAmigos.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Voltar para Lista de Amigos
            </a>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const menu = document.getElementById('sidebar');
            menu.classList.toggle('open');
        }
    </script>
</body>

</html>