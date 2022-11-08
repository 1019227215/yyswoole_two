<html class="no-js" lang="en">

<?php
include __DIR__ . '/../public/head.php';
?>

<body style="position: relative;">
<!-- preloader area start -->
<div id="preloader">
    <div class="loader"></div>
</div>
<!-- preloader area end -->
<!-- page container area start -->
<div class="page-container">
    <!-- main content area start -->
    <div class="main-content">
        <div class="main-content-inner" style="text-align: center;">
            <style type="text/css">

                #tooltip{
                    position:absolute;
                    display:none;
                }

                .tables {
                    border: 1px solid #dee2e6 !important;
                }

                .tables td,th {
                    vertical-align: middle !important;
                    border-right: 1px solid #dee2e6 !important;
                }

                .tables tbody tr td:first-child{
                    width: 30%;
                }

                .tables_none td{
                    border-top: 0 !important;
                }

                td span,input,th span,input {
                    background-color: #fff !important;
                    border: 0 !important;
                }

                td input,th input {
                    border-bottom: 1px solid #ced4da !important;
                }

                #bsWXBox{
                    width:232px !important;
                    height:256px !important;
                }

            </style>
            <div class="row">
                <!-- table dark start -->
                <div class="col-lg-6 mt-5" style="flex: none; max-width: 100%;">
                    <div class="card">
                        <?php echo isset($rdata['additional_content']) ? base64_decode($rdata['additional_content']) : ""; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- main content area end -->
</div>

<!--提示弹窗-->
<div class="modal fade" id="myModal_alert" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="alert alert-primary" role="alert"
                 style="min-height: 188px;margin-bottom: 0;text-align: center;">
                <h4 class="alert-heading"><?php echo $path; ?> 提示:</h4>
                <hr>
                <p class="mb-0"></p>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!--结束-->

<!--分享开始-->
<div class="bshare-custom" style="position: fixed;top: 10px;left: 30px;width: 100%;">
    <a title="分享到微信" class="bshare-weixin"></a>
    <a title="分享到QQ好友" class="bshare-qqim"></a>
    <a title="分享到淘江湖" class="bshare-taojianghu"></a>
    <a title="分享到QQ空间" class="bshare-qzone"></a>
    <a title="分享到新浪微博" class="bshare-sinaminiblog"></a>
    <a title="分享到网易微博" class="bshare-neteasemb"></a>
    <a title="更多平台" class="bshare-more bshare-more-icon more-style-addthis"></a>
    <span class="BSHARE_COUNT bshare-share-count">0</span>
</div>
<script type="text/javascript" charset="utf-8" src="http://static.bshare.cn/b/buttonLite.js#style=-1&amp;uuid=&amp;pophcol=2&amp;lang=zh"></script>
<script type="text/javascript" charset="utf-8" src="http://static.bshare.cn/b/bshareC0.js"></script>
<!--分享结束-->

<script>

    /**
     * 弹窗内容
     */
    function loadAlert($msg) {

        $("#myModal_alert .mb-0").html($msg);
        $("#myModal_alert").modal('show');
        $("#myModal_alert").on('shown.bs.modal', function () {
            setTimeout("$(\"#myModal_alert\").modal('hide')",1000);//延时自动关闭
            //$(this).delay(1000).modal('hide');
        });
    }

    document.oncontextmenu = function(ev) {
        if($('#urlText').length == 0){
            // 创建input
            $("body").after('<input id="urlText" style="position:fixed;top:-200%;left:-200%;" type="text" value=' + window.location.href + '>');
        }
        var url = $('#urlText').val();
        $('#urlText').select(); //选择对象
        document.execCommand("Copy"); //执行浏览器复制命令
        $('#urlText').remove();
        loadAlert("连接已复制,请分享吧!>>>\r\n<br>"+url);
        //阻止默认右键事件
        return false;
    }

    $(function () {

        $(".page-container").addClass("sbar_collapsed");
    });

</script>

<!-- page container area end -->
<?php
include __DIR__ . '/../public/tail.php';
?>
</body>

</html>
