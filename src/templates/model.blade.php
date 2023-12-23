<?{{ 'php' }}

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
@if ($belongsToRelations)
use Illuminate\Database\Eloquent\Relations\BelongsTo;
@endif
@if ($hasManyRelations)
use Illuminate\Database\Eloquent\Relations\HasMany;
@endif

class {{ $name }} extends Model {
    use HasFactory;

@if ($fillables)
{{ "\t" }}/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
@foreach ($fillables as $fillable)
{!! "\t\t" . "'" . $fillable . "'" . ',' !!}
@endforeach
    ];
@endif

@if ($casts)
{{ "\t" }}/**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
@foreach ($casts as $key => $val)
{!! "\t\t" . $key . ' => ' . $val . ',' !!}
@endforeach
    ];
@endif

    protected $hidden = ['created_at', 'updated_at'];

@if ($hasManyRelations)
@foreach ($hasManyRelations as $relation)
{{ "\t" }}public function {{ str()->camel($relation->child_table) }}(): HasMany {
{{ "\t\t" }}return $this->hasMany({{ str()->ucfirst(str()->camel(str()->singular($relation->child_table))) }}::class);
{{ "\t" }}}
{{ "\n" }}
@endforeach
@endif
@if ($belongsToRelations)
@foreach ($belongsToRelations as $relation)
{{ "\t" }}public function {{ str()->camel(str()->singular($relation->parent_table)) }}(): BelongsTo {
{{ "\t\t" }}return $this->belongsTo({{ str()->ucfirst(str()->camel(str()->singular($relation->parent_table))) }}::class);
{{ "\t" }}}
{{ "\n" }}
@endforeach
@endif
}
