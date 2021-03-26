<?php

namespace Wang\Pkg\Http\Admin\Controllers;

use App\Models\QueueError;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class QueueErrorController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '队列错误表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new QueueError());

        $grid->column('id', 'id');
        $grid->column('ulid', '队列ulid');
        $grid->column('error_reason', '错误记录');
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '更新时间');

        //$grid->disableActions();
        $grid->disableExport();
        $grid->disableCreateButton();
        //$grid->disableFilter();
        $grid->disableRowSelector();

        $grid->filter(function ($filter) {
            //$filter->disableIdFilter();
            //$filter->equal('day', '根据日期筛选');
            $filter->equal('taskname', '队列名称');
            $filter->equal('ulid', 'ulid');
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
        $show = new Show(QueueError::findOrFail($id));

        $show->field('id', 'id');
        $show->field('ulid', '队列ulid');
        $show->field('error_reason', '错误记录');
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
        $form = new Form(new QueueError());

        $form->text('ulid', '队列ulid');
        $form->textarea('error_reason', '错误记录');

        return $form;
    }
}
