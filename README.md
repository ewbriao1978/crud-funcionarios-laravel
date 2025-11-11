# Instruções para uso do Laravel. 

## como criar do ZERO 
### Atualizar repositórios
sudo apt update && sudo apt upgrade -y

### Instalar PHP e extensões necessárias
sudo apt install php php-cli php-fpm php-json php-common php-mysql php-zip php-gd php-mbstring php-curl php-xml php-bcmath php-json php-tokenizer php-ctype php-fileinfo -y

### Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

### Verificar instalações
php --version
composer --version


#### Objetivo: Configurar ambiente PHP com todas as dependências necessárias para o Laravel.

## Criar Projeto Laravel
### Criar projeto com Laravel 12
composer create-project laravel/laravel crud-funcionarios
cd crud-funcionarios

### Verificar versão do Laravel
php artisan --version

#### Objetivo: Inicializar uma nova aplicação Laravel com a estrutura padrão.


# CONFIGURAÇÃO DO BANCO DE DADOS

## Configurar MySQL e Arquivo .env

# Acessar MySQL (execute como usuário com privilégios)
sudo mysql -u root -p

# No MySQL, executar:
CREATE DATABASE mydb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

#### ou use localhost/phpmyadmin para criar um banco de dados "mydb" ou qualquer outro nome


### Edite o arquivo .env
APP_NAME=CRUD_Funcionarios
APP_ENV=local
APP_KEY=base64:... (já deve estar preenchido)
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mydb
DB_USERNAME=root
DB_PASSWORD=mysqluser //caso esteja no ifrs

#### Objetivo: Configurar conexão com banco de dados MySQL.


## MIGRATION DA TABELA FUNCIONARIOS
4. Criar e Configurar Migration
bash
## Criar migration para tabela funcionarios
php artisan make:migration create_funcionarios_table

Edite o arquivo database/migrations/xxxx_xx_xx_xxxxxx_create_funcionarios_table.php:

php
```
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id(); // Primary key auto-increment
            $table->string('nome', 255); // Nome completo
            $table->decimal('salario', 10, 2); // Salário com 2 casas decimais
            $table->string('email', 255)->unique(); // Email único
            $table->timestamps(); // created_at e updated_at
            
            // Índices para performance (DICA PRODUÇÃO)
            $table->index('email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};

``` 
#### Objetivo: Definir estrutura da tabela com tipos de dados apropriados e índices.

## Executar Migration

##  Executar migration para criar tabela
php artisan migrate

## Verificar status
php artisan migrate:status

#### Objetivo: Criar fisicamente a tabela no banco de dados.

## MODEL FUNCIONARIO
### Criar Model com Eloquent

# Criar model Funcionario
php artisan make:model Funcionario

Edite o arquivo app/Models/Funcionario.php:

php
```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'funcionarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'salario',
        'email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'salario' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required|string|max:255',
        'salario' => 'required|numeric|min:0',
        'email' => 'required|email|unique:funcionarios,email',
    ];
}

``` 
#### Objetivo: Criar model para operações de banco e definir regras de validação.

### CONTROLLER API RESOURCE
### Criar Controller com Resource Methods

#### Criar controller resource para API
php artisan make:controller FuncionarioController --api

Edite o arquivo app/Http/Controllers/FuncionarioController.php:

php
```
<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FuncionarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $funcionarios = Funcionario::select('id', 'nome', 'salario', 'email', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $funcionarios,
                'count' => $funcionarios->count()
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar funcionários',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'salario' => 'required|numeric|min:0|max:9999999.99',
                'email' => 'required|email|unique:funcionarios,email'
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'salario.required' => 'O campo salário é obrigatório.',
                'salario.numeric' => 'O salário deve ser um valor numérico.',
                'salario.min' => 'O salário não pode ser negativo.',
                'email.required' => 'O campo email é obrigatório.',
                'email.email' => 'Informe um email válido.',
                'email.unique' => 'Este email já está cadastrado.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $funcionario = Funcionario::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Funcionário criado com sucesso',
                'data' => $funcionario
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar funcionário',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $funcionario = Funcionario::find($id);

            if (!$funcionario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $funcionario
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar funcionário',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $funcionario = Funcionario::find($id);

            if (!$funcionario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nome' => 'sometimes|string|max:255',
                'salario' => 'sometimes|numeric|min:0|max:9999999.99',
                'email' => 'sometimes|email|unique:funcionarios,email,' . $id
            ], [
                'salario.numeric' => 'O salário deve ser um valor numérico.',
                'salario.min' => 'O salário não pode ser negativo.',
                'email.email' => 'Informe um email válido.',
                'email.unique' => 'Este email já está cadastrado.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $funcionario->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Funcionário atualizado com sucesso',
                'data' => $funcionario
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar funcionário',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $funcionario = Funcionario::find($id);

            if (!$funcionario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            $funcionario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Funcionário excluído com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir funcionário',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
```

