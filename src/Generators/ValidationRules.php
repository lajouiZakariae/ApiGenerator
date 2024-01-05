<?php

namespace Zakalajo\ApiGenerator\Generators;

use Illuminate\Support\Collection;
use Zakalajo\ApiGenerator\Database\Column;

class ValidationRules
{
    /**
     * @param Column $column
     * @return Collection
     */
    public static function resolve($column)
    {
        $rules = collect([]);

        $column->isNullable() ? $rules->add("'nullable'") : $rules->add("'required'");

        if ($column->getType() === "tinyint" && $column->getMaxLength() === 1) // will stop here
            return $rules->add("'boolean'");

        if ($column->getType() === 'enum') {
            return $rules->add("Rule::enum(\App\\Enums\\" . str($column->getName())->camel()->ucfirst() . "::class)");
        }

        if ($column->isForeign()) {
            return $rules->add("'exists:" . $column->getForeign()->parent_table . ',' . $column->getForeign()->parent_column . "'");
        }

        if ($column->isFloat()) {
            $rules->add("'numeric'");
        }

        if ($column->isInteger()) {
            $rules->add("'integer'");
            $rules->add("'min:" . ($column->isUnSigned() ? 0 : -2_147_483_648) . "'");
            $rules->add("'max:" . ($column->isUnSigned() ? 4_294_967_295 : 2_147_483_647) . "'");
        }

        if ($column->isBigInt()) {
            $rules->add("'integer'");
            $rules->add("'min:" . ($column->isUnSigned() ? 0 : -9_223_372_036_854_775_808) . "'");
            $rules->add("'max:" . ($column->isUnSigned() ? 18_446_744_073_709_551_615 : 9_223_372_036_854_775_807) . "'");
        }

        if ($column->isTextual()) {
            $rules->add("'string'");
            $rules->add("'min:1'");
            $rules->add("'max:" . $column->getCharMexLength() . "'");
        };

        return $rules;
    }

    /**
     * @param Collection<int,Column> $columns
     */
    public static function resolveMany($columns)
    {
        $all_validation_rules = collect();

        $columns->each(function (Column $column) use ($all_validation_rules) {
            $rules = ValidationRules::resolve($column);
            $all_validation_rules->put($column->getName(), $rules);
        });

        return $all_validation_rules;
    }
}
