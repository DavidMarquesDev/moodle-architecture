# Roteiro Manual de QA — Plataforma Moodle

## 1. Objetivo

Este roteiro descreve como subir o ambiente, liberar acesso para testes e executar validações manuais da plataforma para QA.

Escopo de validação:

- Core Moodle rodando via Docker.
- Plugin `blocks/meu_dashboard`.
- Plugin `mod/videoaula`.
- Fluxos de instalação, upgrade, permissões e smoke test funcional.

## 2. Pré-requisitos

- Docker Desktop instalado e em execução.
- Git instalado.
- Porta `8080` livre na máquina local.
- Repositórios clonados lado a lado:

```text
Laravel/
├── moodle-core/
└── moodle-architecture/
```

## 3. Subir a infraestrutura (Docker)

No Windows (PowerShell), execute:

```powershell
cd "D:\Desenvolvimento\David Estudo\Laravel\moodle-architecture"
docker compose up -d
```

Verificações:

```powershell
docker compose ps
docker compose logs --tail=100 moodle
docker compose logs --tail=100 db
```

Critério de sucesso:

- Container `moodle_app` em estado `Up`.
- Container `moodle_db` em estado `Up`.
- Sem erro de conexão com banco nos logs do Moodle.

## 4. Primeiro acesso e instalação do Moodle

1. Abra `http://localhost:8080`.
2. Conclua o instalador.
3. Use os dados do banco:
   - Host: `db`
   - Database: `moodle`
   - User: `moodle`
   - Password: `moodle`
   - Porta: `3306`
4. Finalize a criação do usuário administrador.

## 5. Atualização de plugins após alterações

Sempre que houver mudança em código, rode:

```powershell
cd "D:\Desenvolvimento\David Estudo\Laravel\moodle-architecture"
docker compose exec moodle php admin/cli/upgrade.php --non-interactive
docker compose exec moodle php admin/cli/purge_caches.php
```

Critério de sucesso:

- Upgrade sem exceções.
- Cache limpo sem erro.

## 6. Como liberar servidor para QA externa

### Opção A (rede interna/VPN)

- Compartilhe `http://IP_DA_SUA_MAQUINA:8080`.
- Garanta firewall liberado para porta `8080`.

### Opção B (internet com túnel)

Exemplo com ngrok:

```powershell
ngrok http 8080
```

Compartilhe a URL HTTPS gerada pelo ngrok com a equipe QA.

## 7. Massa mínima de teste

Antes de iniciar os testes:

1. Criar 1 curso de teste.
2. Criar 1 usuário professor e 1 usuário aluno.
3. Matricular ambos no curso.
4. Garantir que o bloco `Meu Dashboard` esteja adicionado à página.
5. Criar ao menos 1 atividade `Videoaula`.

## 8. Roteiro de testes manuais — `block_meu_dashboard`

### Cenário 1 — Renderização do bloco

1. Logar como aluno.
2. Abrir painel “Meu Moodle” ou página com o bloco.
3. Validar se o bloco carrega sem erro visual.

Resultado esperado:

- Bloco exibido.
- Sem erro JS no console.

### Cenário 2 — Coleta de dados AJAX

1. Abrir DevTools > Network.
2. Recarregar página.
3. Verificar chamadas AJAX para serviços:
   - `block_meu_dashboard_get_dashboard_data`
   - `block_meu_dashboard_get_recent_messages`
   - `block_meu_dashboard_get_messages_series`

Resultado esperado:

- Respostas `200`.
- Payload válido sem erro de capability/contexto.

### Cenário 3 — Usuário não autenticado

1. Abrir página do bloco sem login.

Resultado esperado:

- Mensagem de login obrigatório.
- Nenhum erro fatal.

## 9. Roteiro de testes manuais — `mod_videoaula`

### Cenário 1 — Criação de atividade

1. Logar como professor com permissão de edição.
2. Entrar no curso e adicionar atividade `Videoaula`.
3. Salvar com dados válidos.

Resultado esperado:

- Atividade criada.
- Acesso por `view.php` sem erro.

### Cenário 2 — Criar reunião

1. Abrir a atividade criada.
2. Clicar no botão “Criar reunião”.

Resultado esperado:

- Exibição do ID de reunião.
- Exibição do link de entrada (`joinurl`).

### Cenário 3 — Controle de permissão

1. Logar como aluno.
2. Abrir a mesma atividade.
3. Tentar executar ação de criação de reunião.

Resultado esperado:

- Aluno consegue visualizar atividade.
- Aluno não consegue executar ação de gerenciamento.

## 10. Teste de backup/restore (`mod_videoaula`)

1. No curso, executar backup incluindo atividade `Videoaula`.
2. Restaurar em novo curso.
3. Abrir atividade restaurada.

Resultado esperado:

- Atividade restaurada sem erro.
- Dados principais preservados.

## 11. Smoke test final de homologação

Executar checklist:

- [ ] Login admin, professor e aluno.
- [ ] Dashboard renderizando e respondendo chamadas AJAX.
- [ ] Criação e visualização de `Videoaula`.
- [ ] Criação de reunião (perfil com permissão).
- [ ] Backup e restore com `Videoaula`.
- [ ] Sem erro crítico em logs do container Moodle.

Comando de inspeção de logs:

```powershell
cd "D:\Desenvolvimento\David Estudo\Laravel\moodle-architecture"
docker compose logs --tail=200 moodle
```

## 12. Encerramento de ambiente

Parar containers:

```powershell
cd "D:\Desenvolvimento\David Estudo\Laravel\moodle-architecture"
docker compose down
```

Parar e remover volumes (reset completo de dados):

```powershell
cd "D:\Desenvolvimento\David Estudo\Laravel\moodle-architecture"
docker compose down -v
```

## Autor

- David Marques — <github.com/DavidMarquesDev>
