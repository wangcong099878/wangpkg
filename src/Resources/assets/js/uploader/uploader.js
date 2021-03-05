/*
var uploader = new plupload.Uploader({
 runtimes: 'html5,flash,html4',
 browse_button: 'btn{{$id}}',
 url: '/shop/goods/channelup',
 flash_swf_url: '/js/uploader/Moxie.swf',
 unique_names: true,
 auto_start: true,
 init: {
 'FilesAdded': function (up, files) {
 $.each(files, function (i, file) {
 up.start();
 /!*                            var type = file.name.split(".").pop();
 if (type == "xlsx") {
 up.start();
 } else {
 alert("请传入正确的格式，目前支持xlsx");
 }*!/
 });
 up.refresh();
 },
 'BeforeUpload': function (up, file) {
 //console.log("上传前的处理");
 },
 'UploadProgress': function (up, file) {
 //console.log("上传前的处理");
 },
 'FileUploaded': function (up, file, info) {
 var res = json_decode(info.response);
 console.log(res);
 },
 'UploadComplete': function () {
 //alert('上传成功！');
 //window.reload();

 },
 'Error': function (up, err, errTip) {
 return
 },
 }
 });
 uploader.init();*/



function initUploader(option) {
    var uploader = Qiniu.uploader({
        runtimes: 'html5,flash,html4',
        browse_button: option.browse_button,
        max_file_size: option.max_file_size,
        flash_swf_url: '/js/uploader/Moxie.swf',
        uptoken_url: '/api/getToken',
        domain: 'sjbl',
        unique_names: true,
        auto_start: true,
        init: {
            'FilesAdded': function(up, files) {
                message.manual.show("上传中...");
                plupload.each(files, function(file) {
                    var type = file.type.split("/")[1];
                    
                    if (type == "jpg" || type == "png" || type == "jpeg") {

                    } else {
                        alert("图片格式不正确");
                    }
                });
            },
            'BeforeUpload': function(up, file) {
                

            },
            'UploadProgress': function(up, file) {

            },
            'FileUploaded': option.FileUploaded, //每个文件上传成功后
            'UploadComplete': option.UploadComplete,
            'Error': function(up, err, errTip) {
                alert(errTip);
                message.manual.close();
            },
        }
    });
}

// 广告封面
function initAdFrontUploader() {
    initUploader({
        'browse_button': 'adfront',
        'max_file_size': '200kb',
        'FileUploaded': function(up, file, info) {//每个文件上传成功
            $("#form_front").attr("src", imgUrl + $.parseJSON(info).key);
        },
        'UploadComplete': function() {//文件全部上传完毕
            message.manual.close();
        },
    })
}

// 图标
function initAdIconUploader() {
    initUploader({
        'browse_button': 'adicon',
        'max_file_size': '50kb',
        'FileUploaded': function(up, file, info) {//每个文件上传成功
            $("#form_icon").attr("src", imgUrl + $.parseJSON(info).key);
        },
        'UploadComplete': function() {//文件全部上传完毕
            message.manual.close();
        },
    })
}

// 截图
var screenshot = [];
function initAdScreenshotUploader() {
    initUploader({
        'browse_button': 'adscreenshot',
        'max_file_size': '200kb',
        'FileUploaded': function(up, file, info) {//每个文件上传成功
            if (screenshot.length > 2) {
                screenshot.removeAt(0);
                screenshot.push($.parseJSON(info).key);
            } else {
                screenshot.push($.parseJSON(info).key);
            }
        },
        'UploadComplete': function() {//文件全部上传完毕
            var a = $(".form-upload-sreenshot");
            for (var i = 0; i < screenshot.length; i++) {
               
                var str = '<div class="form-upload-del"><div class="wallet-icon-img wallet-icon-del"></div></div><img src="' + imgUrl + screenshot[i] + '">';
                $(a[i]).html(str);
            }
            adform.delImg();
            message.manual.close();
        },
    })
}





//app封面
function man_initAppFrontUploader() {
    initUploader({
        'browse_button': 'man_app_front',
        'max_file_size': '100kb',
        'FileUploaded': function(up, file, info) {//每个文件上传成功
            $("#act_fontcover").attr("src", imgUrl + $.parseJSON(info).key);
        },
        'UploadComplete': function() {//文件全部上传完毕
            message.manual.close();
        },
    })
}
//app图标
function man_initAppIconUploader() {
    initUploader({
        'browse_button': 'man_app_icon',
        'max_file_size': '50kb',
        'FileUploaded': function(up, file, info) {//每个文件上传成功
            $("#act_icon").attr("src", imgUrl + $.parseJSON(info).key);
        },
        'UploadComplete': function() {//文件全部上传完毕
            message.manual.close();
        },
    })
}

//pc封面
function man_initPcFrontUploader() {
    initUploader({
        'browse_button': 'man_pc_front',
        'max_file_size': '100kb',
        'FileUploaded': function(up, file, info) {//每个文件上传成功
            $("#act_pc_frontcover").attr("src", imgUrl + $.parseJSON(info).key);
        },
        'UploadComplete': function() {//文件全部上传完毕
            message.manual.close();
        },
    })
}