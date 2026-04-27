<?php
require_once '../../config/conexao.php';
require_once '../../models/professorclasse.php';
REQUIRE_ONCE '../../includes/header.php';
$professores = $pdo->query("SELECT * FROM professores")->fetchAll(PDO::FETCH_ASSOC);
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
?>

<body class="container mt-4">
    <h2>Associação de Professores e Classes</h2>

    <form action="/professor_classes/store" method="POST" class="mb-4">
        <div class="row">
            <div class="col">
                <select name="professor_id" class="form-select" required>
                    <option value="">Selecione um Professor</option>
                    <?php foreach ($professores as $professor): ?>
                        <option value="<?= $professor['id'] ?>"><?= htmlspecialchars($professor['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <select name="classe_id" class="form-select" required>
                    <option value="">Selecione uma Classe</option>
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">Associar</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Professor</th>
                <th>Classe</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($professor_classes as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['professor']) ?></td>
                    <td><?= htmlspecialchars($item['classe']) ?></td>
                    <td>
                        <form action="/professor_classes/destroy" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php REQUIRE_ONCE '../../includes/footer.php'; ?>
