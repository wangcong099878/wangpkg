<?php

namespace Wang\Pkg\Http\Admin\Action;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchReset extends BatchAction
{
    public $name = '批量入队';

    /*
     $grid->batchActions(function ($batch) {
            $batch->add(new BatchReplicate());
      });
     */

    //https://laravel-admin.org/docs/zh/1.x/model-grid-custom-actions

    //php artisan admin:action Post\\BatchReplicate --grid-batch --name="批量复制"
    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            // ...
            $model->state=1;
            $model->save();
        }

        return $this->response()->success('批量重置成功')->refresh();
    }

}
