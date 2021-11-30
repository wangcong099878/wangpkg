<link rel="stylesheet" href="/vendor/wangpkg/lib/magnific-popup/magnific-popup.css">
<script src="/vendor/wangpkg/lib/magnific-popup/jquery.magnific-popup.min.js"></script>
<script>
    $(function () {
        $('.grid-popup-link').magnificPopup({
            "type": "image",
            "gallery": {
                "enabled": true,
                "preload": [0, 2],
                "navigateByImgClick": true,
                "arrowMarkup": "<button title=\"%title%\" type=\"button\" class=\"mfp-arrow mfp-arrow-%dir%\"><\/button>",
                "tPrev": "Previous (Left arrow key)",
                "tNext": "Next (Right arrow key)",
                "tCounter": "<span class=\"mfp-counter\">%curr% of %total%<\/span>"
            },
            "mainClass": "mfp-with-zoom",
            "zoom": {"enabled": true, "duration": 300, "easing": "ease-in-out"}
        });
    });
</script>
