<?{{ 'php' }}

namespace {{ $controller_namespace }};

use App\Http\Controllers\Controller;
@if ($resource_name)
use {{ $resource_import }};
@endif
use App\Models\{{ $model_name }};
use {{ $form_request_name ? $form_request_import : 'Illuminate\Http\Request' }};
use Illuminate\Http\Response;
@if ($belongs_to_relations)
@foreach ($belongs_to_relations as $relation)
use {{ $relation->model_import }};
@endforeach
@endif

class {{ $controller_name }} extends Controller {
    public function index(): Response {
@if ($resource_name)
{{ "\t\t\t" }}${{ str()->lower(str()->plural($model_name)) }} = {{ $model_name }}::all();
        return response({{ $resource_name }}::collection(${{ str()->lower(str()->plural($model_name)) }}));
@endif
    }

    public function store({{ $form_request_name ? $form_request_name : 'Request' }} $request): Response {
@if ($resource_name)
{{ "\t\t\t" }}$data = $request->validated();
{{ "\t\t\t" }}${{ str()->lower($model_name) }} = {{ $model_name }}::create($data);
        return response({{ $resource_name }}::collection(${{ str()->lower($model_name) }}), Response::HTTP_CREATED);
@endif
    }

    public function show({{ $model_name }} ${{ str()->lower($model_name) }}): Response {
@if ($resource_name)
{{ "\t\t\t" }}return response(new {{ $resource_name }}(${{ str()->lower($model_name) }}));
@endif
    }

    public function update({{ $form_request_name ? $form_request_name : 'Request' }} $request, {{ $model_name }} ${{ str()->lower($model_name) }}): Response {
@if ($resource_name)
{{ "\t\t\t" }}$data = $request->validated();
{{ "\t\t\t" }}${{ str()->lower($model_name) }}->update($data);
        return response()->noContent();
@endif
    }

    public function destroy({{ $model_name }} ${{ str()->lower($model_name) }}): Response {
@if ($resource_name)
{{ "\t\t\t" }}${{ str()->lower($model_name) }}->delete();
        return response()->noContent();
@endif
    }

@if ($belongs_to_relations)
@foreach ($belongs_to_relations as $relation)
{{ "\t" }}public function {{ $relation->method_name }}({{ $relation->parent_model_name }} ${{ $relation->parent_variable_name }}): Response {
        return response(${{ $relation->parent_variable_name }}->{{ $relation->relation_method_name }});
    }{{ "\n" }}
@endforeach
@endif
}
