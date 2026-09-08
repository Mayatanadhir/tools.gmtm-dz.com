<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view(
            app()->getLocale() === 'ar'
                ? 'layouts.app-rtl'
                : 'layouts.app-ltr'
        );
    }
}
