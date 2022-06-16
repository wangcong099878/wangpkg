<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>快速上传</title>
    <style>
        html, body {
            font-size: 12px;
        }

        #box {
            /*background: red;*/
            width: 300px;
            height: 300px;
            border: 1px dashed #000;
            /*position: absolute;*/
            top: 50%;
            left: 50%;
            /*margin: -150px 0 0 -150px;*/
            text-align: center;
            font: 20px/300px '微软雅黑';
            /*display: none;*/
            margin: 0 auto;
        }

        .custom-copy {
            margin-bottom: 1px;
            width: 100%;
            height: 35px;
            line-height: 35px;
            font-size: 12px;
            background: #F2F4F9;
            border: 2px dashed #D6DAE5;
            border-radius: 5px;
            width: 100%;
            text-align: center;

        }

        .custom-app-text {
            color: #2D2D33;
        }

        .custom-icon-copy {
            color: #F9292B;
        }

        .custom-copy-title {
            color: #F9292B;
        }

        #qrcode {
            text-align: center;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="/assets/43626/css/dialog.css?ver=1.0.1">
</head>
<body>
<!--<meter id="m1" value="0" min="0" max="100"></meter>-->


<div class="custom-copy customcopy9020833193004667" data-text="2" data-clipboard-text="2">
<span target="_blank">
<i class="custom-icon-copy"></i>&nbsp;
<span class="custom-copy-text">点此复制&nbsp;【</span>
<span class="custom-copy-title">1</span>
<span class="custom-copy-text">】</span>
</span></div>

<div id="qrcode">
    <img src="" id="images" style="margin: 0 auto;" alt="">
</div>


<div>
    <span>版本信息：</span><span id="app_version"></span>
</div>

<div id="box">
    请将文件拖拽到此区域
</div>

<div style="text-align: center;">
    <progress id="progressBar" value="0" max="100" style="width: 90%;"></progress>
    <span id="percentage"></span>
    <br/>
    <span id="time"></span>
    <br/>
</div>


