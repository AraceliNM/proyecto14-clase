<?php

namespace App\Http\Livewire\Admin;

use App\Models\City;
use Livewire\Component;

class ShowCity extends Component
{
    public $city;

    public function mount(City $city)
    {
        $this->city = $city;
    }

    public function render()
    {
        return view('livewire.admin.show-city')->layout('layouts.admin');
    }
}
