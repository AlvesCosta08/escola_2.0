<?php
require_once '../functions/funcoes_chamadas.php';
require_once '../config/conexao.php';

/**
 * Obtém estatísticas mensais das chamadas com filtro por trimestre
 */
function obterEstatisticasChamadasMensais($pdo, $trimestre = null) {
    $estatisticas = [
        'total_chamadas_mes' => 0,
        'ultima_chamada' => null,
        'trimestre_atual' => null,
        'erro' => null
    ];
    
    try {
        // Obter o mês e ano atual
        $mesAtual = date('m');
        $anoAtual = date('Y');
        
        // Determinar trimestre atual se não for fornecido
        if ($trimestre === null) {
            $trimestre = ceil($mesAtual / 3);
        }
        $estatisticas['trimestre_atual'] = $trimestre;
        
        // Consulta para contar as chamadas do mês corrente com trimestre
        $sqlTotal = "SELECT COUNT(*) as total 
                     FROM chamadas 
                     WHERE MONTH(data) = :mes 
                     AND YEAR(data) = :ano
                     AND trimestre = :trimestre";
        
        $stmtTotal = $pdo->prepare($sqlTotal);
        $stmtTotal->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmtTotal->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmtTotal->bindParam(':trimestre', $trimestre, PDO::PARAM_INT);
        $stmtTotal->execute();
        
        $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
        $estatisticas['total_chamadas_mes'] = $resultTotal['total'];
        
        // Consulta para obter a última chamada do mês com trimestre
        $sqlUltima = "SELECT c.*, 
                             cl.nome as classe_nome, 
                             u.nome as professor_nome,
                             c.trimestre as trimestre_chamada
                      FROM chamadas c
                      JOIN classes cl ON c.classe_id = cl.id
                      JOIN usuarios u ON c.professor_id = u.id
                      WHERE MONTH(c.data) = :mes 
                      AND YEAR(c.data) = :ano
                      AND c.trimestre = :trimestre
                      ORDER BY c.data DESC, c.criado_em DESC
                      LIMIT 1";
        
        $stmtUltima = $pdo->prepare($sqlUltima);
        $stmtUltima->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmtUltima->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmtUltima->bindParam(':trimestre', $trimestre, PDO::PARAM_INT);
        $stmtUltima->execute();
        
        $estatisticas['ultima_chamada'] = $stmtUltima->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $estatisticas['erro'] = "Erro ao obter estatísticas: " . $e->getMessage();
        error_log($estatisticas['erro']);
    }
    
    return $estatisticas;
}

/**
 * Obtém as últimas chamadas do mês agrupadas por classe com trimestre
 */
function obterUltimasChamadasPorClasse($pdo, $trimestre = null) {
    $chamadasPorClasse = [];
    
    try {
        // Obter o mês e ano atual
        $mesAtual = date('m');
        $anoAtual = date('Y');
        
        // Determinar trimestre atual se não for fornecido
        if ($trimestre === null) {
            $trimestre = ceil($mesAtual / 3);
        }
        
        // Consulta para obter a última chamada de cada classe no mês com trimestre
        $sql = "SELECT c.*, 
                       cl.nome as classe_nome, 
                       cl.id as classe_id,
                       u.nome as professor_nome,
                       c.trimestre as trimestre_chamada,
                       MAX(c.data) as ultima_data
                FROM chamadas c
                JOIN classes cl ON c.classe_id = cl.id
                JOIN usuarios u ON c.professor_id = u.id
                WHERE MONTH(c.data) = :mes 
                AND YEAR(c.data) = :ano
                AND c.trimestre = :trimestre
                GROUP BY c.classe_id
                ORDER BY cl.nome ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mes', $mesAtual, PDO::PARAM_INT);
        $stmt->bindParam(':ano', $anoAtual, PDO::PARAM_INT);
        $stmt->bindParam(':trimestre', $trimestre, PDO::PARAM_INT);
        $stmt->execute();
        
        $chamadasPorClasse = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erro ao obter chamadas por classe: " . $e->getMessage());
        return ['erro' => "Erro ao obter chamadas por classe: " . $e->getMessage()];
    }
    
    return $chamadasPorClasse;
}

/**
 * Exibe as últimas chamadas por classe em formato HTML incluindo o trimestre
 */
function exibirUltimasChamadasPorClasse($pdo, $trimestre = null) {
    $chamadas = obterUltimasChamadasPorClasse($pdo, $trimestre);
    
    if (isset($chamadas['erro'])) {
        echo "<div class='alert alert-danger'>{$chamadas['erro']}</div>";
        return;
    }
    
    if (empty($chamadas)) {
        echo "<div class='alert alert-info'>Nenhuma chamada registrada este mês.</div>";
        return;
    }
    
    // Obter trimestre atual para exibição no título
    $mesAtual = date('m');
    $trimestreAtual = $trimestre ?? ceil($mesAtual / 3);
    
    echo "<h4>Chamadas do Trimestre {$trimestreAtual}</h4>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped table-hover'>";
    echo "<thead class='thead-dark'>";
    echo "<tr>";
    echo "<th>Classe</th>";   
    echo "<th>Trimestre</th>";
    echo "<th>Última Chamada</th>";
    echo "<th>Bíblias</th>";
    echo "<th>Revistas</th>";
    echo "<th>Visitantes</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    foreach ($chamadas as $chamada) {
        echo "<tr>";
        echo "<td>{$chamada['classe_nome']}</td>";       
        echo "<td>{$chamada['trimestre_chamada']}</td>";
        echo "<td>" . formatarDataBrasil($chamada['data']) . "</td>";
        echo "<td>{$chamada['total_biblias']}</td>";
        echo "<td>{$chamada['total_revistas']}</td>";
        echo "<td>{$chamada['total_visitantes']}</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
}

/**
 * Formata data no padrão brasileiro
 */
function formatarDataBrasil($data) {
    return $data ? date('d/m/Y', strtotime($data)) : 'N/A';
}