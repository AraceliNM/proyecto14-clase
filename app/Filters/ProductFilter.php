<?php

namespace App\Filters;

use App\Models\Size;
use App\Models\Product;
use App\Filters\QueryFilter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends QueryFilter
{
    public function rules(): array
    {
        return [
            'search' => 'filled',
            'category' => 'filled',
            'subcategory' => 'filled',
            'brand' => 'filled',
            'price' => 'filled',
            'color' => 'filled',
            'size' => 'filled',
            'status' => [
                'filled',
                Rule::in([Product::BORRADOR, Product::PUBLICADO])
            ],
        ];
    }

    public function sort($query, $data)
    {
        $query->join('brands', 'brands.id', 'brand_id')
            ->join('subcategories', 'subcategories.id', 'subcategory_id')
            ->join('categories', 'categories.id', 'category_id')
            ->select('products.*')
            ->orderBy($data['field'], $data['asc'] ? 'asc' : 'desc');
    }

    public function search($query, $search)
    {
        return $query->where('products.name', 'LIKE', "%{$search}%");
    }

    public function category($query, $category)
    {
        return $query->whereHas('subcategory', function ($query) use ($category) {
            $query->whereHas('category', function ($query) use ($category) {
                $query->where('name', 'LIKE', "%{$category}%");
            });
        });
    }

    public function subcategory($query, $subcategory)
    {
        return $query->whereHas('subcategory', function (Builder $query) use ($subcategory) {
            $query->where('name', 'LIKE', "%{$subcategory}%");
        });
    }

    public function brand($query, $brand)
    {
        return $query->whereHas('brand', function (Builder $query) use ($brand) {
            $query->where('name', 'LIKE', "%{$brand}%");
        });
    }

    public function status($query, $status)
    {
        return $query->where('status', $status);
    }

    public function price($query, $price)
    {
        return $query->where('price', 'LIKE', "%{$price}%");
    }

    public function color($query, $colorId)
    {
        return $query->whereHas('colors', function ($query) use ($colorId) {
            $query->where('colors.id', $colorId);
        })->orWhereHas('sizes', function ($query) use ($colorId) {
            $query->where(function ($query) use ($colorId) {
                $query->whereHas('colors', function ($query) use ($colorId) {
                    $query->where('color_id', $colorId);
                });
            });
        });
    }

    public function size($query)
    {
        return $query->whereHas('sizes');
    }
}
