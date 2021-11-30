<?php

/**
 * Created by PhpStorm.
 * User:  wangcong
 * Date: 2018/4/16
 * Time: 上午11:38
 */

namespace Wang\Pkg\Extensions\Form\Local;

use Encore\Admin\Form\Field;

class upFile extends Field
{

    protected $view = 'wangpkg::admin.form.local.upFile';

    protected static $css = [];

    protected static $js = [
/*        'vendor/wangpkg/js/uploader/h5/qiniu.min.js',
        'vendor/wangpkg/js/uploader/h5/h5.js',*/
    ];

    public function render()
    {
        $baseurl = env('QINIU_IMG_URL');
        $bucket = env('QINIU_BUCKET');

        $apiUrl = config('wangpkg.local.upfile');
        $this->script = <<<EOT
            $(function () {
                var imgUrl = 'https:{$baseurl}';



    // 监听点击上传按钮;
    $('#uo_img{$this->id}').click(function(){
        $('#uploadimage{$this->id}').click();
    });

    document.getElementById('uploadimage{$this->id}').addEventListener("change", function (e) {


                            if (e.target.value == null) {
                        return;
                    }
                    //判断file0的文件类型
                    var file = this.files[0];
                    var _self = this;
                    var formData = new FormData();
                    formData.append("action", "UploadVMKImagePath");
                    formData.append("upload", file);
                    formData.append("updateType", "admin");

                    $.ajax({
                        url: "{$apiUrl}",
                        data: formData,
                        type: "Post",
                        dataType: "json",
                        cache: false,//上传文件无需缓存
                        processData: false,//用于对data参数进行序列化处理 这里必须false
                        contentType: false, //必须
                        success: function (result) {
                            if (result.status == 1) {
                                imgSrc.push();
                                _self.value = '';
                                _self.outerHTML = _self.outerHTML;

                                $("#href{$this->id}").attr("href", result.url);
                            $("#file{$this->id}").val(result.url);
                            } else {
                                alert(result.msg);
                            }
                        },
                    })


    return;
        //阿里云上传图片
        // var formData = new FormData();
        // formData.append('file', file);

        // 七牛云上传服务
        if(e.target.value == null) {
            return;
        }
        //判断file0的文件类型
        var file = this.files[0];

        var _self = this;

        //判断类型
        if(undefined == file){
            return ;
        }
        var r = new FileReader();
        r.readAsDataURL(file);

        r.onload = function(e) {
            var ret = {};
            var str;
            ret.base64Data = e.target.result;

            upFile(ret, function(filepath) {
                _self.value='';
                _self.outerHTML = _self.outerHTML;
/*                var file = document.getElementById('file');
file.value = ''; //虽然file的value不能设为有字符的值，但是可以设置为空值
//或者
file.outerHTML = file.outerHTML;*/

                //console.log(filepath);
                 $("#href{$this->id}").attr("href", imgUrl + filepath);
                            $("#file{$this->id}").val(imgUrl + filepath);
            });
        }
    });

            });
EOT;
        return parent::render();
    }

    public function setValue($value = '')
    {
        if ($value) {
            $this->value = $value;
        }
        return $this;
    }
}
