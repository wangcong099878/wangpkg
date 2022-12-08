<style>
    .main {
        width: 80%;
        margin: 0 auto;
    }

    .upload-content .modal-dialog {
        width: 100%;
    }

    .show {
        text-align: center;
    }

    .upload-content .content-img-list {
        display: inline-block;
        padding: 0;
    }

    .upload-content .content-img .gcl {
        font-size: 25px;
        color: #aaa;
    }

    .upload-content .content-img-list-item {
        position: relative;
        display: inline-block;
        width: 150px;
        height: 150px;
        margin: 7px;
        border: 1px dashed #DEDEDE;
        border-radius: 4px;
        background-color: #fff;
        vertical-align: middle;
        line-height: 150px;
        overflow: hidden;
    }

    .upload-content .content-img-list-item .hide {
        display: none;
    }

    .upload-content .content-img-list-item div {
        position: absolute;
        left: 0;
        bottom: 0;
        text-align: center;
        width: 100%;
        background: rgba(0, 0, 0, 0.4);
        height: 100%;
        line-height: 150px;
    }

    .upload-content .content-img-list-item .delete-btn,
    .upload-content .content-img-list-item .big-btn {
        color: #fff;
        cursor: pointer;
        margin: 0 5px;
    }

    .upload-content .content-img-list-item img {
        width: 100%;
    }

    /*.upload-content .upload-tips {
        padding-top: 10px;
        text-align: right;
        width: 100%;
    }*/
    /*图片上传按钮*/

    .upload-content .file {
        position: relative;
        display: inline-block;
        border: 1px dashed #DEDEDE;
        border-radius: 4px;
        width: 150px;
        height: 150px;
        line-height: 150px;
        text-align: center;
        background-color: #fff;
        vertical-align: top;
        margin: 7px;
    }

    .upload-content .file input {
        position: absolute;
        right: 0;
        top: 0;
        opacity: 0;
        cursor: pointer;
        width: 150px;
        height: 150px;
    }

    .upload-content .file:hover {
        border: 1px dashed #3a75dc;
    }

    #imgPreview {
        width: 40%;
        height: 180px;
        margin: 10px auto 0px auto;
        border: 1px solid black;
        text-align: center;
    }

    #imgSpan {
        position: absolute;
        top: 60px;
        left: 40px;
    }

    .filepath {
        width: 100%;
        height: 100%;
        opacity: 0;
    }
</style>

<link rel="stylesheet" href="/vendor/wangpkg/lib/multipleImage/fonts/font_1805932_ysrcp4y0uy9.css">
<div class="form-group row form-field {!! !$errors->has($errorKey) ?: 'has-error' !!}">
    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>
    <div class="col-sm-8">
        @include('admin::form.error')
        {{-- 这个style可以限制他的高度，不会随着内容变长 --}}
        <div class="">
            <div class="upload-content">
                <div class="content-img">
                    <ul class="content-img-list"></ul>
                    <div class="file">
                        <i class="gcl gcladd"></i>
                        <input type="hidden" name="{{$name}}" id="btn{{$id}}" value='{!! old($column, $value) !!}'>
                        <input type="file" name="file" accept="image/*" id="wangUpload{{$id}}" multiple>
                    </div>
                </div>
                <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog"
                     aria-labelledby="myLargeModalLabel">
                    <div class="modal-dialog modal-lg" role="document" style="width: 80%;">
                        <div class="modal-content" style="border-radius: 5px;padding: 10px;background: #FFFFFF;">

                        </div>
                    </div>
                </div>

                {{--                <div class="modal grid-modal fade in" id="grid-modal-2-state" tabindex="-1" role="dialog" aria-hidden="false" style="display: block;">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content" style="border-radius: 5px;">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                                <h4 class="modal-title">不通过原因</h4>
                                            </div>
                                            <div class="modal-body">
                                                真的来了啊 啊啊啊 啊啊啊啊
                                            </div>
                                        </div>
                                    </div>
                                </div>--}}
            </div>
        </div>

        {{--<textarea type='text/plain' style="height:400px;" id='ueditor' id="{{$id}}" name="{{$name}}" placeholder="{{ $placeholder }}" {!! $attributes !!}  class='ueditor'>
            {!! old($column, $value) !!}
        </textarea>--}}

        {{-- 这个style可以限制他的高度，不会随着内容变长 --}}
        @include('admin::form.help-block')
    </div>
</div>

