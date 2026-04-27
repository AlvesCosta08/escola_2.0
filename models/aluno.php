<?php
class Aluno {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function listar() {
        try {
            $stmt = $this->db->prepare("
                SELECT a.id, a.nome, a.data_nascimento, a.telefone, c.nome AS classe 
                FROM alunos a 
                JOIN classes c ON a.classe_id = c.id 
                ORDER BY a.nome ASC
            ");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Alunos listados: " . count($result));
            return $result;
        } catch (PDOException $e) {
            error_log("Erro ao listar alunos: " . $e->getMessage());
            return [];
        }
    }

    public function buscar($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, c.nome AS classe 
                FROM alunos a 
                JOIN classes c ON a.classe_id = c.id 
                WHERE a.id = :id
            ");
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Buscar aluno ID $id: " . ($aluno ? "Encontrado" : "Não encontrado"));
            
            if ($aluno) {
                return [
                    "status" => "success",
                    "data" => $aluno
                ];
            } else {
                return [
                    "status" => "error",
                    "message" => "Aluno não encontrado"
                ];
            }
        } catch (PDOException $e) {
            error_log("Erro ao buscar aluno: " . $e->getMessage());
            return [
                "status" => "error",
                "message" => "Erro ao buscar aluno: " . $e->getMessage()
            ];
        }
    }

    public function salvar($dados) {
        try {
            $nome = trim(htmlspecialchars($dados['nome'], ENT_QUOTES, 'UTF-8'));
            $data_nascimento = $dados['data_nascimento'];
            $telefone = trim(htmlspecialchars($dados['telefone'], ENT_QUOTES, 'UTF-8'));
            $classe_id = filter_var($dados['classe_id'], FILTER_VALIDATE_INT);
    
            if (!$nome || !$data_nascimento || !$telefone || !$classe_id) {
                error_log("Dados inválidos para salvar: " . json_encode($dados));
                return ["status" => "error", "message" => "Dados inválidos"];
            }
    
            error_log("Salvando aluno: " . json_encode($dados));
    
            $stmt = $this->db->prepare("
                INSERT INTO alunos (nome, data_nascimento, telefone, classe_id) 
                VALUES (:nome, :data_nascimento, :telefone, :classe_id)
            ");
            $stmt->execute([
                ':nome' => $nome,
                ':data_nascimento' => $data_nascimento,
                ':telefone' => $telefone,
                ':classe_id' => $classe_id
            ]);
    
            error_log("Aluno salvo com sucesso. ID: " . $this->db->lastInsertId());
            return ["status" => "success", "message" => "Aluno cadastrado com sucesso"];
        } catch (PDOException $e) {
            error_log("Erro ao salvar aluno: " . $e->getMessage());
            return ["status" => "error", "message" => "Erro ao salvar aluno: " . $e->getMessage()];
        }
    }

    public function editar($id, $dados) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            $nome = trim(htmlspecialchars($dados['nome'], ENT_QUOTES, 'UTF-8'));
            $data_nascimento = $dados['data_nascimento'];
            $telefone = trim(htmlspecialchars($dados['telefone'], ENT_QUOTES, 'UTF-8'));
            $classe_id = filter_var($dados['classe_id'], FILTER_VALIDATE_INT);
    
            if (!$id || !$nome || !$data_nascimento || !$telefone || !$classe_id) {
                error_log("Dados inválidos para editar: " . json_encode($dados));
                return ["status" => "error", "message" => "Dados inválidos"];
            }
    
            error_log("Editando aluno ID: $id - Dados: " . json_encode($dados));
    
            $stmt = $this->db->prepare("
                UPDATE alunos 
                SET nome = :nome, data_nascimento = :data_nascimento, telefone = :telefone, classe_id = :classe_id
                WHERE id = :id
            ");
            $stmt->execute([
                ':id' => $id,
                ':nome' => $nome,
                ':data_nascimento' => $data_nascimento,
                ':telefone' => $telefone,
                ':classe_id' => $classe_id
            ]);
    
            error_log("Aluno atualizado com sucesso. Linhas afetadas: " . $stmt->rowCount());
            return ["status" => "success", "message" => "Aluno atualizado com sucesso"];
        } catch (PDOException $e) {
            error_log("Erro ao editar aluno: " . $e->getMessage());
            return ["status" => "error", "message" => "Erro ao editar aluno: " . $e->getMessage()];
        }
    }

    public function excluir($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM alunos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            error_log("Aluno ID $id excluído com sucesso. Linhas afetadas: " . $stmt->rowCount());
            return ["status" => "success", "message" => "Aluno excluído com sucesso"];
        } catch (PDOException $e) {
            error_log("Erro ao excluir aluno: " . $e->getMessage());
            return ["status" => "error", "message" => "Erro ao excluir aluno: " . $e->getMessage()];
        }
    }
}
?>