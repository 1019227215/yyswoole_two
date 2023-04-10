<!-- 模态框（Modal） -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"><?php echo $path; ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="#" id="form1">
                <div class="modal-body">

                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary submit-btn-area" id="add">提交</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- /.modal -->

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

<!--帮助弹窗-->
<div class="modal fade" id="myModal_help" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" style="margin-left: 350px;">
        <div class="modal-content" style="width: 1200px;height: 860px;">
            <img class="avatar user-thumb" src="../../assets/images/help/help.png" alt="avatar">
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!--结束-->
<script>

    /**
     * 接口请求方法
     * 公共方法
     */
    function query(url, fun, dt = {}, tp = "POST", asy = true, dtpe = "json", err = "log") {
        $.ajax({
            url: url,
            async: asy,
            type: tp,
            data: dt,
            // 成功后开启模态框
            success: fun,
            error: function (XMLHttpRequest, msg, e) {
                if (err == "log") {
                    console.log(XMLHttpRequest, msg, e);
                } else {
                    alert(msg);
                }
            },
            dataType: dtpe
        });
        $(".main-content-inner").children().show();
        $(".main-content-inner .content_loading").hide();
    }

    /**
     * 加载内容
     */
    function load(data) {

        if (!$.isEmptyObject(data)) {
            if (data.code == 600) {

                $(data.position).html(data.data);
            } else {
                console.log(data.message);
            }
        } else {
            console.log("内容为空!", data);
        }
    }

    /**
     * 加载内容
     */
    function reload(data) {

        if (!$.isEmptyObject(data)) {
            if (data.code == 600) {

                location.href = data.URL;
            } else {
                location.href = data.URL;
                console.log(data.message);
            }
        } else {
            console.log("内容为空!", data);
        }
    }

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

    /**
     * 写cookies
     */
    function setCookie(name, value, Days) {
        var exp = new Date();
        exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
        document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";Path=" + escape("/");
    }

    /**
     * 读cookie
     */
    function getCookie(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    }

    /**
     * 删cookie
     */
    function delCookie(name) {
        var exp = new Date();
        exp.setTime(exp.getTime() - 1);
        var cval = getCookie(name);
        console.log(cval, cval != null, cval != "");
        if (cval != null) {
            document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
        }
    }

    /**
     * 清除cookie
     */
    function clearCookie(name) {
        setCookie(name, "", -1);
    }

    /**
     * 闭包
     * 公共事件管理
     */
    $(function () {

        //加载页面内容
        query("<?php echo $interfaceUrl;?>", <?php echo isset($func) ? $func : "load";?>);

        //刷新页面
        $(".fa-refresh").click(function () {

            if ($(".main-content-inner .content_loading").length > 0) {
                $(".main-content-inner").children().hide();
                $(".main-content-inner .content_loading").show();
            } else {
                $(".main-content-inner").children().hide();
                $(".main-content-inner").append("<div class='content_loading' style=\"width: 100%;height: 80px; text-align: center;display: flex;flex-direction: column;justify-content: center;align-items: center;\">\n" +
                    "                <i class=\"fa fa-spinner\" style=\"margin: 0 auto;\" > 内容加载中...</i>\n" +
                    "            </div>");
            }

            //加载页面内容
            query("<?php echo $interfaceUrl;?>", <?php echo isset($func) ? $func : "load";?>);
        });

        //关闭
        $('#myModal').modal('hide');

        //下拉搜索初始化
        $(".standardSelect").chosen({
            disable_search_threshold: 10,
            no_results_text: "Oops, nothing found!",
            width: "100%"
        });

        //帮助
        $('.ti-help').click(function () {

            $('#myModal_help').modal('show');
        });

        //加载个人名称
        if (localStorage.userinfo != "") {
            var userinfo = JSON.parse(localStorage.userinfo);
            $(".user-name span").text(userinfo.uname);
        }

        //退出
        $(".loginout").click(function () {

            var url = "../../auth/index/loginout.php";
            localStorage.userinfo = "";
            delCookie("token");
            clearCookie("token");
            query(url, reload);
        });
    });

</script>