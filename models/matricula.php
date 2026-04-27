<?php
require_once '../config/conexao.php';

class Matricula {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listarMatriculas() {
        try {
            $sql = "SELECT m.id, a.nome AS aluno, c.nome AS classe, cg.nome AS congregacao, u.nome AS usuario, 
                           m.data_matricula, m.status, m.trimestre
                    FROM matriculas m
                    JOIN alunos a ON m.aluno_id = a.id
                    JOIN classes c ON m.classe_id = c.id
                    JOIN congregacoes cg ON m.congregacao_id = cg.id
                    LEFT JOIN usuarios u ON m.usuario_id = u.id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar matrículas.");
        }
    }

    public function criarMatricula($data) {
        try {
            if (empty($data['aluno_id']) || empty($data['classe_id']) || empty($data['congregacao_id']) ||
                empty($data['status']) || empty($data['professor_id']) || empty($data['trimestre'])) {
                throw new Exception("Todos os campos obrigatórios devem ser preenchidos.");
            }

            if ($this->verificarMatriculaExistente($data['aluno_id'], $data['classe_id'], $data['congregacao_id'])) {
                throw new Exception("Este aluno já está matriculado nesta classe e congregação.");
            }

            $data_matricula = !empty($data['data_matricula']) ? $data['data_matricula'] : date('Y-m-d');

            if (!strtotime($data_matricula)) {
                throw new Exception("Data de matrícula inválida.");
            }

            $sql = "INSERT INTO matriculas (aluno_id, classe_id, congregacao_id, usuario_id, data_matricula, status, trimestre)
                    VALUES (:aluno_id, :classe_id, :congregacao_id, :usuario_id, :data_matricula, :status, :trimestre)";
    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $data['aluno_id'],
                ':classe_id' => $data['classe_id'],
                ':congregacao_id' => $data['congregacao_id'],
                ':usuario_id' => $data['professor_id'],
                ':data_matricula' => $data_matricula,
                ':status' => $data['status'],
                ':trimestre' => $data['trimestre']
            ]);

            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar matrícula: " . $e->getMessage());
            throw new Exception("Erro ao criar matrícula: " . $e->getMessage());
        }
    }

    public function atualizarMatricula($id, $data) {
        try {
            $sql = "UPDATE matriculas SET 
                        aluno_id = :aluno_id, 
                        classe_id = :classe_id, 
                        congregacao_id = :congregacao_id, 
                        usuario_id = :usuario_id, 
                        trimestre = :trimestre, 
                        status = :status 
                    WHERE id = :id";
                    
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':aluno_id' => $data['aluno_id'],
                ':classe_id' => $data['classe_id'],
                ':congregacao_id' => $data['congregacao_id'],
                ':usuario_id' => $data['professor_id'],
                ':trimestre' => $data['trimestre'],
                ':status' => $data['status']
            ]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar matrícula: " . $e->getMessage());
            throw new Exception("Erro ao atualizar matrícula.");
        }
    }

    public function excluirMatricula($id) {
        try {
            $sql = "DELETE FROM matriculas WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Erro ao excluir matrícula.");
        }
    }

    public function verificarMatriculaExistente($aluno_id, $classe_id, $congregacao_id) {
        $sql = "SELECT COUNT(*) FROM matriculas 
                WHERE aluno_id = :aluno_id 
                  AND classe_id = :classe_id 
                  AND congregacao_id = :congregacao_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':classe_id' => $classe_id,
            ':congregacao_id' => $congregacao_id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function verificarMatriculaExistenteParaExclusao($id) {
        $sql = "SELECT COUNT(*) FROM matriculas WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    public function verificarMatriculaExistenteNoMesmoTrimestre($aluno_id, $trimestre) {
        $sql = "SELECT COUNT(*) FROM matriculas 
                WHERE aluno_id = :aluno_id 
                  AND trimestre = :trimestre
                  AND status != 'inativo'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':trimestre' => $trimestre
        ]);
        return $stmt->fetchColumn() > 0;
    }

    private function verificarMatriculaExistenteParaTrimestre($aluno_id, $classe_id, $congregacao_id, $trimestre) {
        $sql = "SELECT COUNT(*) FROM matriculas 
                WHERE aluno_id = :aluno_id 
                  AND classe_id = :classe_id
                  AND congregacao_id = :congregacao_id
                  AND trimestre = :trimestre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':classe_id' => $classe_id,
            ':congregacao_id' => $congregacao_id,
            ':trimestre' => $trimestre
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function carregarSelects() {
        $sql_alunos = "SELECT id, nome FROM alunos";
        $sql_classes = "SELECT id, nome FROM classes";
        $sql_congregacoes = "SELECT id, nome FROM congregacoes";
        $sql_usuarios = "SELECT id, nome FROM usuarios";

        $stmt_alunos = $this->pdo->prepare($sql_alunos);
        $stmt_classes = $this->pdo->prepare($sql_classes);
        $stmt_congregacoes = $this->pdo->prepare($sql_congregacoes);
        $stmt_usuarios = $this->pdo->prepare($sql_usuarios);

        $stmt_alunos->execute();
        $stmt_classes->execute();
        $stmt_congregacoes->execute();
        $stmt_usuarios->execute();

        return [
            'alunos' => $stmt_alunos->fetchAll(PDO::FETCH_ASSOC),
            'classes' => $stmt_classes->fetchAll(PDO::FETCH_ASSOC),
            'congregacoes' => $stmt_congregacoes->fetchAll(PDO::FETCH_ASSOC),
            'usuarios' => $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function buscarMatriculaPorId($id) {
        try {
            $sql = "SELECT * FROM matriculas WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar matrícula.");
        }
    }

    public function listarMatriculasPorTrimestre($trimestre_atual) {
        $stmt = $this->pdo->prepare("SELECT * FROM matriculas WHERE trimestre = :trimestre_atual");
        $stmt->bindParam(':trimestre_atual', $trimestre_atual);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function migrarMatriculasParaNovoTrimestre($trimestre_atual, $trimestre_novo, $congregacao_id, $manter_status = true) {
        try {
            if ($trimestre_atual === $trimestre_novo) {
                throw new Exception("O trimestre atual e o novo trimestre não podem ser iguais.");
            }

            $sql = "SELECT * FROM matriculas 
                    WHERE trimestre = :trimestre_atual 
                    AND congregacao_id = :congregacao_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':trimestre_atual' => $trimestre_atual,
                ':congregacao_id' => $congregacao_id
            ]);
            $matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($matriculas)) {
                throw new Exception("Nenhuma matrícula encontrada para migração.");
            }

            error_log("Quantidade de matrículas para migrar: " . count($matriculas));

            $this->pdo->beginTransaction();
            $matriculas_migradas = 0;

            foreach ($matriculas as $matricula) {
                if ($this->verificarMatriculaExistenteParaTrimestre(
                    $matricula['aluno_id'],
                    $matricula['classe_id'],
                    $matricula['congregacao_id'],
                    $trimestre_novo
                )) {
                    continue;
                }

                $novo_status = $manter_status ? $matricula['status'] : 'ativo';

                $sql_insert = "INSERT INTO matriculas 
                    (aluno_id, classe_id, congregacao_id, usuario_id, data_matricula, status, trimestre)
                    VALUES (:aluno_id, :classe_id, :congregacao_id, :usuario_id, :data_matricula, :status, :trimestre)";

                $stmt_insert = $this->pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    ':aluno_id' => $matricula['aluno_id'],
                    ':classe_id' => $matricula['classe_id'],
                    ':congregacao_id' => $matricula['congregacao_id'],
                    ':usuario_id' => $matricula['usuario_id'] ?? null,
                    ':data_matricula' => date('Y-m-d'),
                    ':status' => $novo_status,
                    ':trimestre' => $trimestre_novo
                ]);

                $matriculas_migradas++;
            }

            $this->pdo->commit();

            return [
                'sucesso' => true,
                'mensagem' => $matriculas_migradas > 0
                    ? "Foram migradas $matriculas_migradas matrículas para o trimestre $trimestre_novo."
                    : "Nenhuma matrícula nova foi migrada. Todas já existem no trimestre $trimestre_novo."
            ];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Erro ao migrar matrículas: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => "Erro ao migrar matrículas: " . $e->getMessage()];
        }
    }
}
?>








