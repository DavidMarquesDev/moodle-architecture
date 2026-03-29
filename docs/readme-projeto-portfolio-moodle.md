# Projeto de Portfólio Moodle: `block_meu_dashboard` e `mod_videoaula`

## Visão geral

Este projeto implementa dois plugins Moodle orientados a portfólio técnico:

- `blocks/meu_dashboard`: dashboard customizado para alunos com indicadores e séries de mensagens.
- `mod/videoaula`: módulo de atividade com integração de reunião (Zoom), serviços externos e suporte a backup/restore.

O objetivo é demonstrar prática real com APIs do Moodle, estrutura de plugins, integração externa e padrões de qualidade de código.

## Escopo implementado

### 1) Plugin `block_meu_dashboard`

- Classe principal do bloco com renderização de conteúdo e integração frontend.
- Web services externos:
  - `get_dashboard_data`
  - `get_recent_messages`
  - `get_messages_series`
- Camadas internas separadas por responsabilidade:
  - Factory
  - Service
  - Repository + Interface
- Frontend com:
  - AMD module (`dashboard.js`)
  - Template Mustache (`dashboard.mustache`)
  - SCSS (`dashboard.scss`)
- Capacidades e serviços declarados em `db/access.php` e `db/services.php`.

### 2) Plugin `mod_videoaula`

- Estrutura principal do módulo:
  - `mod_form.php`
  - `lib.php`
  - `index.php`
  - `view.php`
- Serviços externos:
  - `create_meeting`
  - `get_activity_data`
- Camadas internas:
  - Integração (`zoom_client`)
  - Serviço de domínio/aplicação (`meeting_service`)
- Definições de permissões e serviços:
  - `db/access.php`
  - `db/services.php`
- Persistência com schema em `db/install.xml`.
- Suporte completo de backup/restore:
  - `backup/moodle2/backup_videoaula_activity_task.class.php`
  - `backup/moodle2/backup_videoaula_stepslib.php`
  - `backup/moodle2/restore_videoaula_activity_task.class.php`
  - `backup/moodle2/restore_videoaula_stepslib.php`

## Qualidade e validações aplicadas

- `declare(strict_types=1)` em todos os novos arquivos PHP.
- Tipagem explícita de retorno.
- Validação de sintaxe com `php -l` nos arquivos alterados.
- Ajustes de compatibilidade para análise estática no bloco:
  - `local/_ide_stubs/block_base.php`.

## Como executar localmente

1. Configure o Moodle local apontando para o repositório `moodle-core`.
2. Acesse o ambiente como administrador.
3. Conclua o fluxo de upgrade do Moodle para instalar/atualizar os plugins.
4. Verifique:
   - Bloco `Meu Dashboard` disponível para inclusão.
   - Atividade `Videoaula` disponível no curso.
5. Execute cenários:
   - Consulta de dados no dashboard.
   - Criação de reunião via serviço externo.
   - Backup e restore de curso com instância de `videoaula`.

## Versionamento recomendado

### Repositório `moodle-core`

- Commits focados por domínio:
  - `feat(block_meu_dashboard): ...`
  - `feat(mod_videoaula): ...`
  - `fix(intelephense): ...`

### Repositório `moodle-architecture`

- Commit de documentação:
  - `docs(portfolio): atualizar roteiro e README técnico`

## Checklist de publicação

- [x] Implementação dos dois plugins principais.
- [x] Web services registrados e funcionando.
- [x] Estrutura de backup/restore no `mod_videoaula`.
- [x] Documentação do roteiro atualizada.
- [x] README técnico de portfólio criado.
- [ ] Publicação do histórico final no repositório remoto.

## Autor

- David Marques — <github.com/DavidMarquesDev>
