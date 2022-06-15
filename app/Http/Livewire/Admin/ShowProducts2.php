<?php

namespace App\Http\Livewire\Admin;

use App\Filters\ProductFilter;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ShowProducts2 extends Component
{
    use WithPagination;

    public $pagination = 10;
    public $columns = [
        'Nombre', 'Categoría', 'Subcategoría', 'Marca', 'Fecha de Creación',
        'Stock', 'Color', 'Talla', 'Estado', 'Precio', 'Editar'
    ];
    public $search, $category, $subcategory, $brand, $price, $color, $size;
    public $status = 2;
    public $selectedColumns = [];
    public $show = false;
    public $camp = null;
    public $order = null;
    public $icon = '-circle';

    public function mount()
    {
        $this->selectedColumns = $this->columns;
    }

    public function showColumn($column)
    {
        return in_array($column, $this->selectedColumns);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPagination()
    {
        $this->resetPage();
    }

    protected function getProducts(ProductFilter $productFilter)
    {
        $products = Product::query()
            ->with('subcategory.category')
            ->filterBy($productFilter, [
                'search' => $this->search,
                'category' => $this->category,
                'subcategory' => $this->subcategory,
                'brand' => $this->brand,
                'status' => $this->status,
                'price' => $this->price,
                'color' => $this->color,
                'size' => $this->size,
            ])->paginate($this->pagination);

        $products->appends($productFilter->valid());

        return $products;
    }

    public function clear()
    {
        $this->pagination = 10;
        $this->order = null;
        $this->camp = null;
        $this->selectedColumns = $this->columns;
        $this->icon = '-circle';
    }

    public function clearFilters()
    {
        $this->reset(['search', 'category', 'subcategory', 'brand', 'price', 'color', 'size', 'status']);
        $this->resetPage();
    }

    public function sortable($camp)
    {
        if ($camp !== $this->camp) {
            $this->order = null;
        }
        switch ($this->order) {
            case null:
                $this->order = 'asc';
                $this->icon = '-arrow-circle-up';
                break;
            case 'asc':
                $this->order = 'desc';
                $this->icon = '-arrow-circle-down';
                break;
            case 'desc':
                $this->order = null;
                $this->icon = '-circle';
                break;
        }

        $this->camp = $camp;
    }

    public function render(ProductFilter $productFilter)
    {
        $products = Product::query()->where('name', 'LIKE', "%{$this->search}%");

        if ($this->camp && $this->order) {
            $products = $products->orderBy($this->camp, $this->order);
        };

        $products = $products->paginate($this->pagination);

        return view('livewire.admin.show-products2',  [
            'products' => $this->getProducts($productFilter),
        ])->layout('layouts.admin');
    }
}
