<?php

namespace App\Http\Livewire\Admin;

use App\Models\Category;
use Livewire\WithFileUploads;
use App\Models\Brand;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateCategory extends Component
{
    use WithFileUploads;

    public $brands, $categories, $image;
    public $createForm = [
        'name' => null,
        'slug' => null,
        'icon' => null,
        'image' => null,
        'brands' => [],
    ];
    public $listeners = ['delete'];

    protected $rules = [
        'createForm.name' => 'required',
        'createForm.slug' => 'required|unique:categories,slug',
        'createForm.icon' => 'required',
        'createForm.image' => 'required|image|max:1024',
        'createForm.brands' => 'required',
    ];
    protected $validationAttributes = [
        'createForm.name' => 'nombre',
        'createForm.slug' => 'slug',
        'createForm.icon' => 'icono',
        'createForm.image' => 'imagen',
        'createForm.brands' => 'marcas',
    ];

    public function mount()
    {
        $this->getBrands();
        $this->getCategories();
        $this->image = 1;
    }

    public function getBrands()
    {
        $this->brands = Brand::all();
    }

    public function save()
    {
        $this->validate();

        $image = $this->createForm['image']->store('categories', 'public');

        $category = Category::create([
            'name' => $this->createForm['name'],
            'slug' => $this->createForm['slug'],
            'icon' => $this->createForm['icon'],
            'image' => $image
        ]);

        $category->brands()->attach($this->createForm['brands']);

        $this->image = 2;
        $this->reset('createForm');

        $this->getCategories();
        $this->emit('saved');
    }

    public function updatedCreateFormName($value)
    {
        $this->createForm['slug'] = Str::slug($value);
    }

    public function getCategories()
    {
        $this->categories = Category::all();
    }

    public function delete(Category $category)
    {
        $category->delete();
        $this->getCategories();
    }

    public function render()
    {
        return view('livewire.admin.create-category');
    }
}
