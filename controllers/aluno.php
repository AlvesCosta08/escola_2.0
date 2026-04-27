<?php
// Define o caminho base absoluto
define('BASE_PATH', dirname(__DIR__));

// Inclui os arquivos necessários com caminhos absolutos
require_once BASE_PATH . '/config/conexao.php';
require_once BASE_PATH . '/models/aluno.php';
require_once BASE_PATH . '/models/classe.php';

// Habilita logs para debug
error_log("=== ALUNO CONTROLLER ACESSADO ===");
error_log("Ação: " . ($_GET['acao'] ?? 'nenhuma'));

header('Content-Type: application/json');

// Verifica se a conexão foi estabelecida
if (!isset($pdo) || !$pdo) {
    error_log("ERRO: Conexão com banco de dados não estabelecida");
    echo json_encode(["status" => "error", "message" => "Erro de conexão com o banco de dados"]);
    exit;
}

$aluno = new Aluno($pdo);
$classe = new Classe($pdo);

// Função para validar os dados
function validarDados($dados) {
    if (empty($dados['nome'])) {
        return "O campo nome é obrigatório.";
    }
    if (empty($dados['data_nascimento'])) {
        return "O campo data de nascimento é obrigatório.";
    }
    if (empty($dados['telefone'])) {
        return "O campo telefone é obrigatório.";
    }
    if (empty($dados['classe_id'])) {
        return "O campo classe é obrigatório.";
    }
    return null;
}

$acao = $_GET['acao'] ?? '';

try {
    switch ($acao) {
        case 'listar':
            error_log("Executando ação: listar");
            $lista = $aluno->listar();
            echo json_encode(["status" => "success", "data" => $lista]);
            break;

        case 'buscar':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            error_log("Executando ação: buscar - ID: " . $id);
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "ID inválido"]);
                break;
            }

            $resultado = $aluno->buscar($id);
            echo json_encode($resultado);
            break;

        case 'salvar':
            error_log("Executando ação: salvar");
            error_log("Dados recebidos: " . print_r($_POST, true));
            
            // Remove ID se existir
            unset($_POST['id']);
        
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'data_nascimento' => $_POST['data_nascimento'] ?? '',
                'telefone' => $_POST['telefone'] ?? '',
                'classe_id' => $_POST['classe_id'] ?? ''
            ];
        
            // Valida os dados
            $erro = validarDados($dados);  
            if ($erro) {
                echo json_encode(["status" => "error", "message" => $erro]);
                break;
            }
        
            $resultado = $aluno->salvar($dados);
            echo json_encode($resultado);
            break;
        
        case 'editar':
            error_log("Executando ação: editar");
            error_log("Dados recebidos: " . print_r($_POST, true));
            
            $id = $_POST['id'] ?? null;
        
            if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
                echo json_encode(["status" => "error", "message" => "ID inválido"]);
                break;
            }
        
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'data_nascimento' => $_POST['data_nascimento'] ?? '',
                'telefone' => $_POST['telefone'] ?? '',
                'classe_id' => $_POST['classe_id'] ?? ''
            ];
        
            // Valida os dados
            $erro = validarDados($dados);
            if ($erro) {
                echo json_encode(["status" => "error", "message" => $erro]);
                break;
            }
        
            $resultado = $aluno->editar($id, $dados);
            echo json_encode($resultado);
            break;

        case 'excluir':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            error_log("Executando ação: excluir - ID: " . $id);
            
            if (!$id) {
                echo json_encode(["status" => "error", "message" => "ID inválido"]);
                break;
            }

            echo json_encode($aluno->excluir($id));
            break;

        default:
            error_log("Ação inválida: " . $acao);
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Ação inválida"]);
            break;
    }
} catch (Exception $e) {
    error_log("ERRO no controller: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Erro interno: " . $e->getMessage()]);
}
?>