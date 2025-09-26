<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Genera scaffolding DDD/Modules para una entidad dentro de un módulo de nwidart.
 *
 * Uso:
 *  php artisan module:make-crud Parcels Parcel --schema="name:string,location:string?nullable,area_m2:decimal(10,2),user_id:foreign(users)?nullable,latitude:decimal(10,7)?nullable,longitude:decimal(10,7)?nullable,crop_type:string?nullable,active:boolean"
 *
 * Tipos soportados:
 *  string, text, int/integer, bigint, decimal(p,s), float, double, boolean/bool,
 *  timestamp, timestamptz (alias datetimetz), enum(val1|val2|...),
 *  foreign(tabla)
 *
 * Flags soportadas por campo:
 *  ?nullable (o 'nullable'), ?unique (o 'unique'), ?index (o 'index'), =default
 *
 * Opciones:
 *  --schema=   Definición de campos (tipo DSL simple)
 *  --prefix=   Prefijo de ruta (default: api)
 *  --force     Sobrescribir archivos existentes
 */
class MakeModuleCrudCommand extends Command
{
    protected $signature = 'module:make-crud 
        {module : Nombre del módulo (Studly), ej: Parcels} 
        {entity : Nombre de la entidad (Studly), ej: Parcel}
        {--schema= : "name:string,active:boolean,..."}
        {--prefix=api : Prefijo de rutas (se aplicará dentro del archivo routes/api.php del módulo)}
        {--force : Sobrescribe archivos si existen}';

    protected $description = 'Genera scaffolding CRUD DDD dentro de un módulo (nwidart/laravel-modules)';

    public function handle(): int
    {
        $module = Str::studly($this->argument('module'));
        $entity = Str::studly($this->argument('entity'));
        $schema = (string)($this->option('schema') ?? '');
        $prefix = trim($this->option('prefix') ?? 'api');
        $force  = (bool)$this->option('force');

        $basePath = base_path("Modules/{$module}");
        if (!File::exists($basePath)) {
            $this->error(" El módulo {$module} no existe. Crea primero: php artisan module:make {$module}");
            return self::FAILURE;
        }

        // Derivados
        $entityLower = Str::snake($entity);               // parcel_item
        $entityCamel = Str::camel($entity);               // parcelItem
        $entityKebab = Str::kebab($entity);               // parcel-item
        $entityPluralStudly = Str::pluralStudly($entity); // Parcels
        $entityPluralKebab  = Str::kebab($entityPluralStudly); // parcels
        $entityPluralSnake  = Str::snake($entityPluralStudly); // parcels

        // Carpetas
        $paths = [
            "Domain/Entities",
            "Domain/Interfaces",
            "Application/Contracts",
            "Application",
            "Infrastructure/Persistence",
            "Infrastructure/Repositories",
            "Http/DTOs",
            "Http/Requests",
            "Http/Controllers/Api",
            "Database/Migrations",
            "Providers",
            "routes",
        ];
        foreach ($paths as $p) File::ensureDirectoryExists("$basePath/$p");

        // Parsear schema
        $fields = $this->parseSchema($schema); // array con keys: name,type,nullable,args,default,foreign,unique,index

        // ===== Escribir archivos =====
        $this->writeEntity($basePath, $module, $entity, $fields, $force);
        $this->writeDomainInterface($basePath, $module, $entity, $force);
        $this->writeEloquent($basePath, $module, $entity, $fields, $force);
        $this->writeRepository($basePath, $module, $entity, $force);
        $this->writeServiceContract($basePath, $module, $entity, $force);
        $this->writeService($basePath, $module, $entity, $force);
        $this->writeRequests($basePath, $module, $entity, $fields, $force);
        $this->writeDTO($basePath, $module, $entity, $fields, $force);
        $this->writeController($basePath, $module, $entity, $force);
        $this->writeRoutes($basePath, $module, $entity, $entityPluralKebab, $prefix, $force);
        $this->writeProviders($basePath, $module, $entity, $prefix, $force);
        $this->writeMigration($basePath, $module, $entityPluralSnake, $fields, $force);
        $this->patchModuleJson($basePath, $module);

        $this->info("Scaffolding CRUD generado para {$module}/{$entity}.");
        $this->line("➡ Ejecuta: composer dump-autoload && php artisan optimize:clear");
        return self::SUCCESS;
    }

    // ---------------------- Writers ----------------------

