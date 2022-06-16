<?php

namespace Wang\Pkg\Http\Admin\Controllers;

use App\Admin\Repositories\Configure; //App\Admin\Repositories\User
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ConfigureController extends AdminController
{

    protected $title='配置管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Configure(), function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');
            // 这里的字段会自动使用翻译文件
            /*$grid->column('id')->sortable();*/

            $grid->column('id', 'id');
            $grid->column('key', 'key');
            $grid->column('name', 'name');
            $grid->column('value', 'value');
            $grid->column('describe', '配置描述');
            $grid->column('state', '是否生效')->using(\App\Models\Configure::$stateMap);
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间');

            //$grid->disableActions();
            //$grid->disableExport();
            //$grid->disableCreateButton();
            $grid->disableFilter();
            $grid->disableRowSelector();

            // 启用简单分页
            $grid->simplePaginate();

            //$grid->setActionClass(\Dcat\Admin\Grid\Displayers\Actions::class);
            //$grid->setActionClass(\Dcat\Admin\Grid\Displayers\DropdownActions::class);

            $grid->actions(function ($actions) use ($grid) {
                //$actions->disableDelete();
                //$actions->disableEdit();

                /*if ($actions->row->deleted == 2) {
                    if ($actions->row->state == 1) {
                        $actions->append((new Pass())->setKey($actions->row->id));
                        $actions->append(new Fail());
                    }
                    $actions->append(new Del());
                }*/

            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Configure(), function (Show $show) {
            // 这里的字段会自动使用翻译文件
            /*$show->field('id');
            $show->field('name');
            $show->field('email');
            $show->field('email_verified_at');
            $show->field('password');
            $show->field('remember_token');
            $show->field('created_at');
            $show->field('updated_at');*/

            $show->field('id', 'id');
            $show->field('key', 'key');
            $show->field('name', 'name');
            $show->field('value', 'value');
            $show->field('describe', '配置描述');
            $show->field('state', '是否生效');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Configure(), function (Form $form) {


            $form->text('key', 'key');
            $form->text('name', 'name');
            $form->text('value', 'value');
            $form->text('describe', '配置描述');
            $form->text('state', '是否生效');

            $form->radio('state', '是否生效')
                ->options([
                    1 => '生效',
                    2 => '不生效',
                ])->default(1);

            $form->saved(function (Form $form) {
                return $form->response()->success('保存成功')->redirect('wangpkg/configure');
            });

            $form->disableResetButton();
            $form->disableViewCheck();
            $form->disableEditingCheck();
            $form->disableCreatingCheck();


            // 这里的字段会自动使用翻译文件
            //http://www.dcatdev.com/admin/form
            /*$form->display('id');
            $form->action(request()->fullUrl());
            $form->text('form1.text', 'text')->required();
            $form->password('form1.password', 'password')->required();
            $form->email('form1.email', 'email');
            $form->mobile('form1.mobile', 'mobile');
            $form->url('form1.url', 'url');
            $form->ip('form1.ip', 'ip');
            $form->color('form1.color', 'color');
            $form->divider(); //分割线
            //弹窗选择
            $form->selectTable('form1.select-table', 'Select Table')
                        ->title('User')
                        ->from(UserTable::make())
                        ->model(Administrator::class, 'id', 'name');
            //弹窗选择
            $form->multipleSelectTable('form1.select-resource-multiple', 'Multiple Select Table')
                ->title('User')
                ->max(4)
                ->from(UserTable::make())
                ->model(Administrator::class, 'id', 'name');

            $form->icon('form1.icon', 'icon');
            $form->rate('form1.rate', 'rate');
            $form->decimal('form1.decimal', 'decimal');
            $form->number('form1.number', 'number');        //数字
            $form->currency('form1.currency', 'currency');  //金额
            $form->switch('form1.switch', 'switch')->default(1);

            $form->html(function () {
                        return '<b>自定义HTML</b>';
                    }, 'html')->help('自定义内容');

            $form->display('created_at');
            $form->display('updated_at');*/

            //在新增页面调用（非提交操作）
            /*            $form->creating(function (Form $form) {
                            if ($form) { // 验证逻辑
                                $form->responseValidationMessages('title', 'title格式错误');

                                // 如有多个错误信息，第二个参数可以传数组
                                $form->responseValidationMessages('content', ['content格式错误', 'content不能为空']);
                            }
                        });*/

            //在编辑页面调用（非提交操作）
            /*            $form->editing(function (Form $form) {
                            if ($form) { // 验证逻辑
                                $form->responseValidationMessages('title', 'title格式错误');

                                // 如有多个错误信息，第二个参数可以传数组
                                $form->responseValidationMessages('content', ['content格式错误', 'content不能为空']);
                            }
                        });*/

            //在表单提交前调用，在此事件中可以修改、删除用户提交的数据或者中断提交操作
            /*            $form->submitted(function (Form $form) {
                            // 获取用户提交参数
                            $title = $form->title;

                            // 上面写法等同于
                            $title = $form->input('title');

                            // 删除用户提交的数据
                            $form->deleteInput('title');

                            // 中断后续逻辑
                            return $form->response()->error('服务器出错了~');
                        });*/

            // 保存前回调，在此事件中可以修改、删除用户提交的数据或者中断提交操作 跳转并提示错误信息
            /*            $form->saving(function (Form $form) {

                            // 修改用户提交的数据
                            $form->author_id = 1;

                            // 删除、忽略用户提交的数据
                            $form->deleteInput('author_id');


                            //可以中断用户请求
                            return $form->response()->error('系统错误')->redirect('configure');
                        });*/

            // 保存后回调，此事件新增和修改操作共用，通过第二个参数 $result 可以判断数据是否保存成功。 跳转并提示成功信息
            /*$form->saved(function (Form $form) {

                //$username = $form->model()->username;

                // 获取最终保存的数组
                //$updates = $form->updates();

                // 判断是否是新增操作
                if ($form->isCreating()) {
                    // 自增ID
                    $newId = $form;
                    // 也可以这样获取自增ID
                    $newId = $form->getKey();

                    if (!$newId) {
                        return $form->error('数据保存失败');
                    }

                    return;
                } else {
                    // 修改操作
                }

                 // 在表單保存後獲取eloquent
                    $form->model()->update(['data' => 'new']);


                return $form->response()->success('保存成功')->redirect('configure');
            });*/

            //删除前回调
            /*            $form->deleting(function (Form $form) {
                            // 获取待删除行数据，这里获取的是一个二维数组
                            $data = $form->model()->toArray();
                        });*/

            //删除后回调，通过第二个参数 $result 可以判断数据是否删除成功。
            /*            $form->deleted(function (Form $form, $result) {
                            // 获取待删除行数据，这里获取的是一个二维数组
                            $data = $form->model()->toArray();

                            // 通过 $result 可以判断数据是否删除成功
                            if (!$result) {
                                return $form->response()->error('数据删除失败');
                            }

                            // 返回删除成功提醒，此处跳转参数无效
                            return $form->response()->success('删除成功');
                        });*/

        });
    }
}
