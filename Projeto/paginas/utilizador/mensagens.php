<?php
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
$id_utilizador = $_SESSION['id_utilizador'];

// Buscar lista de amigos
$queryAmigos = "SELECT u.id_utilizador, u.nome 
                FROM amizades a
                JOIN utilizadores u ON (a.id_utilizador1 = u.id_utilizador OR a.id_utilizador2 = u.id_utilizador)
                WHERE (a.id_utilizador1 = '$id_utilizador' OR a.id_utilizador2 = '$id_utilizador') 
                  AND u.id_utilizador != '$id_utilizador'
                  AND a.status = 'aceite'";
$resultAmigos = mysqli_query($conn, $queryAmigos);

// Buscar lista de grupos
$queryGrupos = "SELECT g.id_grupo, g.nome_grupo, g.id_criador, g.data_criacao,
                (SELECT COUNT(*) FROM membros_grupo WHERE id_grupo = g.id_grupo) as total_membros
                FROM membros_grupo mg
                JOIN grupos g ON mg.id_grupo = g.id_grupo
                WHERE mg.id_utilizador = '$id_utilizador'";
$resultGrupos = mysqli_query($conn, $queryGrupos);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | QuickNote</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #5865F2;
            --primary-dark: #4752C4;
            --primary-light: #EBEEFE;
            --secondary: #3F37C9;
            --accent: #4895EF;
            --light: #FFFFFF;
            --dark: #2C2F33;
            --gray: #99A2B0;
            --light-gray: #F2F3F5;
            --success: #3BA55C;
            --danger: #ED4245;
            --warning: #FAA61A;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            --transition: all 0.2s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.6;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        header {
            background: var(--light);
            box-shadow: var(--box-shadow);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
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
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        /* Main Container */
        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--light);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            transform: translateX(-100%);
            position: fixed;
            height: calc(100vh - 60px);
            z-index: 90;
            top: 60px;
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .sidebar-header h3 {
            color: var(--dark);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--gray);
            cursor: pointer;
        }

        .nav-menu {
            list-style: none;
            flex: 1;
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            color: var(--dark);
            text-decoration: none;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Chat Container */
        .chat-container {
            display: flex;
            flex: 1;
            height: calc(100vh - 60px);
            background: var(--light);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin: 0 auto;
            max-width: 1400px;
            width: 100%;
        }

        /* Contacts Sidebar */
        .contacts-sidebar {
            width: 350px;
            border-right: 1px solid var(--light-gray);
            display: flex;
            flex-direction: column;
            background: var(--light);
        }

        .search-container {
            padding: 1rem;
            position: relative;
            background: var(--light);
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: var(--light-gray);
            color: var(--dark);
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .search-icon {
            position: absolute;
            left: 1.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .contacts-header {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--light);
        }

        .contacts-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .btn-icon {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
        }

        .btn-icon:hover {
            color: var(--primary);
        }

        .contacts-list {
            list-style: none;
            overflow-y: auto;
            flex: 1;
            margin: 0;
            padding: 0;
        }

        .contact-item {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .contact-item:hover {
            background: var(--light-gray);
        }

        .contact-item.active {
            background: var(--primary-light);
        }

        .contact-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .contact-info {
            flex: 1;
            min-width: 0;
        }

        .contact-info h5 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .contact-info p {
            font-size: 0.8rem;
            color: var(--gray);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--light-gray);
        }

        .chat-header {
            padding: 1rem 1.5rem;
            background: var(--light);
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .chat-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }

        .group-actions {
            display: flex;
            gap: 0.5rem;
        }

        .group-action-btn {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .group-action-btn:hover {
            background: var(--light-gray);
            color: var(--primary);
        }

        .group-action-btn.delete {
            color: var(--danger);
        }

        .group-action-btn.delete:hover {
            background: rgba(237, 66, 69, 0.1);
        }

        .chat-messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            background: var(--light-gray);
            display: flex;
            flex-direction: column;
        }

        .message {
            margin-bottom: 1rem;
            max-width: 70%;
            display: flex;
            flex-direction: column;
        }

        .message.sent {
            align-self: flex-end;
        }

        .message.received {
            align-self: flex-start;
        }

        .message-content {
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            position: relative;
            word-wrap: break-word;
            line-height: 1.5;
        }

        .message.sent .message-content {
            background: var(--primary);
            color: white;
            border-top-right-radius: 0;
        }

        .message.received .message-content {
            background: var(--light);
            color: var(--dark);
            border-top-left-radius: 0;
            box-shadow: var(--box-shadow);
        }

        .message-info {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .message-sender {
            font-weight: 600;
            color: var(--dark);
            margin-right: 0.5rem;
        }

        .message-time {
            color: var(--gray);
            font-size: 0.75rem;
        }

        .message-input-container {
            padding: 1rem;
            background: var(--light);
            border-top: 1px solid var(--light-gray);
            display: none;
        }

        .message-form {
            display: flex;
            gap: 0.75rem;
            width: 100%;
        }

        .message-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            resize: none;
            font-family: inherit;
            font-size: 0.95rem;
            transition: var(--transition);
            max-height: 120px;
        }

        .message-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .send-btn {
            padding: 0 1.25rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-btn:hover {
            background: var(--primary-dark);
        }

        .send-btn i {
            font-size: 1.1rem;
        }

        /* Empty States */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--gray);
            text-align: center;
            padding: 2rem;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--light-gray);
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .empty-state p {
            font-size: 0.95rem;
            max-width: 300px;
        }

        /* Group Info */
        .group-info {
            font-size: 0.85rem;
            color: var(--gray);
            display: flex;
            flex-direction: column;
        }

        .group-meta {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.25rem;
            font-size: 0.8rem;
        }

        .group-creator {
            font-style: italic;
            color: var(--success);
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--light);
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .modal-header button {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .modal-header button:hover {
            color: var(--danger);
        }

        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Members List */
        .members-list {
            max-height: 300px;
            overflow-y: auto;
            margin: 1rem 0;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
        }

        .member-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .member-item:last-child {
            border-bottom: none;
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .member-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .member-name {
            font-size: 0.95rem;
        }

        .you-badge {
            font-size: 0.75rem;
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            margin-left: 0.5rem;
        }

        .creator-badge {
            font-size: 0.75rem;
            background: rgba(59, 165, 92, 0.1);
            color: var(--success);
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            margin-left: 0.5rem;
        }

        .member-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        .action-btn:hover {
            background: var(--light-gray);
        }

        .action-btn.remove {
            color: var(--danger);
        }

        .action-btn.remove:hover {
            background: rgba(237, 66, 69, 0.1);
        }

        /* Friend Checkbox */
        .friend-checkbox {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--light-gray);
            transition: var(--transition);
        }

        .friend-checkbox:hover {
            background: var(--light-gray);
        }

        .friend-checkbox input {
            margin-right: 1rem;
        }

        .friend-checkbox label {
            flex: 1;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .contacts-sidebar {
                width: 300px;
            }
        }

        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: calc(100vh - 60px);
            }

            .contacts-sidebar {
                width: 100%;
                height: 40%;
                border-right: none;
                border-bottom: 1px solid var(--light-gray);
            }

            .chat-area {
                height: 60%;
            }

            .message {
                max-width: 85%;
            }
        }

        @media (max-width: 576px) {
            header {
                padding: 0.8rem 1rem;
            }

            .logo h1 {
                display: none;
            }

            .message {
                max-width: 90%;
            }
        }
    </style>
