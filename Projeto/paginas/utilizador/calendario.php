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

if (isset($_SESSION["nivel"]) && $_SESSION["nivel"] != 1) {
    header("Location: erro.php");
    exit();
} else if (!isset($_SESSION["nivel"])) {
    header("Location: erro.php");
    exit();
}

if (!isset($_SESSION["id_utilizador"])) {
    header("Location: login.php");
    exit();
}

$id_utilizador = $_SESSION["id_utilizador"];
$nome = $_SESSION['nome'];

// Buscar eventos do mês atual
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');
$primeiroDia = "$ano-$mes-01";
$ultimoDia = date("Y-m-t", strtotime($primeiroDia));

$sql = "SELECT * FROM calendario_eventos WHERE id_utilizador = $id_utilizador AND data BETWEEN '$primeiroDia' AND '$ultimoDia'";
$res = mysqli_query($conn, $sql);
$eventos = [];
while ($row = mysqli_fetch_assoc($res)) {
    $eventos[$row['data']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma | Calendário</title>
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
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: #f5f7fb; color: var(--dark); line-height: 1.6; }
        header { background: white; box-shadow: var(--box-shadow); padding: 0.8rem 2rem; position: fixed; top: 0; left: 0; right: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 1rem; }
        .logo h1 { font-size: 1.5rem; font-weight: 700; color: var(--primary); margin: 0; }
        .menu-toggle { background: none; border: none; font-size: 1.5rem; color: var(--primary); cursor: pointer; transition: var(--transition); }
        .menu-toggle:hover { color: var(--primary-dark); }
        .user-actions { display: flex; align-items: center; gap: 1rem; }
        .user-actions .btn { padding: 0.6rem 1.2rem; border-radius: var(--border-radius); font-weight: 500; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background-color: var(--primary); color: white; border: none; }
        .btn-primary:hover { background-color: var(--primary-dark); transform: translateY(-2px); }
        .btn-outline { background-color: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline:hover { background-color: rgba(67, 97, 238, 0.1); }
        .sidebar { position: fixed; top: 0; left: -300px; width: 300px; height: 100vh; background: white; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1); z-index: 999; transition: var(--transition); padding: 1.5rem; display: flex; flex-direction: column; }
        .sidebar.open { left: 0; }
        .sidebar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--light-gray); }
        .sidebar-header h3 { color: var(--primary); font-size: 1.3rem; }
        .close-btn { background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; }
        .nav-menu { list-style: none; flex: 1; }
        .nav-item { margin-bottom: 0.5rem; }
        .nav-link { display: flex; align-items: center; gap: 1rem; padding: 0.8rem 1rem; color: var(--dark); text-decoration: none; border-radius: var(--border-radius); transition: var(--transition); }
        .nav-link:hover, .nav-link.active { background-color: rgba(67, 97, 238, 0.1); color: var(--primary); }
        .nav-link i { width: 24px; text-align: center; }
        .main-content { margin-top: 80px; padding: 2rem; transition: var(--transition); }
        .calendar { background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 2rem; max-width: 600px; margin: 0 auto; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .calendar-header h2 { color: var(--primary); }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; }
        .calendar-day, .calendar-cell { text-align: center; padding: 0.7rem 0; }
        .calendar-day { font-weight: 600; color: var(--primary); }
        .calendar-cell { background: #f8f9fa; border-radius: 8px; min-height: 70px; position: relative; cursor: pointer; transition: background 0.2s; }
        .calendar-cell:hover { background: #e9ecef; }
        .calendar-cell.today { border: 2px solid var(--primary); }
        .event-dot { width: 8px; height: 8px; background: var(--danger); border-radius: 50%; display: inline-block; margin-top: 4px; }
        .event-list { margin-top: 0.5rem; }
        .event-item { background: var(--light-gray); border-radius: 6px; padding: 0.3rem 0.5rem; margin-bottom: 0.3rem; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center; }
        .event-item.reuniao { background: var(--accent); color: #fff; }
        .event-item.nota { background: var(--warning); color: #fff; }
        .event-remove { background: none; border: none; color: #fff; margin-left: 0.5rem; cursor: pointer; font-size: 1rem; }
        .modal-bg { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); }
        .modal-bg.active { display: flex; align-items: center; justify-content: center; }
        .modal { background: #fff; border-radius: var(--border-radius); padding: 2rem; box-shadow: var(--box-shadow); min-width: 320px; max-width: 95vw; }
        .modal h2 { margin-bottom: 1rem; color: var(--primary); }
        .modal .close-modal { background: none; border: none; font-size: 1.5rem; color: var(--gray); position: absolute; top: 1rem; right: 1rem; cursor: pointer; }
        .modal form { display: flex; flex-direction: column; gap: 1rem; }
        .modal input, .modal textarea, .modal select { padding: 0.7rem; border-radius: 7px; border: 1px solid var(--light-gray); }
        .modal .btn { width: 100%; }
        .overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 998; display: none; }
        .overlay.active { display: block; }
        @media (max-width: 700px) {
            .main-content { padding: 1rem; }
            .calendar { padding: 1rem; }
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
                <a href="meusConteudos.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Meus Conteúdos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="calendario.php" class="nav-link active">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendário</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Conteúdo Principal -->
    <div class="main-content" id="mainContent">
        <div class="calendar">
            <div class="calendar-header">
                <button class="btn btn-outline" onclick="mudarMes(-1)"><i class="fas fa-chevron-left"></i></button>
                <h2 id="mesAno"></h2>
                <button class="btn btn-outline" onclick="mudarMes(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="calendar-grid" id="calendarGrid">
                <!-- Dias da semana e células do calendário serão gerados via JS -->
            </div>
        </div>
    </div>

    <!-- Modal de evento -->
    <div class="modal-bg" id="modalEvento">
        <div class="modal">
            <button class="close-modal" onclick="fecharModalEvento()">&times;</button>
            <h2 id="modalTitulo">Adicionar Evento</h2>
            <form id="formEvento">
                <input type="hidden" name="data" id="dataEvento">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" required>
                    <option value="nota">Nota</option>
                    <option value="reuniao">Reunião</option>
                </select>
                <label for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" maxlength="100" required>
                <label for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" rows="3"></textarea>
                <button type="submit" class="btn btn-primary">Guardar</button>
                <button type="button" class="btn btn-outline" onclick="fecharModalEvento()">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="fecharTodosModais()"></div>

    <script>
        // Sidebar
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

        // Fechar todos os modais
        function fecharTodosModais() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('active');
            document.getElementById('mainContent').style.marginLeft = '0';
            fecharModalEvento();
        }

        // Modal de evento
        function abrirModalEvento(data) {
            document.getElementById('modalEvento').classList.add('active');
            document.getElementById('modalEvento').style.display = 'flex';
            document.getElementById('overlay').classList.add('active');
            document.getElementById('dataEvento').value = data;
            document.getElementById('titulo').value = '';
            document.getElementById('descricao').value = '';
            document.getElementById('tipo').value = 'nota';
        }
        function fecharModalEvento() {
            document.getElementById('modalEvento').classList.remove('active');
            document.getElementById('modalEvento').style.display = 'none';
            document.getElementById('overlay').classList.remove('active');
        }

        // Calendário
        const eventos = <?php echo json_encode($eventos); ?>;
        let mesAtual = <?= $mes ?>;
        let anoAtual = <?= $ano ?>;
        const nomesMeses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
        const nomesDias = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
        function renderCalendario() {
            document.getElementById('mesAno').textContent = `${nomesMeses[mesAtual-1]} de ${anoAtual}`;
            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';
            // Cabeçalho dos dias
            for(let d=0;d<7;d++) {
                const day = document.createElement('div');
                day.className = 'calendar-day';
                day.textContent = nomesDias[d];
                grid.appendChild(day);
            }
            // Dias do mês
            const primeiroDiaSemana = new Date(anoAtual, mesAtual-1, 1).getDay();
            const diasNoMes = new Date(anoAtual, mesAtual, 0).getDate();
            let dia = 1;
            for(let i=0; i<42; i++) {
                const cell = document.createElement('div');
                cell.className = 'calendar-cell';
                if(i >= primeiroDiaSemana && dia <= diasNoMes) {
                    const dataStr = `${anoAtual}-${String(mesAtual).padStart(2,'0')}-${String(dia).padStart(2,'0')}`;
                    cell.setAttribute('data-data', dataStr);
                    cell.onclick = () => abrirModalEvento(dataStr);
                    if (dataStr === '<?= date('Y-m-d') ?>') cell.classList.add('today');
                    cell.innerHTML = `<div>${dia}</div>`;
                    // Eventos do dia
                    if(eventos[dataStr]) {
                        cell.innerHTML += `<span class="event-dot"></span>`;
                        let lista = '<div class="event-list">';
                        eventos[dataStr].forEach(ev => {
                            lista += `<div class="event-item ${ev.tipo}">
                                <span>${ev.titulo}</span>
                                <button class="event-remove" title="Remover" onclick="removerEvento(${ev.id_evento}, event)"><i class="fas fa-trash"></i></button>
                            </div>`;
                        });
                        lista += '</div>';
                        cell.innerHTML += lista;
                    }
                    dia++;
                }
                grid.appendChild(cell);
            }
        }
        function mudarMes(delta) {
            mesAtual += delta;
            if (mesAtual < 1) { mesAtual = 12; anoAtual--; }
            if (mesAtual > 12) { mesAtual = 1; anoAtual++; }
            window.location = `calendario.php?mes=${mesAtual}&ano=${anoAtual}`;
        }
        document.getElementById('formEvento').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('calendario_evento_add.php', {
                method: 'POST',
                body: formData
            }).then(r=>r.json()).then(data=>{
                if(data.success){
                    window.location.reload();
                }else{
                    alert('Erro ao adicionar evento.');
                }
            });
        });
        function removerEvento(id, ev) {
            ev.stopPropagation();
            if(confirm('Remover este evento?')) {
                fetch('calendario_evento_del.php', {
                    method: 'POST',
                    body: new URLSearchParams({id_evento: id})
                }).then(r=>r.json()).then(data=>{
                    if(data.success){
                        window.location.reload();
                    }else{
                        alert('Erro ao remover evento.');
                    }
                });
            }
        }
        renderCalendario();
    </script>
</body>
</html>