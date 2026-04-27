<?php
session_start();
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/'); // Remove o cookie de sessão

header("Location: ../index.php");
exit;
?>