</head>

<body>
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

    <div class="main-container">
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
                    <a href="mensagens.php" class="nav-link active">
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

        <!-- Chat Interface -->
        <div class="chat-container">
            <!-- Contacts List -->
            <div class="contacts-sidebar">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Pesquisar conversas..." id="searchContacts">
                </div>

                <div class="contacts-header">
                    <h3>Amigos</h3>
                </div>

                <ul class="contacts-list" id="listaAmigos">
                    <?php if (mysqli_num_rows($resultAmigos) > 0): ?>
                        <?php while ($amigo = mysqli_fetch_assoc($resultAmigos)): ?>
                            <li class="contact-item" onclick="abrirChatPrivado(<?php echo $amigo['id_utilizador']; ?>, '<?php echo htmlspecialchars($amigo['nome']); ?>')">
                                <div class="contact-avatar">
                                    <?php echo strtoupper(substr($amigo['nome'], 0, 1)); ?>
                                </div>
                                <div class="contact-info">
                                    <h5><?php echo htmlspecialchars($amigo['nome']); ?></h5>
                                    <p>Online</p>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="empty-state">
                            <i class="fas fa-user-friends"></i>
                            <h3>Nenhum amigo adicionado</h3>
                            <p>Adicione amigos para começar a conversar</p>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="contacts-header">
                    <h3>Grupos</h3>
                    <button class="btn-icon" onclick="openCreateGroupModal()" title="Criar grupo">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <ul class="contacts-list" id="listaGrupos">
                    <?php if (mysqli_num_rows($resultGrupos) > 0): ?>
                        <?php while ($grupo = mysqli_fetch_assoc($resultGrupos)): ?>
                            <li class="contact-item" onclick="abrirChatGrupo(<?php echo $grupo['id_grupo']; ?>, '<?php echo htmlspecialchars($grupo['nome_grupo']); ?>', <?php echo $grupo['id_criador']; ?>)">
                                <div class="contact-avatar">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="contact-info">
                                    <h5><?php echo htmlspecialchars($grupo['nome_grupo']); ?></h5>
                                    <div class="group-info">
                                        <div class="group-meta">
                                            <span><?php echo $grupo['total_membros']; ?> membros</span>
                                            <span><?php echo date('d/m/Y', strtotime($grupo['data_criacao'])); ?></span>
                                        </div>
                                        <?php if ($grupo['id_criador'] == $id_utilizador): ?>
                                            <span class="group-creator">Criado por você</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>Você não está em nenhum grupo</h3>
                            <p>Crie ou entre em um grupo para começar</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Chat Area -->
            <div class="chat-area">
                <div class="chat-header" id="chatHeader">
                    <div class="chat-title">Selecione uma conversa</div>
                </div>

                <div class="chat-messages" id="chatMensagens">
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>Nenhuma conversa selecionada</h3>
                        <p>Selecione um amigo ou grupo na lista ao lado</p>
                    </div>
                </div>

                <div class="message-input-container" id="messageInputContainer">
                    <form class="message-form" id="messageForm">
                        <textarea class="message-input" id="mensagemTexto" rows="1" placeholder="Digite sua mensagem..."></textarea>
                        <button type="submit" class="send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para criar grupo -->
    <div class="modal" id="createGroupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Criar Novo Grupo</h3>
                <button onclick="closeModal('createGroupModal')">&times;</button>
            </div>
            <form id="createGroupForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="groupName">Nome do Grupo*</label>
                        <input type="text" class="form-control" id="groupName" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label>Adicionar Membros:</label>
                        <div class="members-list">
                            <?php
                            // Resetar o ponteiro do resultado para poder iterar novamente
                            mysqli_data_seek($resultAmigos, 0);
                            while ($amigo = mysqli_fetch_assoc($resultAmigos)): ?>
                                <div class="friend-checkbox">
                                    <input type="checkbox" name="membros[]" value="<?php echo $amigo['id_utilizador']; ?>" id="friend_<?php echo $amigo['id_utilizador']; ?>">
                                    <label for="friend_<?php echo $amigo['id_utilizador']; ?>"><?php echo htmlspecialchars($amigo['nome']); ?></label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal('createGroupModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Grupo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para gerenciar grupo -->
    <div class="modal" id="manageGroupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="manageGroupTitle">Gerenciar Grupo</h3>
                <button onclick="closeModal('manageGroupModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <h4>Membros do Grupo</h4>
                    <div class="members-list" id="groupMembersList"></div>
                </div>
                <div class="form-group" id="addMembersSection">
                    <h4>Adicionar Membros</h4>
                    <div class="members-list" id="availableFriendsList">
                        <!-- Amigos disponíveis serão carregados via AJAX -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteGroupBtn" style="display: none;">Apagar Grupo</button>
                <button type="button" class="btn btn-warning" id="leaveGroupBtn" style="display: none;">Sair do Grupo</button>
                <button type="button" class="btn" onclick="closeModal('manageGroupModal')">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        let idDestinatario = null;
        let idGrupo = null;
        let currentChatName = null;
        let isGroupAdmin = false;

        // Toggle sidebar on mobile
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        // Open private chat
        function abrirChatPrivado(idAmigo, nomeAmigo) {
            // Reset active state
            document.querySelectorAll('.contact-item').forEach(item => {
                item.classList.remove('active');
            });

            // Set current item as active
            event.currentTarget.classList.add('active');

            // Set current chat info
            idDestinatario = idAmigo;
            idGrupo = null;
            currentChatName = nomeAmigo;
            isGroupAdmin = false;

            // Update UI
            document.getElementById('chatHeader').innerHTML = `
                <div class="chat-title">${nomeAmigo}</div>
            `;
            document.getElementById('messageInputContainer').style.display = 'flex';

            // Load messages
            carregarMensagens("privado", idAmigo);
        }

        // Open group chat
        function abrirChatGrupo(idGrupoSelecionado, nomeGrupo, idCriador) {
            // Reset active state
            document.querySelectorAll('.contact-item').forEach(item => {
                item.classList.remove('active');
            });

            // Set current item as active
            event.currentTarget.classList.add('active');

            // Set current chat info
            idGrupo = idGrupoSelecionado;
            idDestinatario = null;
            currentChatName = nomeGrupo;
            isGroupAdmin = (idCriador == <?php echo $id_utilizador; ?>);

            // Update UI
            const chatHeader = document.getElementById('chatHeader');
            chatHeader.innerHTML = `
                <div class="chat-title">${nomeGrupo}</div>
                <div class="group-actions">
                    <button class="group-action-btn" title="Membros" onclick="openManageGroupModal(${idGrupoSelecionado}, '${nomeGrupo}')">
                        <i class="fas fa-users"></i>
                    </button>
                    ${isGroupAdmin ? `
                        <button class="group-action-btn" title="Adicionar membro" onclick="openAddMembersSection(${idGrupoSelecionado}, '${nomeGrupo}')">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <button class="group-action-btn delete" title="Apagar grupo" onclick="confirmDeleteGroup(${idGrupoSelecionado})">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            `;

            document.getElementById('messageInputContainer').style.display = 'flex';
            carregarMensagens("grupo", idGrupoSelecionado);
        }

        // Load messages
        function carregarMensagens(tipo, id) {
            const chatMensagens = document.getElementById("chatMensagens");
            chatMensagens.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--gray);">Carregando mensagens...</div>';

            fetch(`buscarMensagens.php?tipo=${tipo}&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    chatMensagens.innerHTML = '';

                    if (data.mensagens && data.mensagens.length > 0) {
                        data.mensagens.forEach(mensagem => {
                            const messageDiv = document.createElement("div");
                            messageDiv.classList.add("message");
                            messageDiv.classList.add(mensagem.tipo === "enviada" ? "sent" : "received");

                            // Message info (sender and time)
                            const messageInfo = document.createElement("div");
                            messageInfo.classList.add("message-info");

                            if (tipo === "grupo" && mensagem.tipo === "received") {
                                const senderSpan = document.createElement("span");
                                senderSpan.classList.add("message-sender");
                                senderSpan.textContent = mensagem.remetente_nome;
                                messageInfo.appendChild(senderSpan);
                            }

                            const timeSpan = document.createElement("span");
                            timeSpan.classList.add("message-time");
                            timeSpan.textContent = formatMessageTime(mensagem.data_envio);
                            messageInfo.appendChild(timeSpan);

                            messageDiv.appendChild(messageInfo);

                            // Message content
                            const messageContent = document.createElement("div");
                            messageContent.classList.add("message-content");
                            messageContent.textContent = mensagem.texto;
                            messageDiv.appendChild(messageContent);

                            chatMensagens.appendChild(messageDiv);
                        });
                    } else {
                        chatMensagens.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-comment-slash"></i>
                                <h3>Nenhuma mensagem ainda</h3>
                                <p>Envie a primeira mensagem para ${currentChatName}</p>
                            </div>
                        `;
                    }

                    // Scroll to bottom
                    chatMensagens.scrollTop = chatMensagens.scrollHeight;
                })
                .catch(error => {
                    chatMensagens.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--danger);">Erro ao carregar mensagens</div>';
                    console.error('Error:', error);
                });
        }

        // Format message time
        function formatMessageTime(timestamp) {
            try {
                // Corrige o formato para ISO
                const date = new Date(timestamp.replace(' ', 'T'));
                if (isNaN(date.getTime())) {
                    return 'Agora';
                }

                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);

                if (diffInSeconds < 60) {
                    return 'Agora';
                }

                // Se for hoje
                if (date.toDateString() === now.toDateString()) {
                    return date.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                // Se for ontem
                const yesterday = new Date(now);
                yesterday.setDate(yesterday.getDate() - 1);
                if (date.toDateString() === yesterday.toDateString()) {
                    return 'Ontem ' + date.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                // Se for este ano
                if (date.getFullYear() === now.getFullYear()) {
                    return date.toLocaleDateString([], {
                        month: 'short',
                        day: 'numeric'
                    });
                }

                // Mais antigo
                return date.toLocaleDateString([], {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (e) {
                console.error('Erro ao formatar data:', e);
                return 'Agora';
            }
        }
        // Send message
        function enviarMensagem() {
            const mensagemTexto = document.getElementById("mensagemTexto").value.trim();
            if (!mensagemTexto || (!idDestinatario && !idGrupo)) return;

            const formData = new FormData();
            formData.append("mensagem", mensagemTexto);
            if (idDestinatario) {
                formData.append("id_destinatario", idDestinatario);
                formData.append("tipo", "privado");
            }
            if (idGrupo) {
                formData.append("id_grupo", idGrupo);
                formData.append("tipo", "grupo");
            }

            fetch("enviarMensagem.php", {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("mensagemTexto").value = "";
                        if (idDestinatario) {
                            carregarMensagens("privado", idDestinatario);
                        } else {
                            carregarMensagens("grupo", idGrupo);
                        }
                    } else {
                        alert("Erro ao enviar mensagem: " + (data.message || "Erro desconhecido"));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Erro ao enviar mensagem");
                });
        }

        // Form submission
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            enviarMensagem();
        });

        // Search contacts
        document.getElementById('searchContacts').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const items = document.querySelectorAll('.contact-item:not(.empty-state)');

            items.forEach(item => {
                const name = item.textContent.toLowerCase();
                if (name.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Open create group modal
        function openCreateGroupModal() {
            document.getElementById('createGroupModal').style.display = 'flex';
        }

        // Open manage group modal
        function openManageGroupModal(groupId, groupName) {
            document.getElementById('manageGroupTitle').textContent = `Gerenciar Grupo: ${groupName}`;
            document.getElementById('deleteGroupBtn').style.display = isGroupAdmin ? 'block' : 'none';
            document.getElementById('deleteGroupBtn').onclick = () => confirmDeleteGroup(groupId);
            document.getElementById('addMembersSection').style.display = isGroupAdmin ? 'block' : 'none';
            document.getElementById('groupMembersList').style.display = 'block';
            // Mostra botão "Sair do Grupo" se não for criador
            document.getElementById('leaveGroupBtn').style.display = isGroupAdmin ? 'none' : 'block';
            document.getElementById('leaveGroupBtn').onclick = function() {
                sairDoGrupo(groupId);
            };

            // Load group members
            fetch(`get_group_members.php?id_grupo=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    const membersList = document.getElementById('groupMembersList');
                    membersList.innerHTML = '';

                    if (data.membros && data.membros.length > 0) {
                        data.membros.forEach(membro => {
                            const memberItem = document.createElement('div');
                            memberItem.className = 'member-item';

                            const memberInfo = document.createElement('div');
                            memberInfo.className = 'member-info';

                            const memberAvatar = document.createElement('div');
                            memberAvatar.className = 'member-avatar';
                            memberAvatar.textContent = membro.nome.charAt(0).toUpperCase();
                            memberInfo.appendChild(memberAvatar);

                            const memberName = document.createElement('span');
                            memberName.className = 'member-name';
                            memberName.textContent = membro.nome;

                            if (membro.id_utilizador == <?php echo $id_utilizador; ?>) {
                                const youBadge = document.createElement('span');
                                youBadge.className = 'you-badge';
                                youBadge.textContent = 'Você';
                                memberName.appendChild(youBadge);
                            }

                            if (membro.id_criador) {
                                const creatorBadge = document.createElement('span');
                                creatorBadge.className = 'creator-badge';
                                creatorBadge.textContent = 'Criador';
                                memberName.appendChild(creatorBadge);
                            }

                            memberInfo.appendChild(memberName);
                            memberItem.appendChild(memberInfo);

                            // Add actions if admin and not yourself
                            if (membro.id_utilizador != <?php echo $id_utilizador; ?> && isGroupAdmin) {
                                const memberActions = document.createElement('div');
                                memberActions.className = 'member-actions';

                                const removeBtn = document.createElement('button');
                                removeBtn.className = 'action-btn remove';
                                removeBtn.innerHTML = '<i class="fas fa-user-minus"></i> Remover';
                                removeBtn.onclick = () => removeMember(groupId, membro.id_utilizador);

                                memberActions.appendChild(removeBtn);
                                memberItem.appendChild(memberActions);
                            }

                            membersList.appendChild(memberItem);
                        });
                    } else {
                        membersList.innerHTML = '<p style="padding: 1rem; text-align: center; color: var(--gray);">Nenhum membro no grupo</p>';
                    }
                });

            // Load available friends if admin
            if (isGroupAdmin) {
                fetch(`get_available_friends.php?id_grupo=${groupId}`)
                    .then(response => response.json())
                    .then(data => {
                        const friendsList = document.getElementById('availableFriendsList');
                        friendsList.innerHTML = '';

                        if (data.amigos && data.amigos.length > 0) {
                            data.amigos.forEach(amigo => {
                                const friendItem = document.createElement('div');
                                friendItem.className = 'friend-checkbox';
                                friendItem.innerHTML = `
                                    <input type="checkbox" id="friend_${amigo.id_utilizador}" value="${amigo.id_utilizador}">
                                    <label for="friend_${amigo.id_utilizador}">${amigo.nome}</label>
                                `;
                                friendsList.appendChild(friendItem);
                            });

                            const addBtn = document.createElement('button');
                            addBtn.className = 'btn btn-primary';
                            addBtn.style.margin = '1rem auto';
                            addBtn.textContent = 'Adicionar Selecionados';
                            addBtn.onclick = (e) => {
                                e.preventDefault();
                                addMembersToGroup(groupId);
                            };
                            friendsList.appendChild(addBtn);
                        } else {
                            friendsList.innerHTML = '<p style="padding: 1rem; text-align: center; color: var(--gray);">Nenhum amigo disponível para adicionar</p>';
                        }
                    });
            }

            document.getElementById('manageGroupModal').style.display = 'flex';
        }

        // Open only add members section
        function openAddMembersSection(groupId, groupName) {
            document.getElementById('manageGroupTitle').textContent = `Adicionar Membros: ${groupName}`;
            document.getElementById('deleteGroupBtn').style.display = 'none';
            document.getElementById('groupMembersList').style.display = 'none';
            document.getElementById('addMembersSection').style.display = 'block';

            fetch(`get_available_friends.php?id_grupo=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    const friendsList = document.getElementById('availableFriendsList');
                    friendsList.innerHTML = '';

                    if (data.amigos && data.amigos.length > 0) {
                        data.amigos.forEach(amigo => {
                            const friendItem = document.createElement('div');
                            friendItem.className = 'friend-checkbox';
                            friendItem.innerHTML = `
                                <input type="checkbox" id="friend_${amigo.id_utilizador}" value="${amigo.id_utilizador}">
                                <label for="friend_${amigo.id_utilizador}">${amigo.nome}</label>
                            `;
                            friendsList.appendChild(friendItem);
                        });

                        const addBtn = document.createElement('button');
                        addBtn.className = 'btn btn-primary';
                        addBtn.style.margin = '1rem auto';
                        addBtn.textContent = 'Adicionar Selecionados';
                        addBtn.onclick = (e) => {
                            e.preventDefault();
                            addMembersToGroup(groupId);
                            closeModal('manageGroupModal');
                        };
                        friendsList.appendChild(addBtn);
                    } else {
                        friendsList.innerHTML = '<p style="padding: 1rem; text-align: center; color: var(--gray);">Nenhum amigo disponível para adicionar</p>';
                    }
                });

            document.getElementById('manageGroupModal').style.display = 'flex';
        }
        // Função para sair do grupo
        function sairDoGrupo(groupId) {
            if (!confirm('Tem certeza que deseja sair deste grupo?')) return;
            fetch('sair_grupo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id_grupo=${groupId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Saiu do grupo com sucesso!');
                        closeModal('manageGroupModal');
                        location.reload();
                    } else {
                        alert('Erro ao sair do grupo: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    alert('Erro ao sair do grupo');
                });
        }

        // Formata data/hora para dd/mm/yyyy HH:MM
        function formatMessageTime(timestamp) {
            if (!timestamp) return '';
            const d = new Date(timestamp.replace(' ', 'T'));
            if (isNaN(d.getTime())) return timestamp;
            const dia = String(d.getDate()).padStart(2, '0');
            const mes = String(d.getMonth() + 1).padStart(2, '0');
            const ano = d.getFullYear();
            const hora = String(d.getHours()).padStart(2, '0');
            const min = String(d.getMinutes()).padStart(2, '0');
            return `${dia}/${mes}/${ano} ${hora}:${min}`;
        }
        // Add members to group
        function addMembersToGroup(groupId) {
            const checkboxes = document.querySelectorAll('#availableFriendsList input[type="checkbox"]:checked');
            const membersToAdd = Array.from(checkboxes).map(cb => cb.value);

            if (membersToAdd.length === 0) {
                alert('Selecione pelo menos um amigo para adicionar');
                return;
            }

            fetch('add_group_members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_grupo=${groupId}&membros=${JSON.stringify(membersToAdd)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Membros adicionados com sucesso!');
                        openManageGroupModal(groupId, currentChatName);
                    } else {
                        alert('Erro ao adicionar membros: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao adicionar membros');
                });
        }

        // Remove member from group
        function removeMember(groupId, memberId) {
            if (!confirm('Tem certeza que deseja remover este membro do grupo?')) return;

            fetch('remove_group_member.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_grupo=${groupId}&id_utilizador=${memberId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Membro removido com sucesso!');
                        openManageGroupModal(groupId, currentChatName);
                    } else {
                        alert('Erro ao remover membro: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao remover membro');
                });
        }

        // Confirm group deletion
        function confirmDeleteGroup(groupId) {
            if (!confirm('Tem certeza que deseja APAGAR este grupo? Esta ação não pode ser desfeita!')) return;

            fetch('delete_group.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_grupo=${groupId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Grupo apagado com sucesso!');
                        closeModal('manageGroupModal');
                        location.reload();
                    } else {
                        alert('Erro ao apagar grupo: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao apagar grupo');
                });
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Create group form submission
        document.getElementById('createGroupForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const groupName = document.getElementById('groupName').value.trim();
            const checkboxes = document.querySelectorAll('#createGroupModal input[type="checkbox"]:checked');
            const members = Array.from(checkboxes).map(cb => cb.value);

            if (!groupName) {
                alert('Por favor, insira um nome para o grupo');
                return;
            }

            fetch('criar_grupo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `nome_grupo=${encodeURIComponent(groupName)}&membros=${JSON.stringify(members)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Grupo criado com sucesso!');
                        closeModal('createGroupModal');
                        location.reload();
                    } else {
                        alert('Erro ao criar grupo: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao criar grupo');
                });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        });

        // Auto-resize textarea
        document.getElementById('mensagemTexto').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Hide message input initially
        document.getElementById('messageInputContainer').style.display = 'none';
    </script>
</body>

</html>