<script src="/assets/43626/js/zepto.min.js?ver=1.0.1"></script>
<script src="/assets/43626/js/adapative.js?ver=1.0.1"></script>
<script src="/assets/43626/js/animate.js?ver=1.0.1"></script>
<script src="/assets/43626/js/zepto.animate.alias.js?ver=1.0.1"></script>
<script src="/assets/43626/js/swiper.min.js?ver=1.0.1"></script>
<script src="/assets/43626/editor/clipboard.min.js?ver=1.0.1"></script>
<script src="/assets/43626/js/utils.js?ver=1.0.1"></script>
<script type="text/javascript" src="/js/jsqrcode.js"></script>
<script>
    var xhr;
    var ot;//
    var oloaded;
    $(function () {
        var oBox = document.getElementById('box');
        //var oM = document.getElementById('m1');
        var timer = null;
        document.ondragover = function () {
            //clearTimeout(timer);
            //timer = setTimeout(function () {
            //oBox.style.display = 'none';
            //}, 200);
            //oBox.style.display = 'block';
        };
        //进入子集的时候 会触发ondragover 频繁触发 不给ondrop机会
        oBox.ondragenter = function () {
            oBox.innerHTML = '请释放鼠标';
        };
        oBox.ondragover = function () {
            return false;
        };
        oBox.ondragleave = function () {
            oBox.innerHTML = '请将文件拖拽到此区域';
        };
        oBox.ondrop = function (ev) {
            var oFile = ev.dataTransfer.files[0];

            /*            if (typeof (files[0]) == "undefined" || files[0].size <= 0) {
                            alert("请选择图片");
                            return;
                        };*/
            var formData = new FormData();
            formData.append("action", "UploadVMKImagePath");
            formData.append("upload", oFile);
            formData.append("updateType", "admin");
            xhr = new XMLHttpRequest();

            xhr.open("post", "https://api.43626.cn/api/Upload/upFile", true);

            //xhr.setRequestHeader('Authorization', 'bearer ' + globalToken);

            //实现上传进度条
            xhr.upload.onprogress = progressFunction;//【上传进度调用方法实现】
            xhr.upload.onloadstart = function () {//上传开始执行方法
                ot = new Date().getTime(); //设置上传开始时间
                oloaded = 0;//设置上传开始时，以上传的文件大小为0
            };

            xhr.onload = function (response) {
                //console.log(response);
                var res = JSON.parse(response.target.responseText)
                console.log(res);
                if (res.status == 1) {
                    addVersion(res.url);
                    document.getElementById("time").innerHTML = "处理完毕！";
                } else {
                    alert(res.msg);
                }
            };
            xhr.send(formData);

            return false;
            var reader = new FileReader();
            //读取成功
            reader.onload = function () {
                console.log(reader);
            };
            reader.onloadstart = function () {
                alert('读取开始');
            };
            reader.onloadend = function () {
                alert('读取结束');
            };
            reader.onabort = function () {
                alert('中断');
            };
            reader.onerror = function () {
                alert('读取失败');
            };
            reader.onprogress = function (ev) {
                var scale = ev.loaded / ev.total;
                if (scale >= 0.5) {
                    alert(1);
                    reader.abort();
                }
                //oM.value = scale * 100;
            };
            reader.readAsDataURL(oFile, 'base64');
            return false;
        };

        getVersion();
    });

    function addVersion(url) {

        $('#app_version').text(url);

        var imgBase64 = jrQrcode.getQrBase64(url);
        window.document.getElementById('images').src = imgBase64;

        $('.custom-copy-title').html(url);
        $('.custom-copy').attr('data-clipboard-text', url);

        bindCopy();

/*        $.post('/api/addVersion', {apk_url: url}, function (res) {
            $(document).dialog({
                content: "发布成功"
            });
            if (res.code == 200) {
                getVersion();
            }
        }, 'json');*/
    }

    function getVersion() {
/*        $.post('/api/idVersion', {}, function (res) {

            $('#app_version').text(JSON.stringify(res.data));

            var imgBase64 = jrQrcode.getQrBase64(res.data.apk_url);
            window.document.getElementById('images').src = imgBase64;

            $('.custom-copy-title').html(res.data.apk_url);
            $('.custom-copy').attr('data-clipboard-text', res.data.apk_url);

            bindCopy();
        }, 'json');*/
    }


    function bindCopy() {
        var clipboard = new ClipboardJS('.custom-copy');
        clipboard.on('success', function (e) {
            //alert("复制成功");
            //clipboard.destroy();

            $(document).dialog({
                content: "复制成功"
            });
        });
        clipboard.on('error', function (e) {
            //alert("复制失败");
            //clipboard.destroy();

            $(document).dialog({
                content: "复制失败"
            });
        });
    }

    //上传进度实现方法，上传过程中会频繁调用该方法
    function progressFunction(evt) {

        var progressBar = document.getElementById("progressBar");
        var percentageDiv = document.getElementById("percentage");
        var percentage = 0;
        // event.total是需要传输的总字节，event.loaded是已经传输的字节。如果event.lengthComputable不为真，则event.total等于0
        if (evt.lengthComputable) {//
            progressBar.max = evt.total;
            progressBar.value = evt.loaded;
            var percentage = Math.round(evt.loaded / evt.total * 100);

            percentageDiv.innerHTML = percentage + "%";


        }

        var time = document.getElementById("time");
        var nt = new Date().getTime();//获取当前时间
        var pertime = (nt - ot) / 1000; //计算出上次调用该方法时到现在的时间差，单位为s
        ot = new Date().getTime(); //重新赋值时间，用于下次计算

        var perload = evt.loaded - oloaded; //计算该分段上传的文件大小，单位b
        oloaded = evt.loaded;//重新赋值已上传文件大小，用以下次计算

        //上传速度计算
        var speed = perload / pertime;//单位b/s
        var bspeed = speed;
        var units = 'b/s';//单位名称
        if (speed / 1024 > 1) {
            speed = speed / 1024;
            units = 'k/s';
        }
        if (speed / 1024 > 1) {
            speed = speed / 1024;
            units = 'M/s';
        }
        speed = speed.toFixed(1);
        //剩余时间
        var resttime = ((evt.total - evt.loaded) / bspeed).toFixed(1);
        time.innerHTML = '，速度：' + speed + units + '，剩余时间：' + resttime + 's';
        if (bspeed == 0) {
            time.innerHTML = '上传已取消';
        }

        if (percentage == 100) {
            time.innerHTML = "上传完毕,等待系统转移到七牛！";
        }

    }

</script>
</body>
</html>
