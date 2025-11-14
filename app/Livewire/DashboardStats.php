<?php

namespace App\Livewire;

use Livewire\Component;

class DashboardStats extends Component
{
    public $counter = 0;

    public function increment()
    {
        $this->counter++;
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
