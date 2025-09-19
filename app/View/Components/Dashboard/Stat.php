<?php

namespace App\View\Components\Dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Stat extends Component
{
    public function __construct(
        public string $title,
        public string|int|float $value,
        public ?string $prefix = null,
        public ?string $icon = null
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.dashboard.stat');
    }
}
