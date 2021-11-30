<?php
/**
 * Created by PhpStorm.
 * User: wangcong 251957448@qq.com
 * Date: 2020/8/28
 * Time: 上午14:38
 */

namespace Wang\Pkg\Http\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Models\Version;

class VersionController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('版本');
            $content->body($this->grid());
        });
    }

    protected function grid()
    {
        return Admin::grid(Version::class, function (Grid $grid) {
            $grid->model()->orderBy('version_number', 'desc');
            $grid->apk_url('APK下载地址');
            $grid->version_number('版本号');
            $grid->version_name('版本名');
            $grid->update_content('版本更新内容');
            $grid->force('是否强制更新')->using(Version::$forceMap);

/*            $states = [
                'on' => ['value' => 1, 'text' => '强制', 'color' => 'success'],
                'off' => ['value' => 2, 'text' => '不强制', 'color' => 'default'],
            ];

            $grid->column('force', '是否强制更新')->switch($states);*/

            $grid->apk_size('apk大小');
            $grid->apk_md5('md5值');
            $grid->created_at('更新时间');
            $grid->disableExport();
            $grid->disableRowSelector();

            $grid->actions(function ($actions) {
                $actions->disableView();
            });

        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('版本创建');
            $content->body($this->form());

        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('版本');
            $content->body($this->form()->edit($id));
        });
    }

    protected function form()
    {
        return Admin::form(Version::class, function (Form $form) {
            $form->text('apk_url', 'APK下载地址');
            $form->text('version_name', '版本名');
            $form->text('version_number', '版本号');

            $form->text('apk_size', 'apk大小');
            $form->text('apk_md5', 'MD5');

            $form->radio('force', '属性')
                ->options([
                    0=>'不更新',
                    1 => '不强制',
                    2 => '强制',
                ])->default(2);

            //$form->switch('force', '是否强制更新')->states($states)->default(2);

            $form->textarea('update_content', '更新内容');
        });
    }

}
