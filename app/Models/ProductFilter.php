<?php

namespace App\Models;

class ProductFilter extends QueryFilter
{
    public function rules(): array
    {
        return [
            'categorySearch' => 'filled',
        ];
    }
}
