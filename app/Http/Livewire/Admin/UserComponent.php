<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;

class UserComponent extends Component
{
    public function render()
    {
        return view('livewire.admin.user-component')->layout('layouts.admin');
    }
}
