<?php
// filepath: c:\xampp\htdocs\Projeto\paginas\admin\admin.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../baseDados/basedados.php";

// Verificar se é admin
if (!isset($_SESSION["id_utilizador"]) || !isset($_SESSION["nivel"]) || $_SESSION["nivel"] < 2) {
    header("Location: ../visitante/login.php");
    exit();
}

// Estatísticas para o dashboard
$stats = [
    'total_utilizadores' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM utilizadores"))['total'],
    'total_conteudos' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM conteudos"))['total'],
    'reports_pendentes' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reports WHERE status = 'pendente'"))['total'],
    'escolas' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM escolas"))['total']
];
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Administração</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --danger: #f72585;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fb;
            margin: 0;
        }

        header {
            background: #fff;
            box-shadow: var(--box-shadow);
            padding: 1rem 2rem;
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
            color: var(--primary);
            font-size: 1.5rem;
            margin: 0;
        }

        .user-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1.2rem;
            border-radius: var(--border-radius);
            border: none;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-outline {
            background: none;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: #fff;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            background: #fff;
            box-shadow: 2px 0 10px #0001;
            padding: 2rem 1rem 1rem 1rem;
        }

        .sidebar-header {
            margin-bottom: 2rem;
        }

        .sidebar-header h3 {
            color: var(--primary);
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1rem;
            color: #222;
            text-decoration: none;
            border-radius: var(--border-radius);
            margin-bottom: 0.5rem;
            transition: 0.2s;
        }

        .nav-link.active,
        .nav-link:hover {
            background: #e9ecef;
            color: var(--primary);
        }

        .main-content {
            margin-left: 240px;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            text-align: center;
        }

        .stat-card h3 {
            color: var(--primary);
            font-size: 2rem;
            margin: 0;
        }

        .stat-card p {
            color: var(--gray);
            margin: 0.5rem 0 0 0;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .admin-table th {
            background: var(--primary);
            color: #fff;
        }

        .admin-table tr:hover {
            background: #f1f3fa;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: var(--box-shadow);
        }

        .modal-header,
        .modal-footer {
            padding: 1rem 1.5rem;
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        @media (max-width: 900px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .sidebar {
                position: static;
                width: 100%;
                height: auto;
                box-shadow: none;
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <h1>Administração</h1>
        </div>
        <div class="user-actions">
            <a href="../utilizador/logout.php" class="btn btn-primary"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </header>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Administração</h3>
        </div>
        <ul class="nav-menu">
            <li><a href="#" class="nav-link active" onclick="openTab('dashboard', event)"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="#" class="nav-link" onclick="openTab('reports', event)"><i class="fas fa-flag"></i> Reports</a></li>
            <li><a href="#" class="nav-link" onclick="openTab('utilizadores', event)"><i class="fas fa-users"></i> Utilizadores</a></li>
            <li><a href="#" class="nav-link" onclick="openTab('conteudos', event)"><i class="fas fa-file-alt"></i> Conteúdos</a></li>
            <li><a href="#" class="nav-link" onclick="openTab('escolas', event)"><i class="fas fa-school"></i> Escolas</a></li>
            <li><a href="#" class="nav-link" onclick="openTab('cursos', event)"><i class="fas fa-graduation-cap"></i> Cursos</a></li>
            <li><a href="#" class="nav-link" onclick="openTab('disciplinas', event)"><i class="fas fa-book"></i> Disciplinas</a></li>
        </ul>
    </div>
    <div class="main-content">
        <!-- Dashboard -->
        <div id="dashboard" class="tab-content active">
            <h2>Dashboard</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?= $stats['total_utilizadores'] ?></h3>
                    <p>Utilizadores</p>
                </div>
                <div class="stat-card">
                    <h3><?= $stats['total_conteudos'] ?></h3>
                    <p>Conteúdos</p>
                </div>
                <div class="stat-card">
                    <h3><?= $stats['reports_pendentes'] ?></h3>
                    <p>Reports Pendentes</p>
                </div>
                <div class="stat-card">
                    <h3><?= $stats['escolas'] ?></h3>
                    <p>Escolas</p>
                </div>
            </div>
        </div>
        <!-- Reports -->
        <div id="reports" class="tab-content">
            <h2>Reports</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reportador</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $reports = mysqli_query($conn, "SELECT r.*, u.nome as reportador FROM reports r LEFT JOIN utilizadores u ON r.id_utilizador = u.id_utilizador ORDER BY r.data_report DESC LIMIT 20");
                    while ($r = mysqli_fetch_assoc($reports)): ?>
                        <tr>
                            <td><?= $r['id_report'] ?></td>
                            <td><?= htmlspecialchars($r['reportador']) ?></td>
                            <td><?= htmlspecialchars($r['tipo']) ?></td>
                            <td><?= htmlspecialchars($r['status']) ?></td>
                            <td><?= $r['data_report'] ?></td>
                            <td>
                                <button class="btn btn-primary" onclick="viewReport(<?= $r['id_report'] ?>)">Ver</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Utilizadores -->
        <div id="utilizadores" class="tab-content">
            <h2>Utilizadores</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Escola</th>
                        <th>Curso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users_query = "SELECT u.*, e.nome as escola_nome, c.nome as curso_nome 
                                FROM utilizadores u
                                LEFT JOIN escolas e ON u.id_escola = e.id_escola
                                LEFT JOIN cursos c ON u.id_curso = c.id_curso
                                ORDER BY u.nome";
                    $users_result = mysqli_query($conn, $users_query);
                    while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?= $user['id_utilizador'] ?></td>
                            <td><?= htmlspecialchars($user['nome']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $user['tipo'])) ?></td>
                            <td><?= $user['escola_nome'] ?? '-' ?></td>
                            <td><?= $user['curso_nome'] ?? '-' ?></td>
                            <td>
                                <button class="btn btn-primary" onclick="editUser(<?= $user['id_utilizador'] ?>)"><i class="fas fa-edit"></i> Editar</button>
                                <button class="btn btn-danger" onclick="confirmDelete('user', <?= $user['id_utilizador'] ?>)"><i class="fas fa-trash"></i> Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Conteúdos -->
        <div id="conteudos" class="tab-content">
            <h2>Conteúdos</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Descrição</th>
                        <th>Formato</th>
                        <th>Data Upload</th>
                        <th>Utilizador</th>
                        <th>Disciplina</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conteudos = mysqli_query($conn, "SELECT c.*, u.nome as utilizador_nome, d.nome as disciplina_nome FROM conteudos c LEFT JOIN utilizadores u ON c.id_utilizador = u.id_utilizador LEFT JOIN disciplinas d ON c.id_disciplina = d.id_disciplina ORDER BY c.data_upload DESC");
                    while ($c = mysqli_fetch_assoc($conteudos)): ?>
                        <tr>
                            <td><?= $c['id_conteudo'] ?></td>
                            <td><?= htmlspecialchars($c['titulo']) ?></td>
                            <td><?= htmlspecialchars($c['descricao']) ?></td>
                            <td><?= htmlspecialchars($c['formato']) ?></td>
                            <td><?= $c['data_upload'] ?></td>
                            <td><?= htmlspecialchars($c['utilizador_nome']) ?></td>
                            <td><?= htmlspecialchars($c['disciplina_nome']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Escolas -->
        <div id="escolas" class="tab-content">
            <h2>Escolas</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $escolas = mysqli_query($conn, "SELECT * FROM escolas ORDER BY nome");
                    while ($e = mysqli_fetch_assoc($escolas)): ?>
                        <tr>
                            <td><?= $e['id_escola'] ?></td>
                            <td><?= htmlspecialchars($e['nome']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Cursos -->
        <div id="cursos" class="tab-content">
            <h2>Cursos</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Escola</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cursos = mysqli_query($conn, "SELECT c.*, e.nome as escola_nome FROM cursos c LEFT JOIN escolas e ON c.id_escola = e.id_escola ORDER BY c.nome");
                    while ($c = mysqli_fetch_assoc($cursos)): ?>
                        <tr>
                            <td><?= $c['id_curso'] ?></td>
                            <td><?= htmlspecialchars($c['nome']) ?></td>
                            <td><?= htmlspecialchars($c['escola_nome']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <!-- Disciplinas -->
        <div id="disciplinas" class="tab-content">
            <h2>Disciplinas</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Curso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $disciplinas = mysqli_query($conn, "SELECT d.*, c.nome as curso_nome FROM disciplinas d LEFT JOIN cursos c ON d.id_curso = c.id_curso ORDER BY d.nome");
                    while ($d = mysqli_fetch_assoc($disciplinas)): ?>
                        <tr>
                            <td><?= $d['id_disciplina'] ?></td>
                            <td><?= htmlspecialchars($d['nome']) ?></td>
                            <td><?= htmlspecialchars($d['curso_nome']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Utilizador -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="userModalTitle">Editar Utilizador</h3>
                <button onclick="closeModal()">&times;</button>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="userName">Nome</label>
                        <input type="text" id="userName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmail">Email</label>
                        <input type="email" id="userEmail" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="userType">Tipo</label>
                        <select id="userType" class="form-control" required>
                            <option value="aluno">Aluno</option>
                            <option value="nao_aluno">Não Aluno</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal de Report (exemplo) -->
    <div class="modal" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detalhes do Report</h3>
                <button onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="reportModalBody">
                <!-- Conteúdo AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeModal()">Fechar</button>
            </div>
        </div>
    </div>
    <footer>
        <p style="text-align:center; color:#888; margin-top:2rem;">&copy; 2023 Plataforma de Gestão de Conteúdos. Todos os direitos reservados.</p>
    </footer>
    <script>
        // Navegação entre tabs
        function openTab(tabName, event) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            if (event) event.currentTarget.classList.add('active');
        }
        // Modal Utilizador
        function editUser(id) {
            fetch(`get_user.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('userModalTitle').textContent = 'Editar Utilizador';
                    document.getElementById('userId').value = data.id_utilizador;
                    document.getElementById('userName').value = data.nome;
                    document.getElementById('userEmail').value = data.email;
                    document.getElementById('userType').value = data.tipo;
                    document.getElementById('userModal').style.display = 'flex';
                });
        }
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = {
                id: document.getElementById('userId').value,
                nome: document.getElementById('userName').value,
                email: document.getElementById('userEmail').value,
                tipo: document.getElementById('userType').value
            };
            fetch('save_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            }).then(() => {
                closeModal();
                location.reload();
            });
        });
        // Modal Report
        function viewReport(id) {
            fetch(`get_report.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    let html = `<p><strong>Reportador:</strong> ${data.reportador || ''}</p>
                                <p><strong>Tipo:</strong> ${data.tipo || ''}</p>
                                <p><strong>Status:</strong> ${data.status || ''}</p>
                                <p><strong>Data:</strong> ${data.data_report || ''}</p>
                                <p><strong>Descrição:</strong><br>${data.descricao || ''}</p>`;
                    document.getElementById('reportModalBody').innerHTML = html;
                    document.getElementById('reportModal').style.display = 'flex';
                });
        }
        // Fechar modal
        function closeModal() {
            document.querySelectorAll('.modal').forEach(modal => modal.style.display = 'none');
        }
        // Eliminar utilizador
        function confirmDelete(type, id) {
            if (confirm('Tem certeza que deseja excluir este item?')) {
                fetch(`delete_${type}.php?id=${id}`).then(() => location.reload());
            }
        }
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal();
            }
        };
    </script>
</body>

</html>