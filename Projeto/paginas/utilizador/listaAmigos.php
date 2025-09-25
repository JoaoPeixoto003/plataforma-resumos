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

// Consultar a lista de amigos
$idUtilizadorLogado = $_SESSION['id_utilizador'];
$query = "
    SELECT 
        CASE 
            WHEN id_utilizador1 = $idUtilizadorLogado THEN id_utilizador2 
            ELSE id_utilizador1 
        END AS id_amigo
    FROM amizades
    WHERE 
        (id_utilizador1 = $idUtilizadorLogado OR id_utilizador2 = $idUtilizadorLogado)
        AND status = 'aceite'
";

$result = mysqli_query($conn, $query);

// Verificar se há resultados
if (mysqli_num_rows($result) > 0) {
    $amigos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $amigos[] = $row['id_amigo'];
    }

    // Buscar detalhes dos amigos (nome, email, etc.)
    $idsAmigos = implode(", ", $amigos);
    $queryDetalhes = "SELECT id_utilizador, nome, email FROM utilizadores WHERE id_utilizador IN ($idsAmigos)";
    $resultDetalhes = mysqli_query($conn, $queryDetalhes);

    $amigosDetalhes = [];
    if (mysqli_num_rows($resultDetalhes) > 0) {
        while ($amigo = mysqli_fetch_assoc($resultDetalhes)) {
            $amigosDetalhes[] = $amigo;
        }
    }
} else {
    $amigosDetalhes = []; // Nenhum amigo encontrado
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Amigos | Plataforma</title>
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

        /* Container principal */
        .friends-main-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .friends-main-container {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .friends-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
        }

        .friends-card h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .friends-card h2 i {
            color: var(--primary);
        }

        /* Lista de amigos */
        .friends-list {
            list-style: none;
        }

        .friend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .friend-item:last-child {
            border-bottom: none;
        }

        .friend-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .friend-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .friend-name {
            font-weight: 500;
        }

        .friend-email {
            font-size: 0.85rem;
            color: var(--gray);
        }

        .friend-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-view {
            background-color: var(--primary);
            color: white;
        }

        .btn-view:hover {
            background-color: var(--primary-dark);
        }

        .btn-remove {
            background-color: var(--danger);
            color: white;
        }

        .btn-remove:hover {
            background-color: #c82333;
        }

        /* Formulário de adicionar amigo */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        .btn-add {
            background-color: var(--success);
            color: white;
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
        }

        .btn-add:hover {
            background-color: #3aa344;
        }

        /* Estado vazio */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--light-gray);
        }

        /* Botão de pedidos */
        .requests-btn-container {
            text-align: center;
            margin-top: 2rem;
        }

        .btn-requests {
            background-color: var(--warning);
            color: white;
            padding: 0.8rem 2rem;
            font-size: 1rem;
        }

        .btn-requests:hover {
            background-color: #e0861a;
        }

        /* Notificação */
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--success);
            color: white;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 1rem;
            max-width: 90%;
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
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
        <div class="friends-main-container">
            <!-- Lista de Amigos -->
            <div class="friends-card">
                <h2><i class="fas fa-user-friends"></i> Lista de Amigos</h2>

                <?php if (count($amigosDetalhes) > 0): ?>
                    <ul class="friends-list">
                        <?php foreach ($amigosDetalhes as $amigo): ?>
                            <li class="friend-item">
                                <div class="friend-info">
                                    <div class="friend-avatar">
                                        <?= strtoupper(substr(htmlspecialchars($amigo['nome']), 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="friend-name"><?= htmlspecialchars($amigo['nome']) ?></div>
                                        <div class="friend-email"><?= htmlspecialchars($amigo['email']) ?></div>
                                    </div>
                                </div>
                                <div class="friend-actions">
                                    <form method="POST" action="remover_amigo.php" style="display:inline;">
                                        <input type="hidden" name="id_amigo" value="<?= $amigo['id_utilizador'] ?>">
                                        <button type="submit" class="action-btn btn-remove" title="Remover amigo">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                    <a href="mensagens.php?chat_id=<?= $amigo['id_utilizador'] ?>" class="action-btn btn-view" title="Enviar mensagem">
                                        <i class="fas fa-comment"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-friends"></i>
                        <p>Você não tem amigos adicionados ainda</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Adicionar Amigos -->
            <div class="friends-card">
                <h2><i class="fas fa-user-plus"></i> Adicionar Amigo</h2>
                <form action="adicionarAmigo.php" method="post">
                    <div class="form-group">
                        <label for="friend-name">Nome ou Email</label>
                        <input type="text" id="friend-name" name="friend-name" class="form-control" required placeholder="Digite o nome ou email do amigo">
                    </div>
                    <button type="submit" class="action-btn btn-add">
                        <i class="fas fa-plus"></i> Adicionar Amigo
                    </button>
                </form>
            </div>
        </div>

        <!-- Botão de pedidos de amizade -->
        <div class="requests-btn-container">
            <a href="pedidosAmizade.php" class="action-btn btn-requests">
                <i class="fas fa-envelope"></i> Ver Pedidos de Amizade
            </a>
        </div>
    </div>

    <!-- Notificação -->
    <?php if (isset($_SESSION['message'])): ?>
        <div id="notification" class="notification">
            <p><?= $_SESSION['message'] ?></p>
            <button class="notification-close" onclick="closeNotification()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <script>
        function toggleSidebar() {
            const menu = document.getElementById('sidebar');
            menu.classList.toggle('open');
        }

        function closeNotification() {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.style.display = 'none';
            }
        }

        // Fechar notificação automaticamente após 5 segundos
        setTimeout(() => {
            closeNotification();
        }, 5000);
    </script>
</body>

</html>

<?php
mysqli_close($conn);
?>