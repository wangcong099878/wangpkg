// 是否是全屏的标志
var wangEditorIsFullScreen = []

function initWangEditor(wangEditorId, name) {
    wangEditorIsFullScreen[wangEditorId] = false;

    var E = window.wangEditor;
    var editor = new E('#toolbar-container-' + wangEditorId, '#editor-text-' + wangEditorId);

    editor.customConfig.editorId = wangEditorId;

    editor.customConfig.zIndex = 0;
    //editor.customConfig.uploadImgMaxSize = 100 * 1024 * 1024;
    //editor.customConfig.maxLength = 100000000;
    editor.customConfig.customUploadImg = function (files, insert) {
        console.log(files)

        if (typeof (files[0]) == "undefined" || files[0].size <= 0) {
            alert("请选择图片");
            return;
        }

        var formData = new FormData();
        formData.append("action", "UploadVMKImagePath");
        formData.append("upload", files[0]);
        formData.append("updateType", "admin");

        //第一种  XMLHttpRequest 对象
        //var xhr = new XMLHttpRequest();
        //xhr.open("post", "/Admin/Ajax/VMKHandler.ashx", true);
        //xhr.onload = function () {
        //    alert("上传完成!");
        //};
        //xhr.send(formData);

        //第二种 ajax 提交

        $.ajax({
            url: "/wangpkg/upload",
            data: formData,
            type: "Post",
            dataType: "json",
            cache: false,//上传文件无需缓存
            processData: false,//用于对data参数进行序列化处理 这里必须false
            contentType: false, //必须
            success: function (result) {
                if (result.status == 1) {
                    insert(result.url)
                } else {
                    alert(result.msg);
                }
            },
        })
        //insert('https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png')
    }
    //editor.customConfig.uploadImgShowBase64 = true

    // 关闭粘贴样式的过滤
    //editor.customConfig.pasteFilterStyle = false
    // 忽略粘贴内容中的图片
    editor.customConfig.pasteIgnoreImg = true
    // 自定义处理粘贴的文本内容
    editor.customConfig.pasteTextHandle = function (content) {
        // content 即粘贴过来的内容（html 或 纯文本），可进行自定义处理然后返回
        //return content + '<p>在粘贴内容后面追加一行</p>'
        return content;
    }

    editor.customConfig.onchange = function (html) {
        $('input[name=' + name + ']').val(html);
    }
    editor.create()
}

// 全屏事件
function doFullScreen(id) {
    //https://www.cnblogs.com/whb17bcdq/p/6513766.html
    var cover = document.getElementById('cover');
/*    var toolbarContaner = document.getElementById('toolbar-container-' + id);
    var editorText = document.getElementById('editor-text-' + id);*/

        var toolbarContaner = document.getElementsByClassName('toolbar-container-' + id)[0];
    var editorText = document.getElementsByClassName('editor-text-' + id)[0];

    //document.getElementsByClassName("toolbar-container-content")[0];

    //获取菜单栏的高度
    var toolbarHeight = $('.toolbar-container-' + id).height();
    //获取屏幕高度
    var documentHeight = $(document.body).height();
    var editorHeight = documentHeight - toolbarHeight - 50;

/*    alert(editorHeight);
    alert(toolbarHeight);*/

    cover.style.display = 'block'
    /*editorText.style.height =   editorHeight+'px';*/

    editorText.style.height = '95%';

    cover.appendChild(toolbarContaner)
    cover.appendChild(editorText)
}

// 退出全屏事件
function unDoFullScreen(id) {
    var container = document.getElementsByClassName('container-' + id)[0];
    var toolbarContaner = document.getElementsByClassName('toolbar-container-' + id)[0];
    var editorText = document.getElementsByClassName('editor-text-' + id)[0];
    var cover = document.getElementById('cover');

    container.appendChild(toolbarContaner)
    container.appendChild(editorText)
    editorText.style.height = '300px';
    cover.style.display = 'none'
}

function wangEditorToogleFullScreen(id) {
    if (wangEditorIsFullScreen[id]) {
        wangEditorIsFullScreen[id] = false
        unDoFullScreen(id)
    } else {
        wangEditorIsFullScreen[id] = true
        doFullScreen(id)
    }
}

$(function(){
    if($('#cover').length==0){
        $('body').append('<div id="cover" style="display:none;padding:0;margin:0;overflow-x:hidden; overflow-y:hidden;position: fixed;z-index: 100000;height: 100%;width: 100%;top: 0;background-color: #f1f1f1;"></div>');
    }
});
