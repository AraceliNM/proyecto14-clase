<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class ProductQuery extends Builder
{
    public function categoryFilter($categorySearch)
    {
        return $this->whereHas('subcategory', function ($query) use ($categorySearch) {
            $query->whereHas('category', function ($query) use ($categorySearch) {
                $query->where('name', 'LIKE', "%{$categorySearch}%");
            });
        });
    }

    public function subcategoryFilter($subcategorySearch)
    {
        return $this->whereHas('subcategory', function (Builder $query) use ($subcategorySearch) {
            $query->where('name', 'LIKE', "%{$subcategorySearch}%");
        });
    }

    public function brandFilter($brandSearch)
    {
        return $this->whereHas('brand', function (Builder $query) use ($brandSearch) {
            $query->where('name', 'LIKE', "%{$brandSearch}%");
        });
    }

    public function statusFilter($status)
    {
        return $this->where('status', $status);
    }

    public function colorsFilter($colorId)
    {
        return $this->whereHas('colors', function ($query) use ($colorId) {
            $query->where('colors.id', $colorId);
        })->orWhereHas('sizes', function ($query) use ($colorId) {
            $query->where(function ($query) use ($colorId) {
                $query->whereHas('colors', function ($query) use ($colorId) {
                    $query->where('color_id', $colorId);
                });
            });
        });
    }

    public function sizesFilter()
    {
        return $this->whereHas('sizes');
    }
}
