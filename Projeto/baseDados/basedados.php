<?php
$serverName = "sql111.infinityfree.com";
$username = "if0_39194191";
$password = "UeAznmRDico5rwR";
$database = "if0_39194191_projeto_db";

$conn = mysqli_connect($serverName, $username, $password, $database);
mysqli_set_charset($conn, "utf8mb4");

if (!$conn) {
    die("Erro na ligação à base de dados: " . mysqli_connect_error());
}

?>
