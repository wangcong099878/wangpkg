<?php

/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/9/16
 * Time: 上午11:38
 */

namespace Wang\Pkg\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Configure;

class ConfigureController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('参数配置');
            $content->body($this->grid());
        });
    }

    protected function grid()
    {
        return Admin::grid(Configure::class, function (Grid $grid) {
            $grid->model()->orderBy('created_at', 'desc');

            $grid->id('配置id');

/*            $states = [
                'off' => ['value' => '0', 'text' => '不生效', 'color' => 'danger'],
                'on' => ['value' => '1', 'text' => '生效', 'color' => 'success'],
            ];
            $grid->status('是否开启')->switch($states);*/

            $grid->name('参数名称');
            $grid->describe('参数描述');
            $grid->value('参数值');

            $grid->created_at('添加时间')->sortable();
            $grid->updated_at('修改时间')->sortable();

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableView();
            });

            $grid->disableRowSelector();
        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑配置');
            $content->body(Admin::form(Configure::class, function (Form $form) {
                $form->text('name', '参数名称');
                $form->text('describe', '参数描述');
                $form->textarea('value', '配置内容');
            })->edit($id));
        });
    }

    protected function form()
    {
        return Admin::form(Configure::class, function (Form $form) {
            $form->text('key', 'key');
            $form->text('name', '参数名称');
            $form->text('describe', '参数描述');
            $form->textarea('value', '配置内容');
        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('添加公告');
            $content->description('添加公告');
            $content->body($this->form());
        });
    }

    public function scan($id)
    {

    }
}
