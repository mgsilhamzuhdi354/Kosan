<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PenyediaLayout extends Component
{
    public function __construct(public ?string $header = null, public ?string $title = null)
    {
    }

    public function render(): View|Closure|string
    {
        return view('layouts.penyedia');
    }
}