    protected function writeEntity(string $base, string $module, string $entity, array $fields, bool $force): void
    {
        $ns = "Modules\\{$module}\\Domain\\Entities";
        $path = "$base/Domain/Entities/{$entity}.php";

        $props   = [];
        $ctor    = [];
        // id al inicio
        $ctor[]  = "        protected ?int \$id,";

        foreach ($fields as $f) {
            [$phpType, $_] = $this->phpType($f['type']);
            $t = ($f['nullable'] ? '?' : '') . $phpType;
            $props[] = "        public {$t} \${$f['name']},";
        }
        // timestamps al final
        $ctor[] = "        protected ?\\Carbon\\Carbon \$created_at,";
        $ctor[] = "        protected ?\\Carbon\\Carbon \$updated_at,";

        $content = <<<PHP
<?php

namespace {$ns};

use Modules\\Core\\Domain\\BaseEntity;

class {$entity} extends BaseEntity
{
    public function __construct(
{$this->joinLines(array_merge($ctor, $props))}
    ) {}
}
PHP;
        $this->put($path, $content, $force);
    }

    protected function writeDomainInterface(string $base, string $module, string $entity, bool $force): void
    {
        $ns = "Modules\\{$module}\\Domain\\Interfaces";
        $path = "$base/Domain/Interfaces/I{$this->plural($entity)}Repository.php";
        $content = <<<PHP
<?php

namespace {$ns};

use Modules\\Core\\Domain\\Interfaces\\IBaseRepository;

interface I{$this->plural($entity)}Repository extends IBaseRepository
{
    // Métodos específicos del dominio si aplican
}
PHP;
        $this->put($path, $content, $force);
    }

    protected function writeEloquent(string $base, string $module, string $entity, array $fields, bool $force): void
    {
        $ns = "Modules\\{$module}\\Infrastructure\\Persistence";
        $path = "$base/Infrastructure/Persistence/{$entity}Eloquent.php";
        $table = Str::snake($this->plural($entity)); // e.g. parcels
        $fillable = implode("','", array_map(fn($f) => $f['name'], $fields));

        $content = <<<PHP
<?php

namespace {$ns};

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class {$entity}Eloquent extends Model
{
    use SoftDeletes;

    protected \$table = '{$table}';

    protected \$fillable = ['{$fillable}'];

    protected \$casts = [
{$this->eloquentCasts($fields, 8)}
    ];
}
PHP;
        $this->put($path, $content, $force);
    }

    protected function writeRepository(string $base, string $module, string $entity, bool $force): void
    {
        $ns = "Modules\\{$module}\\Infrastructure\\Repositories";
        $eloNS = "Modules\\{$module}\\Infrastructure\\Persistence\\{$entity}Eloquent";
        $domainNS = "Modules\\{$module}\\Domain\\Entities\\{$entity}";
        $ifaceNS = "Modules\\{$module}\\Domain\\Interfaces\\I{$this->plural($entity)}Repository";
        $path = "$base/Infrastructure/Repositories/{$this->plural($entity)}Repository.php";

        $content = <<<PHP
<?php

namespace {$ns};

use Modules\\Core\\Infrastructure\\BaseRepository;
use {$domainNS};
use {$ifaceNS};
use {$eloNS};

class {$this->plural($entity)}Repository extends BaseRepository implements I{$this->plural($entity)}Repository
{
    public function __construct({$entity}Eloquent \$model)
    {
        parent::__construct(\$model, {$entity}::class);
    }
}
PHP;
        $this->put($path, $content, $force);
    }

    protected function writeServiceContract(string $base, string $module, string $entity, bool $force): void
    {
        $ns = "Modules\\{$module}\\Application\\Contracts";
        $path = "$base/Application/Contracts/I{$this->plural($entity)}Service.php";
        $content = <<<PHP
<?php

namespace {$ns};

use Modules\\Core\\Application\\Contracts\\IBaseService;

interface I{$this->plural($entity)}Service extends IBaseService
{
    // Casos de uso específicos si aplican
}
PHP;
        $this->put($path, $content, $force);
    }

