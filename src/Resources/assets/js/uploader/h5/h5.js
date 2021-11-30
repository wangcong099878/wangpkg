function upFile(ret, callback) {
    if (!ret) {
        return;
    }

    $.ajax({
        type: 'POST',
        url: '/wangpkg/getQiniuToken',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        data: {},
        timeout: 60000,
        success: function (response) {
            if (typeof (response) == 'string') {
                response = JSON.parse(response);
            }
            var putExtra = {
                fname: "",
                params: {},
                mimeType: null
            };
            var config = {
                useCdnDomain: true,
                region: qiniu.region.z0
            };
            var observer = {
                next: function (res) {
                    //alert(JSON.stringify(res));  上传图片进度    进度条
                },
                error: function (err) {
                    callback(false);
                },
                complete: function (res) {
                    // 图片地址
                    var images = res.key;
                    callback(images);
                }
            };

            if (response.uptoken) {
                var fileinfo = dataURLtoBlob(ret.base64Data);
                console.log(fileinfo);
                var suffix = '';
                switch (fileinfo.type) {
                    case 'image/jpeg':
                        suffix = '.jpg';
                        break;
                    case 'image/png':
                        suffix = '.png';
                        break;
                    case 'text/csv':
                        suffix = '.csv';
                        break;
                    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                        suffix = '.xlsx';
                        break;
                    case 'application/msword':
                        suffix = '.doc';
                        break;

                    case 'application/vnd.android.package-archive':
                        suffix = '.apk';
                        break;
                }

                //var suffix = ret.data.split(".").pop();
                var filename = Date.parse(new Date()) + suffix;
                //var filename = Date.parse(new Date()) + '.' + suffix;
                //var filename = Date.parse(new Date()) + randomNumber +'.jpeg';
                var observable = qiniu.upload(dataURLtoBlob(ret.base64Data), filename, response.uptoken, putExtra, config);
                subscription = observable.subscribe(observer);
            } else {
                callback(false);
            }
        },
        error: function (xhr, type) {
            /*    	    alert(JSON.stringify(data));
                        alert(_Config.baseURL + url);
                        alert(JSON.stringify(type));
                        alert(JSON.stringify(xhr));*/
            $(document).dialog({
                type: 'notice',
                infoText: '服务器错误',
                autoClose: 1500,
                position: 'bottom'  // center: 居中; bottom: 底部
            })
        }
    });
}


function upImg(ret, callback) {
    if (!ret) {
        return;
    }

    $.ajax({
        type: 'POST',
        url: '/api/up/getToken',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        data: {},
        timeout: 60000,
        success: function (response) {
            if (typeof (response) == 'string') {
                response = JSON.parse(response);
            }
            var putExtra = {
                fname: "",
                params: {},
                mimeType: null
            };
            var config = {
                useCdnDomain: true,
                region: qiniu.region.z0
            };
            var observer = {
                next: function (res) {
                    //alert(JSON.stringify(res));  上传图片进度    进度条
                },
                error: function (err) {
                    callback(false);
                },
                complete: function (res) {
                    // 图片地址
                    var images = res.key;
                    callback(images);
                }
            };

            if (response.uptoken) {

                //alert(dataURLtoBlob(ret.base64Data).data);
                var fileinfo = dataURLtoBlob(ret.base64Data);
                console.log(fileinfo);
                var suffix = '';
                switch (fileinfo.type) {
                    case 'image/jpeg':
                        suffix = '.jpg';
                        break;
                    case 'image/png':
                        suffix = '.png';
                        break;
                    case 'text/csv':
                        suffix = '.csv';
                        break;
                    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                        suffix = '.xlsx';
                        break;
                    case 'application/msword':
                        suffix = '.doc';
                        break;
                    case 'application/vnd.android.package-archive':
                        suffix = '.apk';
                        break;
                }
                //var suffix = ret.data.split(".").pop();
                var filename = Date.parse(new Date()) + suffix;
                //var filename = Date.parse(new Date()) + randomNumber +'.jpg';
                var observable = qiniu.upload(dataURLtoBlob(ret.base64Data), filename, response.uptoken, putExtra, config);
                subscription = observable.subscribe(observer);
            } else {
                callback(false);
            }
        },
        error: function (xhr, type) {
            /*    	    alert(JSON.stringify(data));
                        alert(_Config.baseURL + url);
                        alert(JSON.stringify(type));
                        alert(JSON.stringify(xhr));*/
            $(document).dialog({
                type: 'notice',
                infoText: '服务器错误',
                autoClose: 1500,
                position: 'bottom'  // center: 居中; bottom: 底部
            })
        }
    });
}

function dataURLtoBlob(dataurl) {
    var arr = dataurl.split(','),
        mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]),
        n = bstr.length,
        u8arr = new Uint8Array(n);
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], {
        type: mime
    });
}


//基本post请求
function POST(url, data, callback, headers) {

    var headers = headers ? headers : {
        'Content-Type': 'application/x-www-form-urlencoded',
    };
    $.ajax({
        type: 'POST',
        url: _Config.baseURL + url,
        headers: headers,
        data: {},
        timeout: 60000,
        success: function (res) {
            if (res.code == 109) {
                Cookies.remove("Authorization");
                Cookies.remove("userinfo");
                Cookies.remove("s_token");
                toPage({
                    'pageName': 'userInfo'
                });
                return;
            }
            callback(res);
        },
        error: function (xhr, type) {
            /*    	    alert(JSON.stringify(data));
                        alert(_Config.baseURL + url);
                        alert(JSON.stringify(type));
                        alert(JSON.stringify(xhr));*/
            $(document).dialog({
                type: 'notice',
                infoText: '服务器错误',
                autoClose: 1500,
                position: 'bottom'  // center: 居中; bottom: 底部
            })
        }
    })
}
