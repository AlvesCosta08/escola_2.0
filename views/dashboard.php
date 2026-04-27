<?php  
require_once '../functions/funcoes_chamadas.php';
require_once '../includes/header.php';

$estatisticas = obterEstatisticasChamadasMensais($pdo);
?>

<body>
<!-- Hero -->
<div class="hero">
  <div class="container">
    <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_perfil']); ?>!</h1>
    <p>Gerencie a Escola Bíblica Dominical com praticidade e organização.</p>
  </div>
</div>

<!-- Carrossel de Avisos -->
<div id="carouselAvisos" class="carousel slide bg-light py-4" data-bs-ride="carousel">
  <div class="carousel-inner container">
    <div class="carousel-item active">
      <div class="alert alert-info text-center shadow">📢 Sisitema em fase de testes.Versão Beta atualmente sendo executada!</div>
    </div> 
    <div class="carousel-item">
      <div class="alert alert-success text-center shadow">✅ Novo material disponível na seção de relatórios.</div>
    </div>
    <div class="carousel-item">
      <div class="alert alert-warning text-center shadow">⚠️ Registre as chamadas até domingo à noite!</div>
    </div>
  </div>
</div>

<!-- Cards -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Painel Rápido</h2>
    <div class="row g-4">

      <!-- Chamadas -->
      <div class="col-md-4" data-aos="fade-up">
        <div class="card p-4 text-center">
          <i class="fas fa-book fa-2x mb-3 text-primary"></i>
          <h5>Chamadas</h5>
          <p>Registre a frequência das turmas e mantenha o histórico.</p>
          <a href="../views/chamadas/index.php" class="btn btn-primary mt-2">Nova Chamada</a>
          <a href="../views/chamadas/listar.php" class="btn btn-warning mt-2">Editar Chamada</a>
          <a href="../views/presencas/index.php" class="btn btn-info mt-2">Corrigir Presenças</a>
          <a href="./sorteios.php" class="btn btn-primary mt-2">
            <i class="fas fa-dice"></i> Sorteios
          </a>
        </div>
      </div>

      <!-- Perfil -->
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card p-4 text-center profile-info">
          <i class="fas fa-user-circle fa-2x mb-3 text-secondary"></i>
          <h5>Seu Perfil</h5>
          <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['usuario_email']); ?></p>
          <p><strong>Função:</strong> <?= htmlspecialchars($_SESSION['usuario_perfil']); ?></p>
        </div>
      </div>

      <!-- Últimas Chamadas -->
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card p-4">
          <h5 class="text-center mb-3">Últimas Chamadas</h5>
          <?php exibirUltimasChamadasPorClasse($pdo); ?>
        </div>
      </div>

    </div>
  </div>
  <script src="../views/chamadas/js/chamadas.js"></script>
</section>
  AOS.init();
</script>
<?php require_once '../includes/footer.php'; ?>