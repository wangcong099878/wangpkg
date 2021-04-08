<?php

namespace Wang\Pkg\Http\Admin\Controllers;

use App\Models\Codelist;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CodelistController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '短信';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Codelist());
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', 'id');
        $grid->column('phone', '手机号码');
        $grid->column('code', '验证码');
        $grid->column('ip', 'ip');
        $grid->column('scene', '业务场景')->using(Codelist::$sceneMap);
        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '更新时间');

        $grid->disableActions();
        $grid->disableExport();
        $grid->disableCreateButton();
        //$grid->disableFilter();
        $grid->disableRowSelector();

        $grid->enableHotKeys();

        $grid->filter(function ($filter) {
            //$filter->disableIdFilter();
            $filter->equal('phone', '手机号码');
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
        $show = new Show(Codelist::findOrFail($id));

        $show->field('id', 'id');
        $show->field('phone', '手机号码');
        $show->field('code', '验证码');
        $show->field('ip', 'ip');
        $show->field('scene', '业务场景');
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
        $form = new Form(new Codelist());

        $form->mobile('phone', '手机号码');
        $form->text('code', '验证码');
        $form->ip('ip', 'ip');
        $form->text('scene', '业务场景')->default(1);

        return $form;
    }
}
