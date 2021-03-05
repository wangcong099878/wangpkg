<div class="form-group {!! !$errors->has($errorKey) ?: 'has-error' !!}">
    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>
    <div class="col-sm-8">
        @include('admin::form.error')

        <div id="formdata{{$id}}">

        </div>
        <script>
            var FromDataId = '#formdata{{$id}}';
            function addFormData(value) {
                var value = value ? value : "";
                var str = getTemplate(value);
                $(FromDataId).append(str);
                $('.additem').unbind('click');
                $('.additem').click(function () {
                    addFormData();
                });
                $('.closeitem').unbind('click');
                $('.closeitem').click(function () {
                    if ($('.additem').length == 1) {
                        alert("最少保留一个");
                    } else {
                        $(this).parent().remove();
                    }
                });
            }
                    <?php if($value != ''){ ?>
                var fromdatalist ={!! htmlspecialchars_decode($value) !!};
                    <?php   }else{ ?>
                var fromdatalist = ["身份证"];
                <?php   } ?>

                if(!fromdatalist){
                    fromdatalist = ["身份证"];
                }


            $(function () {
                    if (fromdatalist != '') {
                        $.each(fromdatalist, function (i, j) {
                            if(j!=""){
                                addFormData(j);
                            }

                        });
                    }else{
                        addFormData();
                    }

                });
        </script>
        @include('admin::form.help-block')
    </div>
</div>