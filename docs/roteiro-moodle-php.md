# 🎓 Roteiro Completo: Desenvolvimento PHP com Moodle
> Para desenvolvedores PHP que querem dominar o Moodle do zero ao portfólio profissional

---

## Sumário

1. [Setup do Ambiente](#1-setup-do-ambiente)
2. [Arquitetura do Moodle](#2-arquitetura-do-moodle)
3. [Desenvolvimento Prático](#3-desenvolvimento-prático)
4. [Projetos para Portfólio](#4-projetos-para-portfólio)
5. [Boas Práticas](#5-boas-práticas)
6. [Recursos e Referências](#6-recursos-e-referências)

---

## 1. Setup do Ambiente

### 1.1 Baixando o Moodle via Git

O repositório oficial do Moodle está no GitHub. Use o Git para clonar a versão estável mais recente:

```bash
# Clone o repositório oficial
git clone https://github.com/moodle/moodle.git

# Acesse a pasta
cd moodle

# Liste as branches disponíveis (versões estáveis)
git branch -a | grep MOODLE

# Faça checkout da versão estável mais recente (ex: 4.5)
git checkout MOODLE_405_STABLE
```

> **Dica:** Sempre use uma branch `MOODLE_XXX_STABLE` em desenvolvimento. A branch `main` é instável e está em desenvolvimento ativo.

---

### 1.2 Ambiente Local: Windows (XAMPP)

#### Passo a Passo

**1. Instalar o XAMPP**
- Baixe em: https://www.apachefriends.org/
- Instale com Apache, MySQL e PHP 8.2+

**2. Configurar o PHP**

Edite o arquivo `C:\xampp\php\php.ini` e ajuste:

```ini
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 360
date.timezone = America/Sao_Paulo
extension=intl
extension=sodium
extension=zip
```

Reinicie o Apache após as alterações.

**3. Copiar o Moodle**

Coloque o código clonado em `C:\xampp\htdocs\moodle`.

**4. Criar o diretório moodledata**

Crie a pasta **fora** do htdocs (por segurança):
```
C:\moodledata
```

**5. Criar o banco de dados**

Acesse `http://localhost/phpmyadmin` e crie um banco chamado `moodle` com encoding `utf8mb4_unicode_ci`.

**6. Executar o instalador**

Acesse `http://localhost/moodle` no navegador e siga o assistente de instalação.

---

### 1.3 Ambiente Local: Linux (Ubuntu/Debian)

#### Passo a Passo

**1. Instalar dependências**

```bash
sudo apt update
sudo apt install -y apache2 mariadb-server \
  php8.2 php8.2-mysql php8.2-xml php8.2-mbstring \
  php8.2-curl php8.2-zip php8.2-intl php8.2-gd \
  php8.2-soap php8.2-sodium git
```

**2. Configurar o PHP**

```bash
sudo nano /etc/php/8.2/apache2/php.ini
```

Ajuste:
```ini
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 360
date.timezone = America/Sao_Paulo
```

**3. Configurar o Apache**

```bash
sudo nano /etc/apache2/sites-available/moodle.conf
```

```apache
<VirtualHost *:80>
    ServerName moodle.local
    DocumentRoot /var/www/moodle
    <Directory /var/www/moodle>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
sudo a2ensite moodle.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**4. Clonar o Moodle**

```bash
sudo git clone https://github.com/moodle/moodle.git /var/www/moodle
cd /var/www/moodle
sudo git checkout MOODLE_405_STABLE
sudo chown -R www-data:www-data /var/www/moodle
```

**5. Criar moodledata**

```bash
sudo mkdir /var/moodledata
sudo chown www-data:www-data /var/moodledata
sudo chmod 770 /var/moodledata
```

**6. Configurar o MariaDB**

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE moodle DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'moodleuser'@'localhost' IDENTIFIED BY 'sua_senha_aqui';
GRANT ALL PRIVILEGES ON moodle.* TO 'moodleuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**7. Instalar via CLI** (opcional, mais rápido)

```bash
sudo -u www-data php /var/www/moodle/admin/cli/install.php \
  --wwwroot=http://moodle.local \
  --dataroot=/var/moodledata \
  --dbtype=mariadb \
  --dbhost=localhost \
  --dbname=moodle \
  --dbuser=moodleuser \
  --dbpass=sua_senha_aqui \
  --fullname="Moodle Dev" \
  --shortname="moodle" \
  --adminuser=admin \
  --adminpass=Admin@123 \
  --agree-license
```

---

### 1.4 Ambiente com Docker (Recomendado para Dev)

O Docker é a opção mais prática para desenvolvimento: sem conflito de versões, ambiente limpo e reproduzível.

#### docker-compose.yml

Crie um arquivo `docker-compose.yml` em sua pasta de projetos:

```yaml
version: '3.8'

services:
  db:
    image: mariadb:10.11
    container_name: moodle_db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: rootpass
      MYSQL_DATABASE: moodle
      MYSQL_USER: moodle
      MYSQL_PASSWORD: moodle
    volumes:
      - moodle_db_data:/var/lib/mysql
    networks:
      - moodle_net

  moodle:
    image: bitnami/moodle:latest
    container_name: moodle_app
    restart: unless-stopped
    ports:
      - "8080:8080"
      - "8443:8443"
    environment:
      MOODLE_DATABASE_HOST: db
      MOODLE_DATABASE_PORT_NUMBER: 3306
      MOODLE_DATABASE_NAME: moodle
      MOODLE_DATABASE_USER: moodle
      MOODLE_DATABASE_PASSWORD: moodle
      MOODLE_USERNAME: admin
      MOODLE_PASSWORD: Admin@123
      MOODLE_EMAIL: admin@example.com
      MOODLE_SITE_NAME: "Moodle Dev"
      MOODLE_SKIP_BOOTSTRAP: "no"
      PHP_MEMORY_LIMIT: 256M
    volumes:
      - moodle_data:/bitnami/moodle
      - moodle_moodledata:/bitnami/moodledata
      # Monte sua pasta de plugins localmente
      - ./meu_plugin:/bitnami/moodle/local/meu_plugin
    depends_on:
      - db
    networks:
      - moodle_net

volumes:
  moodle_db_data:
  moodle_data:
  moodle_moodledata:

networks:
  moodle_net:
    driver: bridge
```

#### Comandos

```bash
# Subir o ambiente (primeira vez pode demorar ~5 min)
docker-compose up -d

# Verificar os logs
docker-compose logs -f moodle

# Acessar o container
docker exec -it moodle_app bash

# Rodar comandos CLI do Moodle dentro do container
docker exec -it moodle_app php /bitnami/moodle/admin/cli/purge_caches.php

# Parar o ambiente
docker-compose down

# Destruir tudo (inclusive dados)
docker-compose down -v
```

Acesse: `http://localhost:8080` — Login: `admin` / `Admin@123`

> **Dica de workflow:** Monte sua pasta de plugin local no volume Docker (como mostrado acima). Assim você edita no VS Code e as alterações aparecem instantaneamente no Moodle.

---

### 1.5 Configuração do Banco de Dados (MySQL/MariaDB)

O Moodle usa um prefixo de tabelas (padrão `mdl_`). Configurações importantes para performance em dev:

```sql
-- Verificar charset
SHOW CREATE DATABASE moodle;

-- Configurações recomendadas para dev (my.cnf)
[mysqld]
innodb_file_per_table = 1
innodb_file_format = Barracuda
innodb_large_prefix = 1
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

---

## 2. Arquitetura do Moodle

### 2.1 Estrutura de Pastas Principais

```
moodle/
│
├── admin/              # Interface e scripts de administração
│   └── cli/            # Comandos de linha de comando (purge cache, upgrade, etc.)
│
├── auth/               # Plugins de autenticação (LDAP, OAuth, email, etc.)
├── blocks/             # Blocos laterais (calendário, atividade recente, etc.)
├── course/             # Lógica e views de cursos
├── enrol/              # Plugins de matrícula (manual, self, pagamento)
├── grade/              # Sistema de notas e avaliação
│
├── lib/                # ⭐ Biblioteca core do Moodle
│   ├── db/             # Tabelas do core (install.xml)
│   ├── classes/        # Classes PHP do core (autoload PSR-4)
│   ├── outputlib.php   # Sistema de output/renderer
│   ├── moodlelib.php   # Funções utilitárias globais
│   ├── dml/            # Database Abstraction Layer (DBAL)
│   └── form/           # API de formulários (Moodle Forms)
│
├── local/              # ⭐ Seu espaço para plugins locais (não afeta o core)
├── mod/                # Módulos de atividade (forum, quiz, assign, etc.)
├── theme/              # Temas visuais
│
├── config.php          # ⭐ Arquivo de configuração principal (gerado na instalação)
├── version.php         # Versão atual do Moodle core
└── index.php           # Página inicial do site
```

### 2.2 Como Funciona o Sistema de Plugins

O Moodle possui mais de 24 categorias de plugins. As mais importantes para desenvolvimento:

| Tipo | Pasta | Uso |
|------|-------|-----|
| `local` | `/local/seuplugin/` | Funcionalidades genéricas, páginas administrativas, APIs internas |
| `mod` | `/mod/seuplugin/` | Módulos de atividade dentro de cursos |
| `block` | `/blocks/seuplugin/` | Blocos laterais nas páginas |
| `theme` | `/theme/seutema/` | Temas visuais |
| `auth` | `/auth/seuplugin/` | Métodos de autenticação |
| `enrol` | `/enrol/seuplugin/` | Métodos de matrícula |
| `report` | `/report/seuplugin/` | Relatórios administrativos |

**Como o Moodle detecta um plugin:**

1. Escaneia as pastas de plugin conhecidas
2. Lê o arquivo `version.php` de cada subpasta
3. Registra o plugin no banco de dados na tabela `mdl_config_plugins`
4. Chama hooks/callbacks definidos em `lib.php` nas ocasiões corretas

**Arquivo obrigatório: `version.php`**

```php
<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_seuplugin';  // tipo_nome
$plugin->version   = 2025032900;          // YYYYMMDDXX
$plugin->requires  = 2023100900;          // Moodle mínimo (4.3)
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0.0';
```

---

### 2.3 Moodle vs Frameworks Tradicionais (Laravel)

| Conceito | Laravel | Moodle |
|----------|---------|--------|
| **Roteamento** | `routes/web.php` com closures/controllers | Cada arquivo `.php` é uma "rota" (transaction script) |
| **ORM** | Eloquent (Active Record) | DBAL próprio (`$DB->get_records()`, `$DB->insert_record()`) |
| **Templates** | Blade | Mustache + Renderers PHP |
| **Formulários** | Request classes + validação | `moodleform` (classe herdada) |
| **Autenticação** | Guards/Policies | `require_login()` + sistema de Capabilities |
| **Injeção de dependência** | Container IoC | Globals: `$DB`, `$CFG`, `$USER`, `$PAGE`, `$OUTPUT` |
| **Migrations** | `php artisan migrate` | `db/install.xml` + `db/upgrade.php` |
| **Eventos** | Events/Listeners | Moodle Events API + `lib.php` callbacks |
| **Cache** | Redis, Memcached, etc. | MUC (Moodle Universal Cache) |
| **CLI** | `php artisan` | `admin/cli/*.php` |
| **Testes** | PHPUnit + Feature Tests | PHPUnit + Behat |

**Principais diferenças conceituais:**

- **Sem MVC rígido:** O Moodle usa Transaction Script — cada página `.php` é responsável por receber, processar e exibir. A separação existe via Renderers, mas não é imposta.
- **Globals são normais:** `$DB`, `$CFG`, `$USER` são globais injetadas automaticamente. No Laravel você usaria facades ou DI.
- **Capabilities vs Gates:** O controle de acesso usa o sistema de `capabilities` — declarado em `db/access.php` e verificado com `has_capability()`.
- **Strings de internacionalização:** Todo texto visível deve usar `get_string('chave', 'componente')`. Nunca hardcode strings em português/inglês.

---

## 3. Desenvolvimento Prático

### 3.1 Criando um Plugin do Zero (local_hello)

Vamos criar um plugin `local` simples que exibe uma página com uma mensagem personalizada.

#### Estrutura de arquivos

```
local/hello/
├── version.php
├── index.php
├── lang/
│   └── en/
│       └── local_hello.php
└── db/
    └── access.php
```

#### Passo 1: `version.php`

```php
<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_hello';
$plugin->version   = 2025032900;
$plugin->requires  = 2023100900; // Moodle 4.3+
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0.0';
```

#### Passo 2: `lang/en/local_hello.php`

```php
<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']   = 'Hello World Plugin';
$string['welcome']      = 'Bem-vindo ao meu primeiro plugin!';
$string['greeting']     = 'Olá, {$a}!';
$string['hello:view']   = 'Ver a página Hello World';
```

#### Passo 3: `db/access.php`

```php
<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/hello:view' => [
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [
            'guest'   => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];
```

#### Passo 4: `index.php`

```php
<?php
// Sempre incluir o config.php do Moodle
require_once('../../config.php');

// Exigir login (remova se quiser acesso público)
require_login();

// Verificar permissão
require_capability('local/hello:view', context_system::instance());

// Configurar a página
$PAGE->set_url(new moodle_url('/local/hello/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_hello'));
$PAGE->set_heading(get_string('pluginname', 'local_hello'));

// Renderizar a saída
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('welcome', 'local_hello'));

// Saudação personalizada com o nome do usuário logado
$greeting = get_string('greeting', 'local_hello', fullname($USER));
echo html_writer::tag('p', $greeting, ['class' => 'alert alert-info']);

echo $OUTPUT->footer();
```

#### Passo 5: Instalar o plugin

1. Acesse `Administração do site > Notificações`
2. O Moodle detectará o novo plugin e pedirá para instalar
3. Clique em "Atualizar banco de dados"
4. Acesse: `http://seu-moodle/local/hello/index.php`

---

### 3.2 Adicionando Banco de Dados ao Plugin

Para persistir dados, crie `db/install.xml` usando o XMLDB:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<XMLDB PATH="local/hello/db" VERSION="2025032900"
       COMMENT="XMLDB file for local/hello"
       xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:noNamespaceSchemaLocation="../../../lib/xmldb/xmldb.xsd">
  <TABLES>
    <TABLE NAME="local_hello_messages" COMMENT="Mensagens do plugin hello">
      <FIELDS>
        <FIELD NAME="id"        TYPE="int"  LENGTH="10" NOTNULL="true" SEQUENCE="true"/>
        <FIELD NAME="userid"    TYPE="int"  LENGTH="10" NOTNULL="true" DEFAULT="0"/>
        <FIELD NAME="message"   TYPE="text" NOTNULL="true"/>
        <FIELD NAME="timecreated" TYPE="int" LENGTH="10" NOTNULL="true" DEFAULT="0"/>
      </FIELDS>
      <KEYS>
        <KEY NAME="primary" TYPE="primary" FIELDS="id"/>
        <KEY NAME="fk_user" TYPE="foreign" FIELDS="userid" REFTABLE="user" REFFIELDS="id"/>
      </KEYS>
    </TABLE>
  </TABLES>
</XMLDB>
```

**Usando o DBAL para queries:**

```php
global $DB, $USER;

// INSERT
$record = new stdClass();
$record->userid      = $USER->id;
$record->message     = 'Minha mensagem';
$record->timecreated = time();
$id = $DB->insert_record('local_hello_messages', $record);

// SELECT todos os registros do usuário
$records = $DB->get_records('local_hello_messages', ['userid' => $USER->id]);

// SELECT com condições SQL
$records = $DB->get_records_sql(
    "SELECT * FROM {local_hello_messages} WHERE userid = :uid ORDER BY timecreated DESC",
    ['uid' => $USER->id]
);

// UPDATE
$record->id      = $id;
$record->message = 'Mensagem atualizada';
$DB->update_record('local_hello_messages', $record);

// DELETE
$DB->delete_records('local_hello_messages', ['id' => $id]);
```

> **Atenção:** Sempre use `{nome_tabela}` (com chaves) nas queries SQL. O Moodle substitui automaticamente pelo prefixo correto (ex: `mdl_`).

---

### 3.3 Criando um Tema Personalizado

Temas no Moodle são baseados em herança. O recomendado é herdar do tema `boost` (baseado em Bootstrap 4/5).

#### Estrutura mínima

```
theme/meutema/
├── version.php
├── config.php
├── lang/
│   └── en/
│       └── theme_meutema.php
├── templates/
│   └── columns2.mustache    # layout de 2 colunas
├── scss/
│   ├── pre.scss             # variáveis SCSS (cores, fontes)
│   └── post.scss            # CSS adicional
└── pix/
    └── screenshot.png       # screenshot do tema
```

#### `version.php`

```php
<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'theme_meutema';
$plugin->version   = 2025032900;
$plugin->requires  = 2023100900;
$plugin->maturity  = MATURITY_STABLE;
```

#### `config.php`

```php
<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name        = 'meutema';
$THEME->sheets      = [];                   // CSS legado (não usar com SCSS)
$THEME->parents     = ['boost'];            // Herda do Boost
$THEME->enable_dock = false;
$THEME->usefallback = true;
$THEME->scss        = function($theme) {
    return theme_meutema_get_main_scss_content($theme);
};
$THEME->prescsscallback  = 'theme_meutema_get_pre_scss';
$THEME->extrascsscallback = 'theme_meutema_get_extra_scss';
```

#### `lib.php` (funções do tema)

```php
<?php
defined('MOODLE_INTERNAL') || die();

function theme_meutema_get_main_scss_content($theme) {
    global $CFG;
    $scss = '';
    $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/moodle.scss');
    return $scss;
}

function theme_meutema_get_pre_scss($theme) {
    // Variáveis que sobrescrevem o Bootstrap/Boost
    $scss = '';
    $scss .= '$primary: #2563eb;';        // Cor primária
    $scss .= '$secondary: #64748b;';
    $scss .= '$font-family-base: "Inter", sans-serif;';
    return $scss;
}

function theme_meutema_get_extra_scss($theme) {
    // CSS extra adicionado após o SCSS principal
    return file_get_contents(__DIR__ . '/scss/post.scss');
}
```

Ative o tema em: **Administração > Aparência > Temas > Seletor de temas**

---

### 3.4 Consumindo Web Services (API REST do Moodle)

O Moodle possui uma API REST robusta. Para usá-la:

#### Configuração (apenas uma vez)

1. **Habilitar Web Services:** `Admin > Plugins > Web Services > Visão geral`
2. **Habilitar protocolo REST:** `Admin > Plugins > Web Services > Gerenciar protocolos`
3. **Criar token:** `Admin > Plugins > Web Services > Gerenciar tokens`

#### Chamando a API externamente (PHP/cURL)

```php
<?php
$token    = 'seu_token_aqui';
$moodle   = 'https://seu-moodle.com';
$function = 'core_user_get_users_by_field';

$params = [
    'wstoken'                => $token,
    'wsfunction'             => $function,
    'moodlewsrestformat'     => 'json',
    'field'                  => 'email',
    'values[0]'              => 'usuario@exemplo.com',
];

$ch = curl_init("{$moodle}/webservice/rest/server.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
print_r($data);
```

#### Criando uma função de Web Service no seu plugin

**`db/services.php`** — registra o serviço:

```php
<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_hello_get_messages' => [
        'classname'   => 'local_hello\external\get_messages',
        'description' => 'Retorna as mensagens do usuário logado',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
```

**`classes/external/get_messages.php`** — lógica da função:

```php
<?php
namespace local_hello\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class get_messages extends \external_api {

    public static function execute_parameters() {
        return new \external_function_parameters([
            'userid' => new \external_value(PARAM_INT, 'ID do usuário', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute(int $userid = 0): array {
        global $DB, $USER;

        // Validar parâmetros
        $params = self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);

        // Usar o usuário logado se não informado
        if ($params['userid'] === 0) {
            $params['userid'] = $USER->id;
        }

        // Verificar contexto e permissão
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/hello:view', $context);

        // Buscar dados
        $records = $DB->get_records('local_hello_messages', ['userid' => $params['userid']]);

        $messages = [];
        foreach ($records as $record) {
            $messages[] = [
                'id'          => (int) $record->id,
                'message'     => $record->message,
                'timecreated' => (int) $record->timecreated,
            ];
        }

        return $messages;
    }

    public static function execute_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id'          => new \external_value(PARAM_INT, 'ID'),
                'message'     => new \external_value(PARAM_TEXT, 'Mensagem'),
                'timecreated' => new \external_value(PARAM_INT, 'Timestamp'),
            ])
        );
    }
}
```

---

## 4. Projetos para Portfólio

### 4.1 Plugin de Integração com Pix (Matrícula por Pagamento)

**Descrição:** Plugin do tipo `enrol` que integra o Moodle com uma API de pagamento Pix (ex: Mercado Pago, PagSeguro ou EFI Bank). O aluno vê o QR Code, paga, e é matriculado automaticamente via webhook.

**Tecnologias:**
- PHP (Moodle enrol plugin)
- API REST do Mercado Pago / EFI Bank
- Webhooks HTTP
- Moodle Events API
- Cron jobs do Moodle

**O que você aprenderá:**
- Criação de plugin `enrol` do zero
- Integração com APIs externas via cURL
- Receber e validar webhooks
- Usar a API de matrícula do Moodle (`enrol_user()`)
- Criar tarefas agendadas com `\core\task\scheduled_task`

**Nível:** ⭐⭐⭐⭐ Avançado

**Estrutura base do plugin:**
```
enrol/pix/
├── version.php
├── lib.php             # classe enrol_pix_plugin extends enrol_plugin
├── db/
│   ├── install.xml     # tabelas: pagamentos, transações
│   └── access.php
├── classes/
│   ├── task/
│   │   └── check_payments.php    # tarefa cron
│   └── external/
│       └── webhook_handler.php   # endpoint do webhook
└── webhook.php         # recebe callback do gateway
```

---

### 4.2 Sistema de Certificados Personalizados

**Descrição:** Plugin que gera certificados PDF personalizados ao concluir um curso, com nome do aluno, data, nota, assinatura digital e QR Code de validação. O certificado pode ser verificado por terceiros via URL pública.

**Tecnologias:**
- PHP + TCPDF ou mPDF para geração de PDF
- Moodle Events API (evento de conclusão de curso)
- QR Code (phpqrcode ou biblioteca similar)
- Moodle File API para armazenar PDFs

**O que você aprenderá:**
- Escutar eventos do Moodle (`\core\event\course_completed`)
- Usar a File API do Moodle para upload/download seguro
- Gerar PDF programaticamente
- Criar páginas públicas de verificação
- Sistema de templates de certificado (admin configura o layout)

**Nível:** ⭐⭐⭐ Intermediário-Avançado

**Estrutura base:**
```
local/certificados/
├── version.php
├── lib.php
├── classes/
│   ├── observer.php           # escuta eventos
│   ├── certificate_manager.php
│   └── pdf_generator.php
├── db/
│   ├── events.php             # registra os observers
│   ├── install.xml
│   └── access.php
├── templates/
│   └── certificate.mustache   # layout do certificado HTML
├── verify.php                 # página pública de verificação
└── download.php               # download do PDF
```

---

### 4.3 Dashboard Customizado para Alunos

**Descrição:** Um bloco ou plugin `local` que substitui o dashboard padrão do Moodle por uma interface moderna e informativa. Exibe progresso nos cursos, próximas atividades, notas recentes, tempo de estudo e gamificação básica (pontos/badges).

**Tecnologias:**
- PHP (Moodle block ou local plugin)
- Mustache templates
- JavaScript + AMD modules (RequireJS do Moodle)
- SCSS personalizado
- Web Services (AJAX interno do Moodle)

**O que você aprenderá:**
- Criar blocos Moodle (`block_`)
- Usar AMD/JavaScript no Moodle corretamente
- Fazer requisições AJAX usando `core/ajax` do Moodle
- Usar a Completion API para progresso de cursos
- Mustache templates com helpers do Moodle
- Trabalhar com a Grades API

**Nível:** ⭐⭐⭐ Intermediário

**Estrutura base:**
```
blocks/meu_dashboard/
├── version.php
├── block_meu_dashboard.php    # classe principal do bloco
├── classes/
│   └── external/
│       └── get_dashboard_data.php  # Web Service AJAX
├── db/
│   ├── services.php
│   └── access.php
├── amd/
│   └── src/
│       └── dashboard.js       # JavaScript AMD
├── templates/
│   └── dashboard.mustache
└── scss/
    └── dashboard.scss
```

---

### 4.4 Integração com API Externa (Zoom/Google Meet)

**Descrição:** Plugin `mod` (módulo de atividade) que integra o Moodle com o Zoom ou Google Meet. O professor cria uma aula ao vivo diretamente pelo Moodle, alunos entram pelo link gerado automaticamente, e as gravações ficam disponíveis no curso.

**Tecnologias:**
- PHP (Moodle mod plugin)
- API REST do Zoom (OAuth 2.0) ou Google Meet API
- Moodle Calendar API
- Moodle Gradebook API

**O que você aprenderá:**
- Criar um módulo de atividade (`mod_`) do zero (estrutura mais complexa)
- Integrar com OAuth 2.0 usando a `\core\oauth2` API do Moodle
- Usar a Calendar API para criar eventos
- Webhooks para sincronizar presença/gravações
- Backup e restore do módulo

**Nível:** ⭐⭐⭐⭐⭐ Expert

**Estrutura base:**
```
mod/videoaula/
├── version.php
├── mod_form.php               # formulário de criação da atividade
├── lib.php                    # callbacks obrigatórios do mod
├── view.php                   # página principal da atividade
├── index.php                  # lista todas as instâncias no curso
├── classes/
│   ├── zoom_client.php        # cliente HTTP para a API do Zoom
│   └── external/
├── db/
│   ├── install.xml
│   ├── access.php
│   └── services.php
└── backup/
    └── moodle2/               # suporte a backup/restore
```

---

## 5. Boas Práticas

### 5.1 Organização de Código

**Nomenclatura de componentes**

O nome do componente SEMPRE segue o padrão `tipo_nome`:
```
local_meuapp        ✅
local_MeuApp        ❌ (nunca maiúscula)
local_meu-app       ❌ (nunca hífen)
meuapp              ❌ (sem o tipo)
```

**Autoloading de classes**

Use namespaces PSR-4 dentro da pasta `classes/`:
```php
// Arquivo: local/meuapp/classes/utils/formatter.php
namespace local_meuapp\utils;

class formatter {
    public static function format_date(int $timestamp): string {
        return userdate($timestamp); // use funções do Moodle
    }
}

// Uso (sem require):
$formatted = \local_meuapp\utils\formatter::format_date(time());
```

**Sempre use as APIs do Moodle, nunca reinvente:**

```php
// ❌ ERRADO: query SQL direta
mysqli_query($conn, "SELECT * FROM mdl_user WHERE id = $id");

// ✅ CORRETO: DBAL do Moodle
$user = $DB->get_record('user', ['id' => $id]);

// ❌ ERRADO: file_get_contents para arquivos
$content = file_get_contents('/path/to/file');

// ✅ CORRETO: File API do Moodle
$fs = get_file_storage();
$file = $fs->get_file($contextid, 'local_meuapp', 'filearea', $itemid, '/', 'filename.pdf');
```

---

### 5.2 O Que Evitar (Erros Comuns)

**1. SQL Injection**
```php
// ❌ NUNCA concatenar variáveis em SQL
$DB->get_records_sql("SELECT * FROM {user} WHERE email = '$email'");

// ✅ SEMPRE usar named params
$DB->get_records_sql("SELECT * FROM {user} WHERE email = :email", ['email' => $email]);
```

**2. XSS (Cross-Site Scripting)**
```php
// ❌ NUNCA imprimir input do usuário diretamente
echo $_POST['mensagem'];

// ✅ SEMPRE sanitizar e escapar
$mensagem = required_param('mensagem', PARAM_TEXT);
echo s($mensagem); // função s() do Moodle faz htmlspecialchars
```

**3. Não verificar permissões**
```php
// ❌ NUNCA confiar que o usuário tem acesso só porque chegou na página
echo $conteudo_restrito;

// ✅ SEMPRE verificar antes
require_login();
require_capability('local/meuapp:view', $context);
```

**4. Hardcode de strings visíveis**
```php
// ❌ ERRADO: string hardcoded
echo '<h2>Bem-vindo!</h2>';

// ✅ CORRETO: string de linguagem
echo $OUTPUT->heading(get_string('welcome', 'local_meuapp'));
```

**5. Modificar arquivos do core**
```
// ❌ NUNCA edite arquivos dentro de:
/lib/, /mod/forum/, /admin/, etc.

// ✅ Use hooks, callbacks e plugins locais para extensão
```

**6. Esquecer de limpar cache**

Após qualquer mudança em `version.php`, `db/access.php`, `lang/`, ou arquivos AMD:
```bash
php admin/cli/purge_caches.php
```

---

### 5.3 Estratégias para Estudar o Core do Moodle

**1. Use o XMLDB Editor**

`Admin > Desenvolvimento > Editor XMLDB` — visualiza toda a estrutura do banco de dados com documentação gerada automaticamente.

**2. Habilite o modo debug**

No `config.php` do seu ambiente de desenvolvimento:
```php
@error_reporting(E_ALL | E_STRICT);
@ini_set('display_errors', '1');
$CFG->debug = 32767;       // DEBUG_DEVELOPER
$CFG->debugdisplay = 1;
$CFG->debugstringids = 1;  // Mostra chave das strings de idioma
$CFG->perfdebug = 15;      // Info de performance
$CFG->debugpageinfo = 1;   // Info da página no rodapé
```

**3. Explore plugins existentes como referência**

Os melhores plugins para estudar (bem escritos e mantidos pelo core):
- `mod/forum` — módulo de atividade completo
- `block/recent_activity` — bloco simples
- `local/mobile` — uso avançado de web services

**4. Use o PHPXref**

Navegue pelo código fonte com referências cruzadas:
`https://phpdoc.moodledev.io/`

**5. DevDocs oficial**

`https://moodledev.io` — documentação atualizada para desenvolvedores

**6. Ferramentas de desenvolvimento**

| Ferramenta | Uso |
|------------|-----|
| VS Code + PHP Intelephense | Autocompletar, goto definition |
| Moodle Code Checker plugin | Valida coding style (PSR-2 modificado) |
| Xdebug | Debugging passo a passo |
| Moodle PHPUnit | Testes automatizados |
| Behat | Testes de comportamento (E2E) |

**7. Leia o changelog entre versões**

Antes de fazer upgrade do seu ambiente de dev, leia:
`https://moodledev.io/docs/apis/changelog`

---

## 6. Recursos e Referências

### Documentação Oficial

- **Developer Docs:** https://moodledev.io
- **Repositório GitHub:** https://github.com/moodle/moodle
- **Plugins Directory:** https://moodle.org/plugins
- **Fórum de Developers:** https://moodle.org/mod/forum/view.php?id=55
- **Tracker de Bugs:** https://tracker.moodle.org

### Cursos Gratuitos

- **Moodle Plugin Development Basics MOOC** — https://moodle.com/news/learn-moodle-plugin-development/
- **Introduction to Moodle Programming** — disponível no moodle.org

### Padrões de Código

- **Coding Style:** https://moodledev.io/general/development/policies/codingstyle
- **PHP versions support:** https://moodledev.io/general/development/policies/php

---

## Mapa de Progressão Sugerida

```
SEMANA 1-2:  Setup + Arquitetura
  └── Ambiente rodando ✓
  └── Explorar estrutura de pastas ✓
  └── Ler o código de um plugin simples (ex: block/html) ✓

SEMANA 3-4:  Primeiro Plugin
  └── Plugin local_hello funcionando ✓
  └── Adicionar banco de dados ✓
  └── Adicionar formulário (moodleform) ✓

SEMANA 5-6:  Web Services e AJAX
  └── Configurar e testar a API REST ✓
  └── Criar uma função external ✓
  └── Fazer requisição AJAX do JavaScript ✓

SEMANA 7-8:  Tema Personalizado
  └── Tema herdando do Boost ✓
  └── Customizar cores e tipografia ✓
  └── Criar um template Mustache customizado ✓

SEMANA 9-12: Projeto de Portfólio
  └── Escolher um dos 4 projetos sugeridos
  └── Implementar com boas práticas
  └── Publicar no GitHub com README detalhado

SEMANA 13+:  Especialização
  └── Estudo de testes (PHPUnit + Behat)
  └── Performance e caching (MUC)
  └── Contribuição para o core ou plugins open source
```

---

> **Versão do roteiro:** 1.0 — Março 2025  
> **Compatível com:** Moodle 4.3+ / PHP 8.2+ / MariaDB 10.6+
