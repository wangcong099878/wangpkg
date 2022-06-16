<?php

namespace Wang\Pkg\Http\Admin\Controllers;

use App\Admin\Repositories\Queue; //App\Admin\Repositories\User
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

//use Wang\Pkg\Http\Admin\Action\Reset;
//use Wang\Pkg\Http\Admin\Action\BatchReset;

class QueueController extends AdminController
{

    protected $title='队列管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Queue(), function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');
            // 这里的字段会自动使用翻译文件
            /*$grid->column('id')->sortable();*/

            $grid->column('id', 'id');
            $grid->column('taskname', '队列名称');
            $grid->column('ulid', 'ulid');
            $grid->column('day', '日期');
            $grid->column('state', '状态')->using(\App\Models\Queue::$stateMap);
            $grid->column('error_reason', '错误记录');
            $grid->column('error_num', '错误次数');
            $grid->column('param1', '冗余索引参数');
            $grid->column('param2', '冗余索引参数2');
            $grid->column('content', '队列内容');
            $grid->column('start_at', '开始时间戳');
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间');

            //$grid->disableActions();
            //$grid->disableExport();
            $grid->disableCreateButton();
            //$grid->disableFilter();
            $grid->disableRowSelector();

            // 启用简单分页
            $grid->simplePaginate();

            //$grid->setActionClass(\Dcat\Admin\Grid\Displayers\Actions::class);
            //$grid->setActionClass(\Dcat\Admin\Grid\Displayers\DropdownActions::class);

            $grid->actions(function ($actions) use ($grid) {
                $actions->disableDelete();
                $actions->disableEdit();

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
                $filter->equal('taskname', '队列名称');
                $filter->equal('day', '根据日期筛选');
                $filter->equal('ulid', 'ulid');
                $filter->equal('param1', '索引1');
                $filter->equal('param2', '索引2');

                $map = \App\Models\Queue::$stateMap;
                $map['']='全部';

                $filter->equal('state', "任务状态")->radio($map);
                $filter->between('created_at', '添加日期')->datetime();

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
        return Show::make($id, new Queue(), function (Show $show) {
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
            $show->field('taskname', '队列名称');
            $show->field('ulid', 'ulid');
            $show->field('day', '日期');
            $show->field('state', '状态');
            $show->field('error_reason', '错误记录');
            $show->field('error_num', '错误次数');
            $show->field('param1', '冗余索引参数');
            $show->field('param2', '冗余索引参数2');
            $show->field('content', '队列内容');
            $show->field('start_at', '开始时间戳');
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
        return Form::make(new Queue(), function (Form $form) {


            $form->text('taskname', '队列名称');
            $form->text('ulid', 'ulid');
            $form->datetime('day', '日期')->default(date('Y-m-d H:i:s'));
            $form->text('state', '状态');
            $form->textarea('error_reason', '错误记录');
            $form->text('error_num', '错误次数');
            $form->text('param1', '冗余索引参数');
            $form->text('param2', '冗余索引参数2');
            $form->text('content', '队列内容');
            $form->datetime('start_at', '开始时间戳')->default(date('Y-m-d H:i:s'));

            $form->saved(function (Form $form) {
                return $form->response()->success('保存成功')->redirect('queue');
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
                            return $form->response()->error('系统错误')->redirect('queue');
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


                return $form->response()->success('保存成功')->redirect('queue');
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
