<?php
require_once '../../config/conexao.php';
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;
?>

<body>

<div class="container">
<?php
if ((int)$id > 0) {
    try {
        $sql = "SELECT c.id, c.data, c.classe_id, c.oferta_classe, c.total_biblias, c.total_revistas, c.total_visitantes, c.trimestre
                FROM chamadas c
                WHERE c.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $chamada = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($chamada) {
            // Buscar classes disponíveis
            $stmtClasses = $pdo->query("SELECT id, nome FROM classes WHERE id > 0 ORDER BY nome");
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
?>
    <h1 class="mb-4"><i class="fas fa-edit"></i> Editar Chamada</h1>

    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>

    <form id="formEditarChamada" class="row g-3" autocomplete="off">
        <input type="hidden" id="id" value="<?php echo htmlspecialchars($chamada['id']); ?>">

        <div class="col-md-6">
            <label for="data" class="form-label required">Data:</label>
            <input type="text" class="form-control" id="data" value="<?php echo $chamada['data'] ? date('d/m/Y', strtotime($chamada['data'])) : ''; ?>" required>
        </div>

        <div class="col-md-6">
            <label for="classe_id" class="form-label required">Classe:</label>
            <select class="form-select" id="classe_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($classes as $classe): ?>
                    <option value="<?php echo $classe['id']; ?>" <?php echo $chamada['classe_id'] == $classe['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($classe['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="oferta_classe" class="form-label">Oferta da Classe (R$):</label>
            <input type="number" step="0.01" class="form-control" id="oferta_classe" value="<?php echo htmlspecialchars($chamada['oferta_classe']); ?>">
        </div>

        <div class="col-md-6">
            <label for="total_biblias" class="form-label">Total de Bíblias:</label>
            <input type="number" class="form-control" id="total_biblias" value="<?php echo htmlspecialchars($chamada['total_biblias']); ?>">
        </div>

        <div class="col-md-6">
            <label for="total_revistas" class="form-label">Total de Revistas:</label>
            <input type="number" class="form-control" id="total_revistas" value="<?php echo htmlspecialchars($chamada['total_revistas']); ?>">
        </div>

        <div class="col-md-6">
            <label for="total_visitantes" class="form-label">Total de Visitantes:</label>
            <input type="number" class="form-control" id="total_visitantes" value="<?php echo htmlspecialchars($chamada['total_visitantes']); ?>">
        </div>

        <div class="col-md-6">
            <label for="trimestre" class="form-label required">Trimestre:</label>
            <select class="form-select" id="trimestre" required>
                <option value="">Selecione...</option>
                <option value="2026-T1" <?php echo $chamada['trimestre'] == '2026-T1' ? 'selected' : ''; ?>>1º Trimestre 2026</option>
                <option value="2026-T2" <?php echo $chamada['trimestre'] == '2026-T2' ? 'selected' : ''; ?>>2º Trimestre 2026</option>
                <option value="2026-T3" <?php echo $chamada['trimestre'] == '2026-T3' ? 'selected' : ''; ?>>3º Trimestre 2026</option>
                <option value="2026-T4" <?php echo $chamada['trimestre'] == '2026-T4' ? 'selected' : ''; ?>>4º Trimestre 2026</option>
            </select>
        </div>

        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Atualizar
            </button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>

<?php
        } else {
            echo "<div class='alert alert-warning'>Chamada não encontrada.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Erro ao buscar chamada: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>ID inválido.</div>";
}
?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    flatpickr("#data", {
        locale: "pt",
        dateFormat: "d/m/Y",
        allowInput: true
    });

    function formatDateToMySQL(dateStr) {
        const [day, month, year] = dateStr.split('/');
        return `${year}-${month}-${day}`;
    }

    const form = document.getElementById('formEditarChamada');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = {
                id: document.getElementById('id').value,
                data: formatDateToMySQL(document.getElementById('data').value),
                classe_id: document.getElementById('classe_id').value,
                oferta_classe: document.getElementById('oferta_classe').value,
                total_biblias: document.getElementById('total_biblias').value,
                total_revistas: document.getElementById('total_revistas').value,
                total_visitantes: document.getElementById('total_visitantes').value,
                trimestre: document.getElementById('trimestre').value,
                acao: 'atualizar'
            };

            fetch('./chamadas_helper.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Atualizado!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Erro ao atualizar: ' + error.message,
                    icon: 'error'
                });
            });
        });
    }
</script>
<?php
require_once '../../includes/footer.php';
?>