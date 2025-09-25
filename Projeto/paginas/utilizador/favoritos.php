<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\utilizador\favoritos.php
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

// Buscar pastas de favoritos do utilizador
$queryPastas = "SELECT * FROM pastas_favoritos WHERE id_utilizador = {$_SESSION['id_utilizador']}";
$resultPastas = mysqli_query($conn, $queryPastas);

// Buscar conteúdos favoritos do utilizador
$queryFavoritos = "SELECT c.id_conteudo, c.titulo, c.descricao, c.formato, 
                   p.nome AS nome_pasta, p.id_pasta
                   FROM favoritos f
                   JOIN conteudos c ON f.id_conteudo = c.id_conteudo
                   LEFT JOIN pastas_favoritos p ON f.id_pasta = p.id_pasta
                   WHERE f.id_utilizador = {$_SESSION['id_utilizador']}
                   ORDER BY p.nome, c.titulo";
$resultFavoritos = mysqli_query($conn, $queryFavoritos);

// Agrupar favoritos por pasta para uso em JS
$favoritosPorPasta = [];
mysqli_data_seek($resultFavoritos, 0);
while ($fav = mysqli_fetch_assoc($resultFavoritos)) {
    $pid = $fav['id_pasta'] ? $fav['id_pasta'] : 0;
    $favoritosPorPasta[$pid][] = $fav;
}

