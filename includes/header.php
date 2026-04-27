<?php
// Define o caminho base absoluto
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/sistemas/escola');

// Inclui a conexão com caminho absoluto (se necessário)
// require_once BASE_PATH . '/config/conexao.php';

// Inicia sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema E.B.D</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- SortableJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/biblia.png" type="image/x-icon">
    
    <!-- CSS Personalizados -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/aluno.css">
    
    <style>
        body {
            padding-top: 80px; /* Ajuste esse valor conforme a altura da sua navbar */
        }
        
        /* Estilo adicional para garantir que o dropdown funcione */
        .navbar-nav .dropdown-menu {
            position: absolute;
        }
        
        /* Ajuste para links ativos */
        .nav-link.active {
            font-weight: bold;
            color: #0056b3 !important;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>/views/dashboard.php">
                <img src="<?php echo BASE_URL; ?>/assets/images/biblia.png" alt="Logo EBD" style="height: 40px; margin-right: 10px;">
                <span class="fw-bold">Escola Bíblica</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>/views/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'alunos') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>/views/alunos/index.php">
                            <i class="fas fa-users"></i> Alunos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'classes') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>/views/classes/index.php">
                            <i class="fas fa-chalkboard"></i> Classes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'professores') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>/views/professores/index.php">
                            <i class="fas fa-chalkboard-teacher"></i> Professores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'congregacao') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>/views/congregacao/index.php">
                            <i class="fas fa-church"></i> Congregações
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'matriculas') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>/views/matriculas/index.php">
                            <i class="fas fa-book-open"></i> Matrículas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'usuario') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>/views/usuario/index.php">
                            <i class="fas fa-user-cog"></i> Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'relatorios') !== false ? 'active' : ''; ?>" 
                           href="<?php echo BASE_URL; ?>/views/relatorios/index.php">
                            <i class="fas fa-chart-bar"></i> Relatórios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout.php" onclick="return confirm('Deseja realmente sair?')">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Início do conteúdo principal -->
    <main class="container mt-4">