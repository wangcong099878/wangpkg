//s_head
//�趨�û�ͷ��
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
            //message.manual.show("�ϴ���...");
            /*plupload.each(files, function (file) {
             var type = file.type.split("/")[1];
             if (type == "jpg" || type == "png" || type == "jpeg") {
             
             } else {
             alert("ͼƬ��ʽ����ȷ");
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
        }, //ÿ���ļ��ϴ��ɹ���
        'UploadComplete': function () {
            //alert('��ɣ�');
        },
        'Error': function (up, err, errTip) {
            tipsmsg("�ϴ�ʧ��", 'info');
            return
            //alert(errTip);
            //message.manual.close();
        },
    }
});

function changehead(imgname) {
    if (!imgname) {
        tipsmsg("�ϴ�ʧ��", 'info');
        return
    }
    var req = new FormData();
    req.append('head', imgname);
    req.append('session_id', getuser("session_id"));
    send('U#SetHead', req, function (res) {
        if (res.code == 1) {
            sethead(imgname);
            setuser("head", imgname);
            tipsmsg("���óɹ���");
            return;
        } else {
            tipsmsg(res.msg);
        }
    }, true);
}