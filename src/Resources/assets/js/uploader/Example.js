//s_head
//设定用户头像
var uploader = Qiniu.uploader({
    runtimes: 'html5,flash,html4',
    browse_button: 's_head',
    max_file_size: '100000kb',
    flash_swf_url: '/Public/js/common/uploader/Moxie.swf',
    uptoken_url: '/Home/Public/GetToken',
    domain: 'gmqb',
    unique_names: true,
    auto_start: true,
    init: {
        'FilesAdded': function (up, files) {
            //message.manual.show("上传中...");
            /*plupload.each(files, function (file) {
             var type = file.type.split("/")[1];
             if (type == "jpg" || type == "png" || type == "jpeg") {
             
             } else {
             alert("图片格式不正确");
             }
             });*/
        },
        'BeforeUpload': function (up, file) {

        },
        'UploadProgress': function (up, file) {

        },
        'FileUploaded': function (up, file, info) {
            changehead($.parseJSON(info).key)
            //alert();
            /*$("#form_front").attr("src", 'http://gmqb.qiniudn.com/' + $.parseJSON(info).key);
             $("#post_url").val($.parseJSON(info).key);*/
        }, //每个文件上传成功后
        'UploadComplete': function () {
            //alert('完成！');
        },
        'Error': function (up, err, errTip) {
            tipsmsg("上传失败", 'info');
            return
            //alert(errTip);
            //message.manual.close();
        },
    }
});

function changehead(imgname) {
    if (!imgname) {
        tipsmsg("上传失败", 'info');
        return
    }
    var req = new FormData();
    req.append('head', imgname);
    req.append('session_id', getuser("session_id"));
    send('U#SetHead', req, function (res) {
        if (res.code == 1) {
            sethead(imgname);
            setuser("head", imgname);
            tipsmsg("设置成功！");
            return;
        } else {
            tipsmsg(res.msg);
        }
    }, true);
}