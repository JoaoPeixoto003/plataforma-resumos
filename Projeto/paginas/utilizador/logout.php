<?php
// Iniciar a sessão
session_start();

// Apagar todas as variáveis de sessão
session_unset();

// Destruir a sessão
session_destroy();

// Redirecionar para a página inicial
header("Location: ../visitante/paginaInicio.php");
exit();