#### Objetivo: Implementar endpoints API com tratamento de erros e validações.

## CONFIGURAÇÃO DAS ROTAS API
## Configurar Rotas API
**CRIE** o arquivo routes/api.php:

php
``` 
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FuncionarioController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('funcionarios', [FuncionarioController::class, 'index']);
Route::post('funcionarios', [FuncionarioController::class, 'store']);
Route::put('funcionarios/{id}', [FuncionarioController::class, 'update']);
Route::delete('funcionarios/{id}', [FuncionarioController::class, 'destroy']);

```
#### Objetivo: Definir endpoints da API com rota de health check e fallback.


### CSRF Error - IMPORTANTE!!
Neste caso, a api.php com as novas rotas não poderão ser usadas, pois o arquivo web.php sempre será lido e este tem CSRF. 

é necessário editar o arquivo bootstrap/app.php
```
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',

// INCLUIR A LINHA ABAIXO
         
        api: __DIR__.'/../routes/api.php',


// TUDO IGUAL DAQUI PARA BAIXO

        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware ...
```


##TESTANDO A APLICAÇÃO
##Executar Servidor de Desenvolvimento

# Iniciar servidor Laravel
php artisan serve --host=0.0.0.0 --port=8000

# Em outro terminal, testar health check
curl http://localhost:8000/api/health
Objetivo: Iniciar aplicação e verificar se está respondendo.

TESTES COM CURL (EXEMPLOS PRÁTICOS)
10. Testar Endpoints da API
bash
# 1. LISTAR funcionários (GET)
curl -X GET http://localhost:8000/api/funcionarios \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"

# 2. CRIAR funcionário (POST)
curl -X POST http://localhost:8000/api/funcionarios \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nome": "João Silva",
    "salario": 3500.50,
    "email": "joao.silva@empresa.com"
  }'

# 3. BUSCAR funcionário específico (GET)
curl -X GET http://localhost:8000/api/funcionarios/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"

# 4. ATUALIZAR funcionário (PUT)
curl -X PUT http://localhost:8000/api/funcionarios/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nome": "João Silva Santos",
    "salario": 3800.00
  }'

# 5. EXCLUIR funcionário (DELETE)
curl -X DELETE http://localhost:8000/api/funcionarios/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json"
DICAS PARA PRODUÇÃO
11. Comandos e Configurações Adicionais
bash
# Otimizar aplicação para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Criar migration para índices adicionais (performance)
php artisan make:migration add_indexes_to_funcionarios_table

# Rollback e re-executar migrations
php artisan migrate:rollback
php artisan migrate

# Ver rotas disponíveis
php artisan route:list --path=api


ESTRUTURA FINAL DO PROJETO
text
```
crud-funcionarios/
├── app/
│   ├── Models/
│   │   └── Funcionario.php
│   └── Http/Controllers/
│       └── FuncionarioController.php
├── database/
│   └── migrations/
│       └── xxxx_xx_xx_xxxxxx_create_funcionarios_table.php
├── routes/
│   └── api.php
├── .env
└── composer.json
```

```
RESUMO DOS ENDPOINTS DISPONÍVEIS
Método	URL	Ação
GET	/api/health	Status da API
GET	/api/funcionarios	Listar todos
POST	/api/funcionarios	Criar novo
GET	/api/funcionarios/{id}	Buscar por ID
PUT	/api/funcionarios/{id}	Atualizar
DELETE	/api/funcionarios/{id}	Excluir
````




