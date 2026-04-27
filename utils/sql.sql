

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL,
  `telefone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `classe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
CREATE TABLE `aniversariantes_mes` (
`id` int(11)
,`nome` varchar(100)
,`data_nascimento` date
,`telefone` varchar(15)
,`classe_id` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamadas`
--

CREATE TABLE `chamadas` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `classe_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `oferta_classe` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_biblias` int(10) NOT NULL,
  `total_revistas` int(10) NOT NULL,
  `total_visitantes` int(10) NOT NULL,
  `trimestre` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1=1º Trimestre, 2=2º Trimestre, 3=3º Trimestre, 4=4º Trimestre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `congregacoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(255) NOT NULL,
  `tabela_afetada` varchar(100) NOT NULL,
  `registro_id` int(11) NOT NULL,
  `data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `congregacao_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_matricula` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('ativo','concluido','cancelado') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ativo',
  `trimestre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `permissoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL COMMENT 'Ex: gerenciar_alunos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `presencas` (
  `id` int(11) NOT NULL,
  `chamada_id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `presente` enum('presente','ausente','justificado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




CREATE TABLE `professores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `congregacao_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `professores_classes` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `relatorio_consolidado` (
`congregacao_id` int(11)
,`congregacao_nome` varchar(255)
,`classe_id` int(11)
,`classe_nome` varchar(100)
,`trimestre` int(11)
,`data_inicio` date
,`data_fim` date
,`total_alunos_matriculados` bigint(21)
,`total_presentes` bigint(21)
,`total_ausentes` bigint(21)
,`total_justificados` bigint(21)
,`total_biblias` decimal(32,0)
,`total_revistas` decimal(32,0)
,`total_visitantes` decimal(32,0)
,`total_ofertas_distintas` bigint(21)
,`ofertas` text
);

CREATE TABLE `relatorio_trimestre_congregacao` (
`classe_nome` varchar(100)
,`congregacao_nome` varchar(255)
,`trimestre` int(11)
,`total_biblias` decimal(32,0)
,`total_revistas` decimal(32,0)
,`total_visitantes` decimal(32,0)
,`total_ofertas` decimal(32,2)
);


CREATE TABLE `resumo_presenca` (
`aluno_id` int(11)
,`aluno_nome` varchar(100)
,`classe_id` int(11)
,`classe_nome` varchar(100)
,`congregacao_id` int(11)
,`congregacao_nome` varchar(255)
,`trimestre` int(11)
,`total_presentes` bigint(21)
,`total_ausentes` bigint(21)
);


CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','user','professor') NOT NULL,
  `congregacao_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `usuario_permissoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `permissao_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;