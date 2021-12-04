<div class="form-group row form-field {!! !$errors->has($errorKey) ?: 'has-error' !!}">
    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>
    <div class="col-sm-8">
        @include('admin::form.error')
        {{-- 这个style可以限制他的高度，不会随着内容变长 --}}
        <input type="file" name="文件上传" id="uploadimage{{$id}}" style="display:none;" class="inputFile">
        <input type="text" id="file{{$id}}" name="{{$name}}" value="{!! old($column, $value) !!}" class="form-control version_name" placeholder="下载地址">
        <button type="button" class="btn btn-defaults btn-sm w120" id="uo_img{{$id}}" {!! $attributes !!}>选择文件</button>

        {{--<input name="{{$name}}" id="file{{$id}}" alt="" value="{!! old($column, $value) !!}">--}}
        <a href="{!! old($column, $value) !!}"  id="href{{$id}}" target="_blank">
            {!! old($column, $value) !!}
        </a>
        {{--<input type="hidden" name="{{$name}}" id="btn{{$id}}" value="{!! old($column, $value) !!}">--}}
        {{--<textarea type='text/plain' style="height:400px;" id='ueditor' id="{{$id}}" name="{{$name}}" placeholder="{{ $placeholder }}" {!! $attributes !!}  class='ueditor'>
            {!! old($column, $value) !!}
        </textarea>--}}
        <script>

        </script>
        {{-- 这个style可以限制他的高度，不会随着内容变长 --}}
        @include('admin::form.help-block')
    </div>
</div>
