<?php
session_start();

// Destruir a sessão
session_unset();
session_destroy();

// Redirecionar para a página inicial
header("Location: visitante/paginaInicio.php");
exit();
?>