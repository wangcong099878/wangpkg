// rem适配  1rem = 100px
(function (doc, win) {
    var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function () {
            var clientWidth = docEl.clientWidth;
            if (!clientWidth) return;
            if(clientWidth >= 375){
                docEl.style.fontSize = '100px';
            }else{
                // 设计稿的width:750 font-size:100px 这样算下来 1rem就是100px
                docEl.style.fontSize = 100 * (clientWidth / 375) + 'px';
            }
            setTimeout(function() {
                document.body.style.visibility = 'visible';
            }, 30);
        };

    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);
