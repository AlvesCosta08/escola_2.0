<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { // Ajuste conforme sua sessão
    header("Location: ../index.php");
    exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>