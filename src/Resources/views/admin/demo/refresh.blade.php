<script>
    $(function () {
        $('.grid-row-refresh').click(function () {
            var id = $(this).attr('data-id');
            swal({
                title: '确定刷新吗？',
                text: '',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: ' 确定 ',
                cancelButtonText: ' 取消 ',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false
            }).then(function (dismiss) {
                if (dismiss.value == true) {
                    post('/article/refresh', {id: id}, function (res) {
                        //console.log(res);
                        $.pjax.reload('#pjax-container');
                        toastr.success('操作成功!');
                    });
                }

                if (dismiss.dismiss == 'cancel') {
                    //alert('取消');
                }

                //点击遮罩取消
                if (dismiss.dismiss == 'overlay') {

                }

                //点击esc
                if (dismiss.dismiss == 'esc') {

                }
                /*                swal(
                                    '已删除！',
                                    '你的文件已经被删除。',
                                    'success'
                                );*/
            })
        });
    });

</script>
