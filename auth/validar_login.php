<?php
session_start();
require_once "../config/conexao.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se a requisição é POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Erro: Requisição inválida.");
}

// Verifica se os dados foram enviados corretamente
if (empty($_POST['email']) || empty($_POST['senha'])) {
    $_SESSION['mensagem'] = "Preencha todos os campos.";
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email']);
$senha = trim($_POST['senha']);

try {
    // Verifica se a conexão com o banco está ativa
    if (!isset($pdo)) {
        die("Erro na conexão com o banco de dados!");
    }

    // Verifica se o usuário existe no banco
    $sql = "SELECT id, nome, email, senha, perfil FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if (password_verify($senha, $usuario['senha'])) {
            if (in_array($usuario['perfil'], ["admin", "professor"])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'] ?? 'Usuário'; // Evita erro caso 'nome' não exista
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];
                header("Location: ../views/dashboard.php");
                exit();
            } else {
                $_SESSION['mensagem'] = "Acesso restrito. Apenas administradores e professores podem acessar.";
            }
        } else {
            $_SESSION['mensagem'] = "Senha incorreta.";
        }
    } else {
        $_SESSION['mensagem'] = "Usuário não encontrado.";
    }
} catch (PDOException $e) {
    $_SESSION['mensagem'] = "Erro no banco de dados: " . $e->getMessage();
}

// Redireciona para a página de login se houver erro
header("Location: login.php");
exit();
?>



















