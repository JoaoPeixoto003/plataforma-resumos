<?php
// filepath: c:\xampp\htdocs\Projeto\index.php
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>QuickNote - Bem-vindo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #4361ee 0%, #4895ef 100%);
            min-height: 100vh;
            margin: 0;
            color: #212529;
            display: flex;
            flex-direction: column;
        }
        .container {
            max-width: 500px;
            margin: 100px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.10);
            padding: 2.5rem 2rem 2rem 2rem;
            text-align: center;
        }
        .logo {
            font-size: 2.2rem;
            font-weight: 700;
            color: #4361ee;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }
        .subtitle {
            color: #3f37c9;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        .presentation-text {
            font-size: 1.1rem;
            color: #444;
            margin-bottom: 2.5rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: #4361ee;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.9rem 2.2rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(67,97,238,0.08);
        }
        .btn:hover {
            background: #3a56d4;
        }
        @media (max-width: 600px) {
            .container {
                margin: 40px 10px 0 10px;
                padding: 1.5rem 0.7rem 1.5rem 0.7rem;
            }
            .logo { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-bolt"></i> QuickNote
        </div>
        <div class="subtitle">
            Bem-vindo à sua plataforma de organização e partilha de conteúdos!
        </div>
        <div class="presentation-text">
            O <b>QuickNote</b> é o seu espaço para guardar, organizar e encontrar apontamentos, resumos, exercícios e outros recursos de estudo.<br><br>
            Faça login ou explore como visitante para conhecer as funcionalidades da plataforma.
        </div>
        <a href="paginas/visitante/paginaInicio.php" class="btn">
            <i class="fas fa-sign-in-alt"></i> Entrar como Visitante
        </a>
    </div>
</body>
</html>