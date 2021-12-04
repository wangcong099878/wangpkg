<style>

    .ck.ck-dropdown.ck-heading-dropdown .ck-dropdown__panel .ck-list__item {
        width: 100%;
    }

    .ck.ck-list__item {
        width: 100%;
    }

    .ck .ck-list__item button {
        width: 100%;
    }

    .ck .ck-button__label {
        width: 100%;
    }

    .ck.ck-reset {
        margin: 0 auto;
        width: 100%;
    }

    .ck-content {
        min-height: 250px;
    }

    .image_resized {
        max-width: 100%;
    }

    .image-style-side {
        float: right;
        margin-left: var(--ck-image-style-spacing);
    }

    .image-style-align-left {
        float: left;
        margin-right: var(--ck-image-style-spacing);
    }

    .image-style-align-center {
        margin-left: auto;
        margin-right: auto;
        text-align: center;
    }

    .image-style-align-right {
        float: right;
        margin-left: var(--ck-image-style-spacing);
    }
</style>
<div class="form-group row form-field {!! !$errors->has($errorKey) ?: 'has-error' !!}">

    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>

    <div class="col-sm-8">

        @include('admin::form.error')

        <textarea class="form-control {{ $class }}" style="width: 100%" name="{{$name}}"
                  placeholder="{{ $placeholder }}" {!! $attributes !!} >{{ old($column, $value) }}</textarea>

        @include('admin::form.help-block')

    </div>
</div>
