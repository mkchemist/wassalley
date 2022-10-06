<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CategoryTreeComponent extends Component
{

    public array $categories;

    public array $branchCategories;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(array $categories, array $branchCategories)
    {
      $this->categories = $categories;
      $this->branchCategories = $branchCategories;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.category-tree-component');
    }
}
