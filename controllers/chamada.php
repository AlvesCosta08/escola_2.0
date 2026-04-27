<?php
require_once '../models/chamada.php';
require_once '../config/conexao.php';

//header('Content-Type: application/json');

// Criar a instância do objeto Chamada
$chamada = new Chamada($pdo);

function sendErrorResponse($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// Verificar se a requisição foi feita via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Método inválido. Apenas POST é permitido.');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$acao = $input['acao'] ?? '';
if (empty($acao)) {
    sendErrorResponse('Ação não especificada.');
}

if (!$pdo) {
    sendErrorResponse('Erro na conexão com o banco de dados.');
}

try {
    switch ($acao) {
        case 'getCongregacoes':
            $congregacoes = $chamada->getCongregacoes();
            echo json_encode(['status' => 'success', 'data' => $congregacoes ?: []]);
            break;

        case 'getClassesByCongregacao':
            $congregacao_id = $input['congregacao_id'] ?? 0;
            if (!$congregacao_id) {
                sendErrorResponse('ID da congregação inválido.');
            }
            $classes = $chamada->getClassesByCongregacao($congregacao_id);
            echo json_encode(['status' => 'success', 'data' => $classes ?: []]);
            break;

        case 'getAlunosByClasse':
            $classe_id = $input['classe_id'] ?? 0;
            $congregacao_id = $input['congregacao_id'] ?? 0;
            $trimestre = $input['trimestre'] ?? null;

            if (!$classe_id || !$congregacao_id || !$trimestre) {
                sendErrorResponse('IDs da classe, congregação ou trimestre inválidos.');
            }

            // Converte o trimestre para o formato do banco (ex: 2 -> '2026-T2')
            $ano_atual = date('Y');
            $trimestre_formatado = $ano_atual . '-T' . $trimestre;

            $alunos = $chamada->getAlunosByClasse($classe_id, $congregacao_id, $trimestre_formatado);
            
            echo json_encode(['status' => 'success', 'data' => ['data' => $alunos ?: []]]);
            break;

        case 'salvarChamada':
            if (!isset($input['data'], $input['classe'], $input['professor'], $input['alunos'], $input['trimestre'])) {
                sendErrorResponse('Dados incompletos para salvar chamada. Todos os campos são obrigatórios.');
            }
        
            // Validação do trimestre
            $trimestre = $input['trimestre'] ?? '';
            if (!in_array($trimestre, ['1', '2', '3', '4', 1, 2, 3, 4])) {
                sendErrorResponse('Trimestre inválido. Deve ser entre 1 e 4');
            }
        
            // Converte o trimestre para o formato do banco (ex: 1 -> '2026-T1')
            $ano_atual = date('Y');
            $trimestre_formatado = $ano_atual . '-T' . $trimestre;
        
            $resultado = $chamada->registrarChamada(
                $input['data'],
                $trimestre_formatado,  // Envia o trimestre no formato correto
                $input['classe'],
                $input['professor'],
                $input['alunos'],                  
                $input['oferta_classe'] ?? 0,
                $input['total_visitantes'] ?? 0,
                $input['total_biblias'] ?? 0,
                $input['total_revistas'] ?? 0
            );
        
            if ($resultado['sucesso']) {
                echo json_encode(['status' => 'success', 'message' => 'Chamada registrada com sucesso.']);
            } else {
                sendErrorResponse($resultado['mensagem']);
            }
            break;

        default:
            sendErrorResponse('Ação inválida.');
    }
} catch (Exception $e) {
    sendErrorResponse('Erro interno: ' . $e->getMessage());
}
?>