<script>
    var imageInputId = "btn{{$id}}";
    $(function () {
        //初始化
        try{
            imgSrc = JSON.parse($('#' + imageInputId).val());
        }catch(e){
            imgSrc = [];
        }

        addNewContent('.content-img-list');
        // 鼠标经过显示删除按钮
        $('.content-img-list').on('mouseover', '.content-img-list-item', function () {
            $(this).children('div').removeClass('hide');
        });

        // 鼠标离开隐藏删除按钮
        $('.content-img-list').on('mouseleave', '.content-img-list-item', function () {
            $(this).children('div').addClass('hide');
        });

        // 单个图片删除
        $(".content-img-list").on("click", '.content-img-list-item a .gcllajitong', function () {
            var index = $(this).parent().parent().parent().index();
            imgSrc.splice(index, 1);
            //imgFile.splice(index, 1);
            //imgName.splice(index, 1);
            var boxId = ".content-img-list";

            $('#' + imageInputId).val(JSON.stringify(imgSrc))

            addNewContent(boxId);
            if (imgSrc.length < 4) { //显示上传按钮
                $('.content-img .file').show();
            }
        });


        $(".content-img-list").on("click", '.content-img-list-item a .gclfangda', function () {
            var index = $(this).parent().parent().parent().index();
            $(".modal-content").html("");

            var bigimg = $(".modal-content").html();
            $(".modal-content").html(bigimg + '<div class="show"><img src="' + imgSrc[index] + '" width="50%" alt=""><div>');
            // $(".modal-content").append(
            //     '<div class="show"><img src="' + imgSrc[a] + '" alt=""><div>'
            // );

        });

    });

    //图片上传
    $('#wangUpload{{$id}}').on('change', function (e) {
        var imgSize = this.files[0].size;
/*        if (imgSize > 1024 * 500 * 1) { //1M
            return alert("上传图片不能超过500KB");
        }*/

        if (this.files[0].type != 'image/png' && this.files[0].type != 'image/jpeg' && this.files[0].type != 'image/gif') {
            return alert("图片上传格式不正确");
        }

        var imgBox = '.content-img-list';
        var fileList = this.files;
        for (var i = 0; i < fileList.length; i++) {
            var imgSrcI = getObjectURL(fileList[i]);
            //imgName.push(fileList[i].name);


            var formData = new FormData();
            formData.append("action", "UploadVMKImagePath");
            formData.append("upload", fileList[i]);
            formData.append("updateType", "admin");

            $.ajax({
                url: "{{config('wangpkg.local.upapi')}}",
                data: formData,
                type: "Post",
                dataType: "json",
                cache: false,//上传文件无需缓存
                processData: false,//用于对data参数进行序列化处理 这里必须false
                contentType: false, //必须
                success: function (result) {
                    if (result.status == 1) {
                        //这里需要写入的是实际链接
                        //imgSrc.push(imgSrcI);
                        imgSrc.push(result.url);
                        //imgFile.push(fileList[i]);
                        $('#' + imageInputId).val(JSON.stringify(imgSrc))
                        addNewContent(imgBox);
                    } else {
                        alert(result.msg);
                    }
                },
            })

        }

        this.value = null; //上传相同图片
    });


    //提交请求
    $('#btn-submit-upload').on('click', function () {
        // FormData上传图片
        var formFile = new FormData();
        // formFile.append("type", type);
        // formFile.append("content", content);
        // formFile.append("mobile", mobile);
        // 遍历图片imgFile添加到formFile里面
        $.each(imgFile, function (i, file) {
            formFile.append('myFile[]', file);
        });
        //    $.ajax({
        //        url: '',
        //        type: 'POST',
        //        data: formFile,
        //        async: true,
        //        cache: false,
        //        contentType: false,
        //        processData: false,
        //        // traditional:true,
        //        dataType:'json',
        //        success: function(res) {
        //            console.log(res);
        //            if(res.code==0){
        //                alert("已提交")
        //    //             $("#adviceContent").val("");
        // 			// $("#contact").val("");
        //            }else{
        //                alert(res.message);
        //                $('.content-img .file').show();
        //                $("#adviceContent").val("");
        //                $("#cotentLength").text("0/240");
        // 			$("#contact").val("");
        // 			imgSrc = [];imgFile = [];imgName = [];
        // addNewContent(".content-img-list");

    });


    //删除
    function removeImg(obj, index) {
        imgSrc.splice(index, 1);
        //imgFile.splice(index, 1);
        //imgName.splice(index, 1);
        var boxId = ".content-img-list";
        $('#' + imageInputId).val(JSON.stringify(imgSrc))
        addNewContent(boxId);
    }


    //建立可存取到file的url
    function getObjectURL(file) {
        var url = null;
        if (window.createObjectURL != undefined) { // basic
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }


</script>

