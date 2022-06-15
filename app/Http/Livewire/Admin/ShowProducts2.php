<?php

namespace App\Http\Livewire\Admin;

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
    public $search, $categorySearch, $subcategorySearch, $brandSearch, $priceSearch, $colorsSearch, $sizesSearch;
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
        $this->reset(['search', 'categorySearch', 'subcategorySearch', 'brandSearch', 'priceSearch', 'colorsSearch', 'sizesSearch', 'status']);
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

    public function render()
    {
        $products = Product::query()->where('name', 'LIKE', "%{$this->search}%")
            ->categoryFilter($this->categorySearch)
            ->subcategoryFilter($this->subcategorySearch)
            ->brandFilter($this->brandSearch)
            ->statusFilter($this->status);

        if ($this->colorsSearch) {
            $products = $products = Product::colorsFilter($this->colorsSearch);
        }

        if ($this->sizesSearch) {
            $products = $products = Product::sizesFilter($this->sizesSearch);
        }

        if ($this->priceSearch) {
            $products = $products = $products->where('price', 'LIKE', "%{$this->priceSearch}%");
        }

        if ($this->camp && $this->order) {
            $products = $products->orderBy($this->camp, $this->order);
        }

        $products = $products->paginate($this->pagination);

        return view('livewire.admin.show-products2',  compact('products'))
            ->layout('layouts.admin');
    }
}
