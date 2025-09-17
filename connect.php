<?php
$servername = "localhost"; // o la ip correespondiente
$username = "root";
$password = "123456789";
$dbname = "sigesa_db";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>