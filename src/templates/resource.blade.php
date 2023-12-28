<?{{ 'php' }}

namespace {{ $resource_namespace }};

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {{ $resource_name }} extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        @if ($columns)
return [
@foreach ($columns as $column)
{{ "\t\t\t" }}{!! "'" . str()->camel($column->name) . "'" !!} => $this->{{ $column->name }},
@endforeach
        ];
@else
return [];
@endif
    }
}
