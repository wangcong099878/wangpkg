<?php

namespace Wang\Pkg\Http\Admin\Action;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Reset extends RowAction
{
    public $name = '重置';

    //php artisan admin:action Post\\Replicate --grid-row --name="复制"
    public function handle(Model $model, Request $request)
    {
        $model->state=1;
        $model->save();
        return $this->response()->success('重置入队成功.')->refresh();
    }
}
