<?php

namespace PictaStudio\Contento\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PictaStudio\Contento\Models\Menu;

class MenuDeleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Menu $menu) {}
}
