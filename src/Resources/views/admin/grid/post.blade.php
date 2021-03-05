<script>
    //封装网络请求函数
    var prefixPath = '/{{config('admin.route.prefix')}}';
    var _token = '{{$_token}}';
    function post(url, req, callback) {
        var option = {_token: _token};
        var param = $.extend({}, req, option);
        $.post(prefixPath + url, param, callback, 'json');
    }
</script>
