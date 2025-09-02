<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Devider extends Component
{
    public $title;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function render()
    {
        return <<<'blade'
<div class="flex items-center my-2">
    <div class="flex-grow border-t border-gray-200"></div>
    <span class="mx-2 px-2 py-1 border border-gray-200 text-xs bg-gray-200">{{ $title }}</span>
    <div class="flex-grow border-t border-gray-200"></div>
</div>
blade;
    }
}
