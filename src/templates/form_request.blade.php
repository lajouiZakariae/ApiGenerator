<?{{ 'php' }}

namespace {{ $form_request_namespace }};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class {{ $form_request_name }} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        @if ($validation_rules)
return [
@foreach ($validation_rules as $name => $rules)
{{ "\t\t\t" }}{!! "'" . $name . "'" . ' => ' . $rules . ',' !!}
@endforeach
        ];
@else
return [];
@endif
    }
}