// Voltar o ponteiro das pastas para reutilizar nos selects
mysqli_data_seek($resultPastas, 0);
$pastasArr = [];
while ($pasta = mysqli_fetch_assoc($resultPastas)) {
    $pastasArr[] = $pasta;
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Favoritos</title>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.8rem;
            color: var(--primary);
        }

        .folders-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .folder-btn {
            background: #f1f3fa;
            border: none;
            border-radius: 6px;
            padding: 0.7rem 1.5rem;
            font-weight: 500;
            color: #4361ee;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .folder-btn.active,
        .folder-btn:hover {
            background: #4361ee;
            color: #fff;
        }

        .content-list {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .content-item {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-item:last-child {
            border-bottom: none;
        }

        .content-info h4 {
            margin-bottom: 0.3rem;
        }

        .content-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .content-actions {
            display: flex;
            gap: 1rem;
        }

        .content-action {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            font-size: 1.2rem;
        }

        .content-action:hover {
            color: var(--primary);
        }

        .no-favorites {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            color: var(--primary);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-outline {
            background: none;
            color: #4361ee;
            border: 1px solid #4361ee;
            border-radius: 6px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }

        .btn-outline:hover {
            background: #f1f3fa;
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
                <a href="favoritos.php" class="nav-link active">
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
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-star"></i> Meus Favoritos</h1>
                <button class="btn btn-primary" id="newFolderBtn">
                    <i class="fas fa-plus"></i> Nova Pasta
                </button>
            </div>

            <!-- Barra de Pastas -->
            <div class="folders-bar">
                <button class="folder-btn active" data-id="0">
                    <i class="fas fa-folder-open"></i> Sem Pasta
                </button>
                <?php foreach ($pastasArr as $pasta) { ?>
                    <button class="folder-btn" data-id="<?= $pasta['id_pasta'] ?>">
                        <i class="fas fa-folder"></i> <?= htmlspecialchars($pasta['nome']) ?>
                    </button>
                <?php } ?>
            </div>

            <!-- Lista de Favoritos -->
            <div class="content-list" id="favoritosList">
                <!-- Favoritos serão preenchidos via JS -->
            </div>
        </div>
    </div>

    <!-- Modal para criar/editar pasta -->
    <div class="modal" id="folderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Nova Pasta</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="folderForm">
                <input type="hidden" id="folderId" name="id_pasta">
                <div class="form-group">
                    <label for="folderName">Nome da Pasta</label>
                    <input type="text" id="folderName" name="nome" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline close-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para mover favorito -->
    <div class="modal" id="moveModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Mover para Pasta</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="moveForm">
                <input type="hidden" id="contentId" name="id_conteudo">
                <div class="form-group">
                    <label for="targetFolder">Selecione a pasta</label>
                    <select id="targetFolder" name="id_pasta" class="form-control">
                        <option value="0">Sem Pasta</option>
                        <?php foreach ($pastasArr as $pasta) { ?>
                            <option value="<?= $pasta['id_pasta'] ?>"><?= htmlspecialchars($pasta['nome']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline close-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Mover</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="fecharTodosModais()"></div>

    <script>
        // Favoritos agrupados por pasta para JS
        const favoritosPHP = <?php echo json_encode($favoritosPorPasta); ?>;

        function renderFavoritos(pastaId) {
            const list = document.getElementById('favoritosList');
            list.innerHTML = '';
            const favoritos = favoritosPHP[pastaId] || [];
            if (favoritos.length === 0) {
                list.innerHTML = `
                    <div class="no-favorites">
                        <i class="fas fa-star" style="font-size: 3rem; margin-bottom: 1rem; color: var(--light-gray);"></i>
                        <h3>Nenhum conteúdo favoritado nesta pasta</h3>
                        <p>Adicione conteúdos aos seus favoritos para encontrá-los mais facilmente depois.</p>
                    </div>
                `;
                return;
            }
            favoritos.forEach(favorito => {
                list.innerHTML += `
                    <div class="content-item">
                        <div class="content-info">
                            <h4>${favorito.titulo}</h4>
                            <p>${favorito.descricao}</p>
                        </div>
                        <div class="content-actions">
                            <a href="../../uploads/${favorito.formato}" download class="content-action" title="Baixar conteúdo">
                                <i class="fas fa-download"></i>
                            </a>
                            <button class="content-action move-favorite" data-id="${favorito.id_conteudo}" title="Mover este favorito para uma pasta">
                                <i class="fas fa-folder"></i>
                            </button>
                            <button class="content-action remove-favorite" data-id="${favorito.id_conteudo}" title="Remover dos favoritos">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            // Reaplicar eventos dos botões
            setTimeout(() => {
                document.querySelectorAll('.move-favorite').forEach(btn => {
                    btn.onclick = function() {
                        document.getElementById('contentId').value = this.getAttribute('data-id');
                        document.getElementById('moveModal').style.display = 'flex';
                        document.getElementById('overlay').style.display = 'flex';
                    };
                });
                document.querySelectorAll('.remove-favorite').forEach(btn => {
                    btn.onclick = function() {
                        if (confirm('Tem certeza que deseja remover este conteúdo dos seus favoritos?')) {
                            const contentId = this.getAttribute('data-id');
                            fetch('removerFavorito.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `id_conteudo=${contentId}&id_utilizador=<?php echo $_SESSION['id_utilizador']; ?>`
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) window.location.reload();
                                    else alert('Erro: ' + data.message);
                                });
                        }
                    };
                });
            }, 100);
        }

        // Troca de pasta
        document.querySelectorAll('.folder-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.folder-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                renderFavoritos(this.getAttribute('data-id'));
            });
        });

        // Render inicial
        renderFavoritos(0);

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

        // Modal de pasta
        const folderModal = document.getElementById('folderModal');
        const newFolderBtn = document.getElementById('newFolderBtn');
        const folderForm = document.getElementById('folderForm');
        const modalTitle = document.getElementById('modalTitle');
        const folderIdInput = document.getElementById('folderId');
        const folderNameInput = document.getElementById('folderName');
        const closeModalBtns = document.querySelectorAll('.close-modal');

        // Modal de mover favorito
        const moveModal = document.getElementById('moveModal');
        const moveForm = document.getElementById('moveForm');
        const contentIdInput = document.getElementById('contentId');
        const targetFolderSelect = document.getElementById('targetFolder');

        // Abrir modal para nova pasta
        newFolderBtn.addEventListener('click', () => {
            modalTitle.textContent = 'Nova Pasta';
            folderIdInput.value = '';
            folderNameInput.value = '';
            folderModal.style.display = 'flex';
            document.getElementById('overlay').style.display = 'flex';
        });

        // Fechar modais
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                folderModal.style.display = 'none';
                moveModal.style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
            });
        });

        // Submeter formulário de pasta
        folderForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(folderForm);
            formData.append('id_utilizador', <?php echo $_SESSION['id_utilizador']; ?>);
            fetch('criarPastaFavoritos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Ocorreu um erro ao salvar a pasta.');
                });
        });

        // Submeter formulário de mover favorito
        moveForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(moveForm);
            formData.append('id_utilizador', <?php echo $_SESSION['id_utilizador']; ?>);
            fetch('moverFavorito.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Ocorreu um erro ao mover o favorito.');
                });
        });

        // Fechar todos os modais
        function fecharTodosModais() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('folderModal').style.display = 'none';
            document.getElementById('moveModal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('mainContent').style.marginLeft = '0';
        }
    </script>
</body>

</html>