    protected function writeService(string $base, string $module, string $entity, bool $force): void
    {
        $ns = "Modules\\{$module}\\Application";
        $ifaceNS = "Modules\\{$module}\\Application\\Contracts\\I{$this->plural($entity)}Service";
        $repoNS  = "Modules\\{$module}\\Domain\\Interfaces\\I{$this->plural($entity)}Repository";
        $path = "$base/Application/{$this->plural($entity)}Service.php";

        $content = <<<PHP
<?php

namespace {$ns};

use Modules\\Core\\Application\\BaseService;
use {$ifaceNS};
use {$repoNS};

class {$this->plural($entity)}Service extends BaseService implements I{$this->plural($entity)}Service
{
    public function __construct(I{$this->plural($entity)}Repository \$repository)
    {
        parent::__construct(\$repository);
    }
}
PHP;
        $this->put($path, $content, $force);
    }

    protected function writeRequests(string $base, string $module, string $entity, array $fields, bool $force): void
    {
        $ns = "Modules\\{$module}\\Http\\Requests";
        $dir = "$base/Http/Requests";
        File::ensureDirectoryExists($dir);

        $hasEnum = false;
        foreach ($fields as $f) {
            if ($f['type'] === 'enum') { $hasEnum = true; break; }
        }

        $createRules = $this->validationRules($fields, false);
        $updateRules = $this->validationRules($fields, true);

        $usesForRequests = $hasEnum ? "use Illuminate\\Validation\\Rule;\n" : "";

        $createPath = "$dir/Create{$entity}Request.php";
        $updatePath = "$dir/Update{$entity}Request.php";

        $createContent = <<<PHP
<?php

namespace {$ns};

use Illuminate\\Foundation\\Http\\FormRequest;
{$usesForRequests}
class Create{$entity}Request extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
{$createRules}
        ];
    }
}
PHP;

        $updateContent = <<<PHP
<?php

namespace {$ns};

