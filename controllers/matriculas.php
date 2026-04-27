<?php
require_once '../config/conexao.php';
require_once '../models/matricula.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

class MatriculaController {
    private $model;

    public function __construct($pdo) {
        $this->model = new Matricula($pdo);
    }

    // Listar todas as matrículas
    public function listarMatriculas() {
        try {
            $matriculas = $this->model->listarMatriculas();
            echo json_encode(['sucesso' => true, 'dados' => $matriculas]);
        } catch (Exception $e) {
            error_log("Erro no listarMatriculas (Controller): " . $e->getMessage());
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar matrículas.']);
        }
    }

    // Criar uma nova matrícula
    public function criarMatricula($data) {
        if (empty($data['aluno_id']) || empty($data['classe_id']) || 
            empty($data['congregacao_id']) || empty($data['status']) || 
            empty($data['professor_id']) || empty($data['trimestre'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Todos os campos obrigatórios devem ser preenchidos.']);
            return;
        }

        // Verificar se o aluno já está matriculado em qualquer classe no mesmo trimestre
        if ($this->model->verificarMatriculaExistenteNoMesmoTrimestre($data['aluno_id'], $data['trimestre'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Este aluno já está matriculado em outra classe neste trimestre.']);
            return;
        }

        // Verificar se a data da matrícula é válida
        if (strtotime($data['data_matricula']) === false) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Data de matrícula inválida.']);
            return;
        }

        try {
            $this->model->criarMatricula($data);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula criada com sucesso.']);
        } catch (Exception $e) {
            error_log("Erro ao criar matrícula (Controller): " . $e->getMessage());
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
    }

    // Buscar uma matrícula específica
    public function buscarMatricula($id) {
        if (!is_numeric($id) || empty($id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.']);
            return;
        }

        try {
            $matricula = $this->model->buscarMatriculaPorId($id);
            if ($matricula) {
                echo json_encode(['sucesso' => true, 'dados' => $matricula]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Matrícula não encontrada.']);
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar matrícula (Controller): " . $e->getMessage());
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar matrícula.']);
        }
    }

    // Atualizar uma matrícula
    public function atualizarMatricula($id, $data) {
        if (empty($data['aluno_id']) || empty($data['classe_id']) || 
           empty($data['congregacao_id']) || empty($data['professor_id']) || 
           empty($data['trimestre']) || empty($data['status'])) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Todos os campos obrigatórios devem ser preenchidos.']);
            return;
        }

        try {
            // Verificar se a matrícula existe
            $matriculaExistente = $this->model->buscarMatriculaPorId($id);
            if (!$matriculaExistente) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Matrícula não encontrada.']);
                return;
            }

            // Verificar se a alteração criaria duplicidade
            if ($matriculaExistente['aluno_id'] != $data['aluno_id'] || 
                $matriculaExistente['trimestre'] != $data['trimestre']) {
                if ($this->model->verificarMatriculaExistenteNoMesmoTrimestre($data['aluno_id'], $data['trimestre'])) {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Este aluno já está matriculado em outra classe neste trimestre.']);
                    return;
                }
            }

            // Atualizar a matrícula
            $resultado = $this->model->atualizarMatricula($id, $data);
            
            if ($resultado) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula atualizada com sucesso.']);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar matrícula.']);
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar matrícula (Controller): " . $e->getMessage());
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar matrícula: ' . $e->getMessage()]);
        }
    }

    // Excluir uma matrícula
    public function excluirMatricula($id) {
        if (!is_numeric($id) || empty($id)) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido.']);
            return;
        }

        try {
            // Verificar se a matrícula existe
            $this->model->verificarMatriculaExistenteParaExclusao($id);

            // Excluir matrícula
            $this->model->excluirMatricula($id);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Matrícula excluída com sucesso.']);
        } catch (Exception $e) {
            error_log("Erro ao excluir matrícula (Controller): " . $e->getMessage());
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir matrícula.']);
        }
    }

    // Carregar selects para o formulário (alunos, classes, congregações, usuários)
    public function carregarSelects() {
        try {
            $dados = $this->model->carregarSelects();
            echo json_encode(['sucesso' => true, 'dados' => $dados]);
        } catch (Exception $e) {
            error_log("Erro no carregarSelects (Controller): " . $e->getMessage());
            echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao carregar dados dos selects.']);
        }
    }

    // Método para migrar matrículas para o próximo trimestre
    public function migrarMatriculas($trimestre_atual, $trimestre_novo, $congregacao_id, $manter_status = true) {
            try {
                // Validação dos trimestres
                if (empty($trimestre_atual) || empty($trimestre_novo)) {
                    throw new Exception("Trimestres atual e novo são obrigatórios.");
                }
                
                if ($trimestre_atual === $trimestre_novo) {
                    throw new Exception("O novo trimestre deve ser diferente do trimestre atual.");
                }
                
                // Chama o model para migrar as matrículas
                $resultado = $this->model->migrarMatriculasParaNovoTrimestre(
                    $trimestre_atual, 
                    $trimestre_novo, 
                    $congregacao_id, 
                    $manter_status
                );
                
                echo json_encode($resultado);
            } catch (Exception $e) {
                error_log("Erro no migrarMatriculas (Controller): " . $e->getMessage());
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
    }

    // Verifica a ação na requisição e chama o método adequado
    if (isset($_GET['acao'])) {
        $controller = new MatriculaController($pdo);

        switch ($_GET['acao']) {
            case 'listarMatriculas':
                $controller->listarMatriculas();
                break;
            case 'criarMatricula':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents("php://input"), true);
                    $controller->criarMatricula($data);
                }
                break;
            case 'atualizarMatricula':
                if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                    $data = json_decode(file_get_contents("php://input"), true);
                    $id = $_GET['id'];
                    $controller->atualizarMatricula($id, $data);
                }
                break;
            case 'excluirMatricula':
                $id = $_GET['id'];
                $controller->excluirMatricula($id);
                break;
            case 'carregarSelects':
                $controller->carregarSelects();
                break;
            case 'buscarMatricula':
                $id = $_GET['id'];
                $controller->buscarMatricula($id);
                break; 
            case 'migrarMatriculas':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents("php://input"), true);
                    
                    if (!isset($data['trimestre_atual']) || !isset($data['novo_trimestre']) || !isset($data['congregacao_id'])) {
                        echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos para migração.']);
                        break;
                    }
                    
                    $controller->migrarMatriculas(
                        $data['trimestre_atual'],
                        $data['novo_trimestre'],
                        $data['congregacao_id'],
                        $data['manter_status'] ?? true
                    );
                }
                break;           
            default:
                echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
                break;
        }
    }




