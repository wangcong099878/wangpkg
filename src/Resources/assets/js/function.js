//后台全局函数
var admin_prefix = '';
function post(url, data, callback, headers) {
    var headers = headers ? headers : {'Content-Type': 'application/x-www-form-urlencoded'};
    $.post({
        type: 'POST',
        url: admin_prefix+url,
        data: data,
        timeout: 15000,
        dataType: 'json',
        headers : headers,
        success: function(res){
            callback(res)
        },
        error: function(xhr, type){
            Dcat.error("请求发生错误！");
        }
    });
}