use Illuminate\\Foundation\\Http\\FormRequest;
{$usesForRequests}
class Update{$entity}Request extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
{$updateRules}
        ];
    }
}
PHP;

        $this->put($createPath, $createContent, $force);
        $this->put($updatePath, $updateContent, $force);
    }

    protected function writeDTO(string $base, string $module, string $entity, array $fields, bool $force): void
    {
        $ns = "Modules\\{$module}\\Http\\DTOs";
        $entityNS = "Modules\\{$module}\\Domain\\Entities\\{$entity}";
        $createReqNS = "Modules\\{$module}\\Http\\Requests\\Create{$entity}Request";
        $updateReqNS = "Modules\\{$module}\\Http\\Requests\\Update{$entity}Request";
        $path = "$base/Http/DTOs/{$entity}DTO.php";

        [$ctorParams, $ctorAssign] = $this->dtoCtor($fields);
        $toEntityArgs = $this->entityNamedArgs($fields);

        $content = <<<PHP
<?php

namespace {$ns};

use {$entityNS};
use {$createReqNS};
use {$updateReqNS};

final class {$entity}DTO
{
{$ctorParams}
    public static function fromCreateRequest(Create{$entity}Request \$r): self
    {
        \$v = \$r->validated();
        return new self(
{$ctorAssign}
        );
    }

    public static function fromUpdateRequest(Update{$entity}Request \$r): self
    {
        \$v = \$r->validated();
        return new self(
{$ctorAssign}
        );
    }

    public function toEntity(?int \$id = null): {$entity}
    {
        return new {$entity}(
            id: \$id,
{$toEntityArgs}
            created_at: null,
            updated_at: null,
        );
    }
}
PHP;
        $this->put($path, $content, $force);
    }

    protected function writeController(string $base, string $module, string $entity, bool $force): void
    {
        $ns = "Modules\\{$module}\\Http\\Controllers\\Api";
        $serviceNS = "Modules\\{$module}\\Application\\Contracts\\I{$this->plural($entity)}Service";
        $dtoNS = "Modules\\{$module}\\Http\\DTOs\\{$entity}DTO";
        $createReqNS = "Modules\\{$module}\\Http\\Requests\\Create{$entity}Request";
        $updateReqNS = "Modules\\{$module}\\Http\\Requests\\Update{$entity}Request";
        $path = "$base/Http/Controllers/Api/{$this->plural($entity)}Controller.php";

        $content = <<<PHP
<?php

namespace {$ns};

use Illuminate\\Http\\Request;
use Modules\\Core\\Http\\BaseController;
use {$serviceNS};
use {$dtoNS};
use {$createReqNS};
use {$updateReqNS};

class {$this->plural($entity)}Controller extends BaseController
{
    public function __construct(private readonly I{$this->plural($entity)}Service \$service) {}

    public function index(Request \$request)
    {
        return \$this->paginated(\$this->service->paginate(
            perPage: \$request->integer('per_page', 15),
            filters: \$request->all()
        ));
    }

    public function store(Create{$entity}Request \$request)
    {
        return \$this->success(
            \$this->service->save({$entity}DTO::fromCreateRequest(\$request)->toEntity()),
            'Created', 201
        );
    }

    public function show(int \$id)
    {
        return \$this->success(\$this->service->find(\$id));
    }

    public function update(Update{$entity}Request \$request, int \$id)
    {
        return \$this->success(
            \$this->service->save({$entity}DTO::fromUpdateRequest(\$request)->toEntity(\$id)),
            'Updated'
        );
    }

    public function destroy(int \$id)
    {
        return \$this->success(\$this->service->delete(\$id), 'Deleted');
    }

    public function active()
    {
        return \$this->success(\$this->service->active());
    }
}
PHP;
        $this->put($path, $content, $force);
    }

    protected function writeRoutes(string $base, string $module, string $entity, string $pluralKebab, string $prefix, bool $force): void
    {
        $routesPath = "$base/routes/api.php";
        if (!File::exists($routesPath)) {
            File::put($routesPath, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");
        }

        $ctrlNS = "Modules\\{$module}\\Http\\Controllers\\Api\\{$this->plural($entity)}Controller";
        $stub = <<<PHP

use {$ctrlNS};

Route::prefix('{$prefix}/{$pluralKebab}')->group(function () {
    Route::get('/',        [{$this->plural($entity)}Controller::class, 'index']);
    Route::get('/active',  [{$this->plural($entity)}Controller::class, 'active']);
    Route::post('/',       [{$this->plural($entity)}Controller::class, 'store']);
    Route::get('/{id}',    [{$this->plural($entity)}Controller::class, 'show']);
    Route::put('/{id}',    [{$this->plural($entity)}Controller::class, 'update']);
    Route::delete('/{id}', [{$this->plural($entity)}Controller::class, 'destroy']);
});
PHP;

        $content = File::get($routesPath);
        if (Str::contains($content, "prefix('{$prefix}/{$pluralKebab}')")) {
            $this->line("ℹ Rutas para {$prefix}/{$pluralKebab} ya existen. Saltando.");
            return;
        }
        File::append($routesPath, $stub);
        $this->line("✔ " . $this->rel($routesPath) . " (+ rutas de {$pluralKebab})");
    }

    protected function writeProviders(string $base, string $module, string $entity, string $prefix, bool $force): void
    {
        $providerPath = "$base/Providers/{$module}ServiceProvider.php";
        $routeProvPath= "$base/Providers/RouteServiceProvider.php";
        $repoIfaceNS  = "Modules\\{$module}\\Domain\\Interfaces\\I{$this->plural($entity)}Repository";
        $repoNS       = "Modules\\{$module}\\Infrastructure\\Repositories\\{$this->plural($entity)}Repository";
        $eloNS        = "Modules\\{$module}\\Infrastructure\\Persistence\\{$entity}Eloquent";
        $svcIfaceNS   = "Modules\\{$module}\\Application\\Contracts\\I{$this->plural($entity)}Service";
        $svcNS        = "Modules\\{$module}\\Application\\{$this->plural($entity)}Service";

        if (!File::exists($providerPath)) {
            $content = <<<PHP
<?php

namespace Modules\\{$module}\\Providers;

use Illuminate\\Support\\ServiceProvider;
use {$repoIfaceNS};
use {$repoNS};
use {$eloNS};
use {$svcIfaceNS};
use {$svcNS};

class {$module}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind({$repoIfaceNS}::class, fn () => new {$repoNS}(new {$eloNS}()));
        \$this->app->bind({$svcIfaceNS}::class, fn (\$app) => new {$svcNS}(\$app->make({$repoIfaceNS}::class)));
    }

    public function boot(): void
    {
        \$this->loadMigrationsFrom(module_path('{$module}', 'Database/Migrations'));
    }
}
PHP;
            $this->put($providerPath, $content, $force);
        }

        if (!File::exists($routeProvPath)) {
            // Importante: NO aplicamos prefix aquí. El prefix se fija en el archivo de rutas con --prefix=...
            $content = <<<PHP
<?php

namespace Modules\\{$module}\\Providers;

use Illuminate\\Foundation\\Support\\Providers\\RouteServiceProvider as ServiceProvider;
use Illuminate\\Support\\Facades\\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$this->routes(function () {
            Route::middleware('api')
                ->group(module_path('{$module}', 'routes/api.php'));
        });
    }
}
PHP;
            $this->put($routeProvPath, $content, $force);
        }
    }

    protected function writeMigration(string $base, string $module, string $table, array $fields, bool $force): void
    {
        $dir = "$base/Database/Migrations";
        File::ensureDirectoryExists($dir);
        $timestamp = date('Y_m_d_His');
        $path = "{$dir}/{$timestamp}_create_{$table}_table.php";

        $cols = $this->migrationColumns($fields);
        $idx  = $this->migrationIndexes($fields);

        $content = <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$t) {
            \$t->id();
{$cols}
            \$t->timestamps();
            \$t->softDeletes();
{$idx}
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
        $this->put($path, $content, $force);
    }

    protected function patchModuleJson(string $base, string $module): void
    {
        $jsonPath = "$base/module.json";
        if (!File::exists($jsonPath)) {
            $this->warn("⚠ module.json no encontrado. Skipping providers patch.");
            return;
        }
        $data = json_decode(File::get($jsonPath), true);
        $data['providers'] = $data['providers'] ?? [];
        $prov = "Modules\\{$module}\\Providers\\{$module}ServiceProvider";
        $rprov= "Modules\\{$module}\\Providers\\RouteServiceProvider";
        foreach ([$prov, $rprov] as $p) {
            if (!in_array($p, $data['providers'], true)) $data['providers'][] = $p;
        }
        File::put($jsonPath, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        $this->line("✔ " . $this->rel($jsonPath) . " (providers patched)");
    }

    // ---------------------- Helpers ----------------------

    protected function put(string $path, string $content, bool $force): void
    {
        if (File::exists($path) && !$force) {
            $this->line("ℹ Existe: " . $this->rel($path) . " (usa --force para sobrescribir)");
            return;
        }
        File::put($path, $content);
        $this->line("✔ " . $this->rel($path));
    }

    protected function rel(string $path): string
    {
        return Str::replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
    }

    protected function plural(string $word): string
    {
        return Str::pluralStudly($word);
    }

    // ---- Schema parsing / generation ----

    protected function parseSchema(string $schema): array
    {
        if (!$schema) return [
            ['name' => 'name', 'type' => 'string', 'nullable' => false, 'args' => [], 'default' => null, 'foreign' => null, 'unique' => false, 'index' => false],
            ['name' => 'active', 'type' => 'boolean', 'nullable' => false, 'args' => [], 'default' => null, 'foreign' => null, 'unique' => false, 'index' => false],
        ];

        $fields = [];
        foreach (array_filter(array_map('trim', explode(',', $schema))) as $chunk) {
            // Ej: latitude:decimal(10,7)?nullable?index
            $name  = trim(Str::before($chunk, ':'));
            $rest  = trim(Str::after($chunk, ':'));
            $type  = $rest;
            $args  = [];
            $nullable = false;
            $default  = null;
            $foreign  = null;
            $unique   = false;
            $index    = false;

            // default (=valor)
            if (Str::contains($rest, '=')) {
                [$type, $def] = array_map('trim', explode('=', $rest, 2));
                $default = strlen($def) ? $def : null;
            }

            // flags
            foreach (['?nullable','nullable','?unique','unique','?index','index'] as $flag) {
                if (Str::contains($type, $flag)) {
                    if (str_contains($flag, 'nullable')) $nullable = true;
                    if (str_contains($flag, 'unique'))   $unique   = true;
                    if (str_contains($flag, 'index'))    $index    = true;
                    $type = Str::replace($flag, '', $type);
                }
            }
            $type = trim($type);

            // enum(values) (usa | o , como separador)
            if (Str::startsWith($type, 'enum')) {
                $valsStr = Str::between($type, '(', ')');
                $list = array_filter(array_map('trim', preg_split('/[|,]/', (string)$valsStr)));
                $args = $list;
                $type = 'enum';
            } elseif (Str::contains($type, '(')) {
                // args e.g. decimal(10,2)
                $argsStr = Str::between($type, '(', ')');
                $args    = array_map('trim', explode(',', (string)$argsStr));
                $type    = Str::before($type, '(');
            }

            // foreign(users)
            if (Str::startsWith($type, 'foreign')) {
                $fkTable = Str::between($rest, 'foreign(', ')');
                $foreign = $fkTable ?: null;
                $type = 'foreign';
            }

            $fields[] = [
                'name'     => $name,
                'type'     => strtolower($type),
                'nullable' => $nullable,
                'args'     => $args,
                'default'  => $default,
                'foreign'  => $foreign,
                'unique'   => $unique,
                'index'    => $index,
            ];
        }
        return $fields;
    }

    protected function phpType(string $type): array
    {
        // Nota: mantenemos tipos simples para DTO/Entidad; las fechas viajan como string
        $php = match ($type) {
            'int', 'integer', 'bigint', 'foreign' => 'int',
            'decimal', 'float', 'double' => 'float',
            'boolean', 'bool' => 'bool',
            default => 'string'
        };
        return [$php, false];
    }

    protected function eloquentCasts(array $fields, int $indent = 4): string
    {
        $map = [];
        foreach ($fields as $f) {
            $cast = match ($f['type']) {
                'int','integer','bigint','foreign' => 'integer',
                'decimal','float','double'         => 'float',
                'boolean','bool'                   => 'boolean',
                'timestamp','datetime','datetimetz','timestamptz' => 'datetime',
                default => null
            };
            if ($cast) $map[$f['name']] = $cast;
        }
        $spaces = str_repeat(' ', $indent);
        $lines = [];
        foreach ($map as $k => $v) $lines[] = "{$spaces}'{$k}' => '{$v}',";
        return implode("\n", $lines);
    }

    protected function validationRules(array $fields, bool $update): string
    {
        $lines = [];
        foreach ($fields as $f) {
            $rules = [];
            // required/sometimes
            $rules[] = $update ? 'sometimes' : ($f['nullable'] ? 'sometimes' : 'required');

            switch ($f['type']) {
                case 'string':
                    $rules[] = 'string';
                    $rules[] = 'max:255';
                    if ($f['nullable']) $rules[] = 'nullable';
                    break;

                case 'text':
                    $rules[] = 'string';
                    if ($f['nullable']) $rules[] = 'nullable';
                    break;

                case 'int':
                case 'integer':
                case 'bigint':
                case 'foreign':
                    $rules[] = 'integer';
                    if ($f['type'] === 'foreign') {
                        $table = $f['foreign'] ?: Str::plural(Str::beforeLast($f['name'], '_id'));
                        $rules[] = "exists:{$table},id";
                    }
                    if ($f['nullable']) $rules[] = 'nullable';
                    break;

                case 'decimal':
                case 'float':
                case 'double':
                    $rules[] = 'numeric';
                    if ($f['nullable']) $rules[] = 'nullable';
                    break;

                case 'boolean':
                case 'bool':
                    $rules[] = 'boolean';
                    if ($f['nullable']) $rules[] = 'nullable';
                    break;

                case 'timestamp':
                case 'timestamptz':
                case 'datetimetz':
                case 'datetime':
                    $rules[] = 'date';
                    if ($f['nullable']) $rules[] = 'nullable';
                    break;

                case 'enum':
                    // Agregar Rule::in([...]) como item sin comillas
                    $list = implode("','", array_map(fn($v) => str_replace("'", "\\'", $v), $f['args']));
                    $rules[] = "Rule::in(['{$list}'])"; // token RAW
                    if ($f['nullable']) $rules[] = 'nullable';
                    break;

                default:
                    $rules[] = 'sometimes';
            }

            // índice/único no se valida aquí, solo a nivel DB

            // Ensamblar: strings con comillas, tokens Rule::... sin comillas
            $pieces = array_map(function ($r) {
                return str_starts_with($r, 'Rule::') ? $r : "'{$r}'";
            }, $rules);

            $lines[] = "            '{$f['name']}' => [" . implode(',', $pieces) . "],";
        }
        return implode("\n", $lines);
    }

    protected function dtoCtor(array $fields): array
    {
        $paramsLines = [];
        $assignLines = [];
        foreach ($fields as $f) {
            [$phpType, $_] = $this->phpType($f['type']);
            $type = ($f['nullable'] ? '?' : '') . $phpType;
            $paramsLines[] = "    public {$type} \${$f['name']};";
            $assignLines[] = "            {$this->castFromValidated($f['name'], $phpType, $f['nullable'])},";
        }
        $ctorParams = "    public function __construct(\n" . implode("\n", array_map(fn($l) => "        " . str_replace(';', ',', $l), $paramsLines)) . "\n    ) {}\n";
        return [$ctorParams, implode("\n", $assignLines)];
    }

    protected function castFromValidated(string $name, string $phpType, bool $nullable): string
    {
        return match ($phpType) {
            'int'   => "{$name}: isset(\$v['{$name}']) ? (int)\$v['{$name}'] : " . ($nullable ? 'null' : '0'),
            'float' => "{$name}: isset(\$v['{$name}']) ? (float)\$v['{$name}'] : " . ($nullable ? 'null' : '0.0'),
            'bool'  => "{$name}: isset(\$v['{$name}']) ? (bool)\$v['{$name}'] : " . ($nullable ? 'null' : 'false'),
            default => "{$name}: " . ($nullable ? "(\$v['{$name}'] ?? null)" : "(\$v['{$name}'] ?? '')"),
        };
    }

    protected function entityNamedArgs(array $fields): string
    {
        $lines = [];
        foreach ($fields as $f) {
            $lines[] = "            {$f['name']}: \$this->{$f['name']},";
        }
        return implode("\n", $lines) . "\n";
    }

    protected function migrationColumns(array $fields): string
    {
        $lines = [];
        foreach ($fields as $f) {
            $name = $f['name'];
            $nullable = $f['nullable'] ? '->nullable()' : '';
            $default  = $f['default'] !== null ? "->default({$f['default']})" : '';

            switch ($f['type']) {
                case 'string':
                    $lines[] = "            \$t->string('{$name}', 255){$nullable}{$default};";
                    break;
                case 'text':
                    $lines[] = "            \$t->text('{$name}'){$nullable}{$default};";
                    break;
                case 'int':
                case 'integer':
                    $lines[] = "            \$t->integer('{$name}'){$nullable}{$default};";
                    break;
                case 'bigint':
                    $lines[] = "            \$t->bigInteger('{$name}'){$nullable}{$default};";
                    break;
                case 'decimal':
                    $precision = isset($f['args'][0]) ? (int)$f['args'][0] : 10;
                    $scale     = isset($f['args'][1]) ? (int)$f['args'][1] : 2;
                    $lines[] = "            \$t->decimal('{$name}', {$precision}, {$scale}){$nullable}{$default};";
                    break;
                case 'float':
                case 'double':
                    $lines[] = "            \$t->float('{$name}'){$nullable}{$default};";
                    break;
                case 'boolean':
                case 'bool':
                    $lines[] = "            \$t->boolean('{$name}'){$default};";
                    break;
                case 'timestamp':
                    $lines[] = "            \$t->timestamp('{$name}'){$nullable}{$default};";
                    break;
                case 'timestamptz':
                case 'datetimetz':
                    $lines[] = "            \$t->timestampTz('{$name}'){$nullable}{$default};";
                    break;
                case 'enum':
                    $vals = implode("','", array_map(function($v) { return str_replace("'", "\\'", $v); }, $f['args']));
                    $lines[] = "            \$t->enum('{$name}', ['{$vals}']){$nullable}{$default};";
                    break;
                case 'foreign':
                    $table = $f['foreign'] ?: Str::plural(Str::beforeLast($name, '_id'));
                    $onDelete = $f['nullable'] ? '->nullOnDelete()' : '->cascadeOnDelete()';
                    $lines[] = "            \$t->foreignId('{$name}')" . ($f['nullable'] ? "->nullable()" : "") . "->constrained('{$table}')" . $onDelete . ";";
                    break;
                default:
                    $lines[] = "            // TODO: tipo no reconocido para '{$name}'";
            }
        }
        return implode("\n", $lines);
    }

    protected function migrationIndexes(array $fields): string
    {
        $lines = [];
        foreach ($fields as $f) {
            if (($f['type'] ?? null) === 'foreign') $lines[] = "            \$t->index('{$f['name']}');";
            if (!empty($f['index'])) $lines[] = "            \$t->index('{$f['name']}');";
            if (!empty($f['unique'])) $lines[] = "            \$t->unique('{$f['name']}');";
            if ($f['name'] === 'active') $lines[] = "            \$t->index('active');";
        }
        return implode("\n", $lines);
    }

    protected function joinLines(array $lines): string
    {
        return implode("\n", array_map(fn($l) => rtrim($l), $lines));
    }
}
