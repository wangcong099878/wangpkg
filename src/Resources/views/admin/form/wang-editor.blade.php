<style>
    .toolbar-container {
        border: 1px solid #ccc;
        background-color: #fff;
    }

    .toolbar-container:after {
        display: table;
        content: '';
        clear: both;
    }

    .editor-toolbar {
        float: left;
    }

    .editor-text {
        border: 1px solid #ccc;
        border-top: 0;
        height: 300px;
        background-color: #fff;
    }

    /*icon*/
    .openAppPageIcon {
        background-image: url('https://imgs.wbwan.vip/zhanneiwenzhang.png');
    }

    .openAppPageIcon.task {
        background-image: url('https://imgs.wbwan.vip/renwuxuanshang.png');
    }

    .openWebSiteIcon {
        background-image: url('https://imgs.wbwan.vip/wangluo.png');
    }
</style>
<div class="form-group {!! !$errors->has($label) ?: 'has-error' !!}">

    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

    @include('admin::form.error')

    {{--        <div id="{{$id}}" style="width: 100%; height: 100%;">
                <p>{!! old($column, $value) !!}</p>
            </div>--}}

    <!--非全屏模式-->
        <div id="container-{{$id}}" class="container-{{$id}}" style="width: 100%; height: 100%;">
            <!--菜单栏-->
            <div class="toolbar-container toolbar-container-{{$id}}" id="toolbar-container-{{$id}}">
                <div class="editor-toolbar" id="editor-toolbar"></div>
            </div>
            <!--编辑区域-->
            <div class="editor-text editor-text-{{$id}}" id="editor-text-{{$id}}">
                {!! old($column, $value) !!}
            </div>
        </div>

        <input type="hidden" name="{{$name}}" value="{{ old($column, $value) }}"/>

    </div>
    <script>
        $(function () {
            initWangEditor("{{$id}}", "{{$name}}");
        });
    </script>
</div>
