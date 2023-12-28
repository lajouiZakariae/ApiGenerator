<?{{ 'php' }}

namespace {{ $factory_namespace }};

use {{ $model_import }};
@if ($relations_models_imports)
@foreach ($relations_models_imports as $import)
use {{ $import }};
@endforeach
@endif
use Illuminate\Database\Eloquent\Factories\Factory;

final class {{ $factory_name }} extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = {{ $model_name }}::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
@if ($columns_with_factories)
@foreach ($columns_with_factories as $column => $factory)
{{ "\t\t\t" }}{!! $column !!} => {!! $factory !!},
@endforeach
@endif
        ];
    }
}
