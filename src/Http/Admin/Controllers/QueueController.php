<?php

namespace Wang\Pkg\Http\Admin\Controllers;

use App\Models\Queue;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

use Wang\Pkg\Http\Admin\Action\Reset;
use Wang\Pkg\Http\Admin\Action\BatchReset;
class QueueController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '队列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Queue());

        $grid->column('id', 'id');
        $grid->column('taskname', '队列名称');
        $grid->column('ulid', 'ulid');
        //$grid->column('day', '日期');
        $grid->column('state', '状态')->using(Queue::$stateMap);
        $grid->column('error_reason', '执行信息');
        $grid->column('error_num', '错误次数');
        $grid->column('param1', '索引1');
        $grid->column('param2', '索引2');
        //$grid->column('content', '队列内容');
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '更新时间');

        //$grid->disableActions();
        $grid->disableExport();
        $grid->disableCreateButton();
        //$grid->disableFilter();
        //$grid->disableRowSelector();

        $grid->actions(function ($actions) {
            $actions->add(new Reset);
        });

        $grid->batchActions(function ($batch) {
            $batch->add(new BatchReset());
        });

        $grid->filter(function ($filter) {
            //$filter->disableIdFilter();
            $filter->equal('taskname', '队列名称');
            $filter->equal('day', '根据日期筛选');
            $filter->equal('ulid', 'ulid');
            $filter->equal('param1', '索引1');
            $filter->equal('param2', '索引2');

            $map = Queue::$stateMap;
            $map = array_merge([''=>'全部'],$map);

            $filter->equal('state', "任务状态")->radio($map);
            $filter->between('created_at', '添加日期')->datetime();
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Queue::findOrFail($id));

        $show->field('id', 'id');
        $show->field('taskname', '队列名称');
        $show->field('ulid', 'ulid');
        $show->field('day', '日期');
        $show->field('state', '状态');
        $show->field('error_reason', '错误记录');
        $show->field('error_num', '错误次数');
        $show->field('param1', '冗余索引参数');
        $show->field('param2', '冗余索引参数2');
        $show->field('content', '队列内容');
        $show->field('created_at', '创建时间');
        $show->field('updated_at', '更新时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Queue());

        $form->text('taskname', '队列名称');
        $form->text('ulid', 'ulid');
        $form->datetime('day', '日期')->default(date('Y-m-d H:i:s'));
        $form->text('state', '状态');
        $form->textarea('error_reason', '错误记录');
        $form->text('error_num', '错误次数');
        $form->text('param1', '冗余索引参数');
        $form->text('param2', '冗余索引参数2');
        $form->text('content', '队列内容');

        return $form;
    }
}
