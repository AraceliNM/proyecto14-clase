<?php

namespace App\Http\Livewire;

use Livewire\Component;

class AddCartItemColor extends Component
{
    public $product;
    public $colors;

    public function mount()
    {
        $this->colors = $this->product->colors;
    }

    public function render()
    {
        return view('livewire.add-cart-item-color');
    }
}
