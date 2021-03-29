<?php

namespace Wang\Pkg\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Admin.
 *
 * @method static void routes()
 *
 * @see \Encore\Admin\Admin
 */
class WangPkg extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wang\Pkg\WangPkg::class;
    }
}
