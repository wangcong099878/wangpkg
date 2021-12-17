<div class="form-group row form-field {!! !$errors->has($errorKey) ?: 'has-error' !!}">
    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>
    <div class="col-sm-8">
        @include('admin::form.error')
        {{-- 这个style可以限制他的高度，不会随着内容变长 --}}
        <textarea type='text/plain' style="height:200px;" id='ueditor{{$id}}' name="{{$name}}"
                  placeholder="{{ $placeholder }}"
                  {!! $attributes !!}  class='ueditor'>{!! old($column, $value) !!}</textarea>
        <script>
            $(function () {
                        //解决第二次进入加载不出来的问题
                        //UE.delEditor("ueditor{{$id}}");
                        // 默认id是ueditor
                        var ueditor{{$id}} = UE.getEditor('ueditor{{$id}}', {
                            serverUrl: "{{config('wangpkg.ueditor_api')}}",
                            // 自定义工具栏
                            toolbars: [
                                ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft', 'justifycenter', 'justifyright', 'link', 'unlink', 'insertimage', 'source',
                                    'insertvideo', '|', 'removeformat', 'formatmatch',
                                    'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|', 'preview',
                                    'fullscreen']
                            ],
                            elementPathEnabled: false,
                            enableContextMenu: false,
                            autoClearEmptyNode: true,
                            wordCount: false,
                            imagePopup: false,
                            autoHeightEnabled: false,
                            autotypeset: {indent: true, imageBlockLine: 'center'}
                        });
            });

            /*            var t{{$id}};
            window.addEventListener('pageshow', function (event) {
            t{{$id}} = setInterval(function () {

                if (typeof (UE) != 'undefined') {
                    clearInterval(t{{$id}});

                    if (event.persisted) { // ios 有效, android 和 pc 每次都是 false
                        console.log(1234);
                        //解决第二次进入加载不出来的问题
                        //UE.delEditor("ueditor{{$id}}");
                        // 默认id是ueditor
                        var ueditor{{$id}} = UE.getEditor('ueditor{{$id}}', {
                            serverUrl: "{{config('wangpkg.ueditor_api')}}",
                            // 自定义工具栏
                            toolbars: [
                                ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft', 'justifycenter', 'justifyright', 'link', 'unlink', 'insertimage', 'source',
                                    'insertvideo', '|', 'removeformat', 'formatmatch',
                                    'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|', 'preview',
                                    'fullscreen']
                            ],
                            elementPathEnabled: false,
                            enableContextMenu: false,
                            autoClearEmptyNode: true,
                            wordCount: false,
                            imagePopup: false,
                            autoHeightEnabled: false,
                            autotypeset: {indent: true, imageBlockLine: 'center'}
                        });
                    } else {

                        if (sessionStorage.getItem('refresh') === 'true') {
                            //解决第二次进入加载不出来的问题
                            UE.delEditor("ueditor{{$id}}");
                            // 默认id是ueditor
                            var ueditor{{$id}} = UE.getEditor('ueditor{{$id}}', {
                                serverUrl: "{{config('wangpkg.ueditor_api')}}",
                                // 自定义工具栏
                                toolbars: [
                                    ['bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'insertunorderedlist', 'insertorderedlist', 'justifyleft', 'justifycenter', 'justifyright', 'link', 'unlink', 'insertimage', 'source',
                                        'insertvideo', '|', 'removeformat', 'formatmatch',
                                        'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|', 'preview',
                                        'fullscreen']
                                ],
                                elementPathEnabled: false,
                                enableContextMenu: false,
                                autoClearEmptyNode: true,
                                wordCount: false,
                                imagePopup: false,
                                autoHeightEnabled: false,
                                autotypeset: {indent: true, imageBlockLine: 'center'}
                            });
                        }
                    }
                    sessionStorage.removeItem('refresh');
                }
                },100);
            });*/
        </script>
        @include('admin::form.help-block')
    </div>
</div>
