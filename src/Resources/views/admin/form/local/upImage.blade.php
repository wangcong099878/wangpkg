<div class="form-group {!! !$errors->has($errorKey) ?: 'has-error' !!}">
    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>
    <div class="col-sm-8">
        @include('admin::form.error')
        <input type="file" name="文件上传" id="uploadimage{{$id}}" style="display:none;" class="inputFile">
        <button type="button" class="btn btn-defaults btn-sm w120" id="uo_img{{$id}}" {!! $attributes !!}>选择文件</button>
        <a href="{!! old($column, $value) !!}" id="href{{$id}}" target="_blank">
            <img src="{!! old($column, $value) !!}"
                 id="img{{$id}}" alt=""
                 width="50px"></a>
        <input type="hidden" name="{{$name}}" id="btn{{$id}}" value="{!! old($column, $value) !!}">
        <script>
            $(function () {
                $('#uo_img{{$id}}').click(function () {
                    $('#uploadimage{{$id}}').click();
                });

                document.getElementById('uploadimage{{$id}}').addEventListener("change", function (e) {
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
                        url: "{{config('wangpkg.local.upapi')}}",
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

                                $("#href{{$id}}").attr("href", result.url);
                                $("#img{{$id}}").attr("src", result.url);
                                $("#btn{{$id}}").val(result.url);
                            } else {
                                alert(result.msg);
                            }
                        },
                    })
                });
            });
        </script>
        {{-- 这个style可以限制他的高度，不会随着内容变长 --}}
        @include('admin::form.help-block')
    </div>
</div>
