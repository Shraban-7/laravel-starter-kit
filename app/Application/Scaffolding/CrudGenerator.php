<?php

namespace App\Application\Scaffolding;

use App\Application\Architecture\ArchitectureLayout;
use App\Domain\Config\StarterContext;

class CrudGenerator
{
    public function generate(StarterContext $context, CrudSpec $spec): void
    {
        $layout = new ArchitectureLayout($context->config, $spec->module);
        $model = $spec->model;
        $variable = $spec->variable();
        $table = $spec->table();

        if ($spec->wants('model')) {
            $namespace = $layout->namespaceFor($layout->models());
            $fillable = implode("', '", array_map(fn (CrudField $field) => $field->name, $spec->fields));
            $context->filesystem->put($context->backendPath($layout->models()."/{$model}.php"), <<<PHP
<?php

namespace {$namespace};

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class {$model} extends Model
{
    use HasFactory;

    protected \$fillable = ['{$fillable}'];
}
PHP);
        }

        if ($spec->wants('migration')) {
            $columns = '';
            foreach ($spec->fields as $field) {
                $method = $field->migrationType();
                $nullable = $field->isRequired() ? '' : '->nullable()';
                if ($method === 'foreignId') {
                    $columns .= "            \$table->foreignId('{$field->name}'){$nullable}->constrained();\n";
                } elseif ($method === 'decimal') {
                    $columns .= "            \$table->decimal('{$field->name}', 10, 2){$nullable};\n";
                } else {
                    $columns .= "            \$table->{$method}('{$field->name}'){$nullable};\n";
                }
            }
            $timestamp = date('Y_m_d_His');
            $context->filesystem->put($context->backendPath("database/migrations/{$timestamp}_create_{$table}_table.php"), <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
{$columns}            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP);
        }

        if ($spec->wants('factory')) {
            $context->filesystem->put($context->backendPath("database/factories/{$model}Factory.php"), <<<PHP
<?php

namespace Database\\Factories;

use Illuminate\\Database\\Eloquent\\Factories\\Factory;

class {$model}Factory extends Factory
{
    public function definition(): array
    {
        return [
            {$this->factoryFields($spec)}
        ];
    }
}
PHP);
        }

        if ($spec->wants('request')) {
            $namespace = $layout->namespaceFor($layout->requests());
            $rules = $this->rules($spec);
            $context->filesystem->put($context->backendPath($layout->requests()."/Store{$model}Request.php"), <<<PHP
<?php

namespace {$namespace};

use Illuminate\\Foundation\\Http\\FormRequest;

class Store{$model}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{$rules}
        ];
    }
}
PHP);
        }

        if ($spec->wants('policy')) {
            $namespace = $layout->namespaceFor($layout->policies());
            $context->filesystem->put($context->backendPath($layout->policies()."/{$model}Policy.php"), <<<PHP
<?php

namespace {$namespace};

use App\\Models\\User;
use {$layout->namespaceFor($layout->models())}\\{$model};

class {$model}Policy
{
    public function viewAny(User \$user): bool
    {
        return true;
    }

    public function create(User \$user): bool
    {
        return true;
    }

    public function update(User \$user, {$model} \${$variable}): bool
    {
        return true;
    }

    public function delete(User \$user, {$model} \${$variable}): bool
    {
        return true;
    }
}
PHP);
        }

        if ($spec->wants('controller')) {
            $this->controller($context, $layout, $spec);
        }

        if ($spec->wants('resource') && $context->config->apiEnabled()) {
            $namespace = $layout->namespaceFor($layout->resources());
            $context->filesystem->put($context->backendPath($layout->resources()."/{$model}Resource.php"), <<<PHP
<?php

namespace {$namespace};

use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

class {$model}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return parent::toArray(\$request);
    }
}
PHP);
        }

        if ($spec->wants('routes')) {
            $this->routes($context, $spec);
        }

        if ($spec->wants('frontend')) {
            $this->frontend($context, $spec);
        }

        if ($spec->wants('admin')) {
            $this->adminResource($context, $spec);
        }

        if ($spec->wants('tests')) {
            $this->tests($context, $spec);
        }
    }

    private function controller(StarterContext $context, ArchitectureLayout $layout, CrudSpec $spec): void
    {
        $api = $context->config->apiEnabled();
        $namespace = $layout->namespaceFor($layout->controllers($api));
        $modelNs = $layout->namespaceFor($layout->models());
        $model = $spec->model;
        $variable = $spec->variable();
        $context->filesystem->put($context->backendPath($layout->controllers($api)."/{$model}Controller.php"), <<<PHP
<?php

namespace {$namespace};

use App\\Http\\Controllers\\Controller;
use {$modelNs}\\{$model};
use Illuminate\\Http\\Request;

class {$model}Controller extends Controller
{
    public function index()
    {
        return {$model}::query()->paginate();
    }

    public function store(Request \$request)
    {
        \$this->authorize('create', {$model}::class);

        return {$model}::query()->create(\$request->validate([]));
    }

    public function show({$model} \${$variable})
    {
        return \${$variable};
    }

    public function update(Request \$request, {$model} \${$variable})
    {
        \$this->authorize('update', \${$variable});
        \${$variable}->update(\$request->validate([]));

        return \${$variable};
    }

    public function destroy({$model} \${$variable})
    {
        \$this->authorize('delete', \${$variable});
        \${$variable}->delete();

        return response()->noContent();
    }
}
PHP);
    }

    private function routes(StarterContext $context, CrudSpec $spec): void
    {
        $model = $spec->model;
        $slug = strtolower($spec->plural());
        $file = $context->config->apiEnabled()
            ? $context->backendPath('routes/api.php')
            : $context->backendPath('routes/web.php');
        $context->filesystem->appendOnce(
            $file,
            "{$model}Controller",
            "Route::apiResource('{$slug}', \\App\\Http\\Controllers\\Api\\{$model}Controller::class);\n",
        );
    }

    private function frontend(StarterContext $context, CrudSpec $spec): void
    {
        $slug = strtolower($spec->plural());
        $model = $spec->model;

        match ($context->config->frontend) {
            'next' => $this->nextPages($context, $slug, $model),
            'nuxt' => $this->nuxtPages($context, $slug),
            'livewire' => $this->livewire($context, $model),
            'vue', 'react' => $context->filesystem->ensureDirectory($context->frontendPath()."/features/{$slug}"),
            default => $this->blade($context, $slug, $model),
        };
    }

    private function nextPages(StarterContext $context, string $slug, string $model): void
    {
        $base = $context->frontendPath()."/app/{$slug}";
        $context->filesystem->put($base.'/page.tsx', "export default function Page() { return <div>{$model} list</div>; }\n");
        $context->filesystem->put($base.'/create/page.tsx', "export default function Page() { return <div>Create {$model}</div>; }\n");
        $context->filesystem->put($base.'/[id]/page.tsx', "export default function Page() { return <div>{$model} detail</div>; }\n");
        $context->filesystem->put($base.'/[id]/edit/page.tsx', "export default function Page() { return <div>Edit {$model}</div>; }\n");
    }

    private function nuxtPages(StarterContext $context, string $slug): void
    {
        $base = $context->frontendPath()."/pages/{$slug}";
        $context->filesystem->put($base.'/index.vue', "<template><div>Index</div></template>\n");
        $context->filesystem->put($base.'/create.vue', "<template><div>Create</div></template>\n");
        $context->filesystem->put($base.'/[id].vue', "<template><div>Show</div></template>\n");
        $context->filesystem->put($base.'/[id]/edit.vue', "<template><div>Edit</div></template>\n");
    }

    private function livewire(StarterContext $context, string $model): void
    {
        $directory = $context->config->livewireDirectory();
        $namespace = $context->config->livewireNamespace();
        $context->filesystem->put($context->backendPath("{$directory}/{$model}s/Index.php"), <<<PHP
<?php

namespace {$namespace}\\{$model}s;

use Livewire\\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.{$this->kebab($model)}s.index');
    }
}
PHP);
    }

    private function blade(StarterContext $context, string $slug, string $model): void
    {
        $context->filesystem->put($context->backendPath("resources/views/{$slug}/index.blade.php"), "<h1>{$model}</h1>\n");
        $context->filesystem->put($context->backendPath("resources/views/{$slug}/create.blade.php"), "<h1>Create {$model}</h1>\n");
        $context->filesystem->put($context->backendPath("resources/views/{$slug}/edit.blade.php"), "<h1>Edit {$model}</h1>\n");
        $context->filesystem->put($context->backendPath("resources/views/{$slug}/show.blade.php"), "<h1>{$model}</h1>\n");
    }

    private function adminResource(StarterContext $context, CrudSpec $spec): void
    {
        $model = $spec->model;
        $context->filesystem->put($context->backendPath("app/Admin/Resources/{$model}Resource.php"), <<<PHP
<?php

namespace App\\Admin\\Resources;

use App\\Admin\\Resource;

class {$model}Resource extends Resource
{
    public static function form(): array
    {
        return [{$this->formFields($spec)}];
    }

    public static function table(): array
    {
        return [{$this->tableFields($spec)}];
    }
}
PHP);
    }

    private function tests(StarterContext $context, CrudSpec $spec): void
    {
        $model = $spec->model;
        $slug = strtolower($spec->plural());
        $context->filesystem->put($context->backendPath("tests/Feature/{$model}Test.php"), <<<PHP
<?php

use App\\Models\\{$model};

it('lists {$slug}', function () {
    {$model}::factory()->create();
    \$this->getJson('/api/v1/{$slug}')->assertSuccessful();
});

it('creates {$slug}', function () {
    \$this->postJson('/api/v1/{$slug}', [])->assertSuccessful();
});

it('updates {$slug}', function () {
    \$model = {$model}::factory()->create();
    \$this->putJson('/api/v1/{$slug}/'.\$model->id, [])->assertSuccessful();
});

it('deletes {$slug}', function () {
    \$model = {$model}::factory()->create();
    \$this->deleteJson('/api/v1/{$slug}/'.\$model->id)->assertNoContent();
});
PHP);
    }

    private function factoryFields(CrudSpec $spec): string
    {
        $lines = [];
        foreach ($spec->fields as $field) {
            $value = match ($field->type) {
                'boolean' => 'true',
                'integer' => '1',
                'decimal' => '10.50',
                default => '$this->faker->word()',
            };
            $lines[] = "'{$field->name}' => {$value},";
        }

        return implode("\n            ", $lines);
    }

    private function rules(CrudSpec $spec): string
    {
        $lines = [];
        foreach ($spec->fields as $field) {
            $rules = $field->rules === [] ? ($field->isRequired() ? 'required' : 'nullable') : implode('|', $field->rules);
            $lines[] = "            '{$field->name}' => '{$rules}',";
        }

        return implode("\n", $lines);
    }

    private function formFields(CrudSpec $spec): string
    {
        return implode(', ', array_map(fn (CrudField $field) => "'{$field->name}' => '{$field->input()}'", $spec->fields));
    }

    private function tableFields(CrudSpec $spec): string
    {
        return implode(', ', array_map(fn (CrudField $field) => "'{$field->name}'", $spec->fields));
    }

    private function kebab(string $value): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $value));
    }
}
