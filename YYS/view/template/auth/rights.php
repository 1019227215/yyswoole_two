<style type="text/css">
    table {
        border-collapse: collapse;
        width: 100%;
        table-layout: fixed;
    }

    tr {
        width: 100%;
    }

    tr td {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        word-wrap: break-word;
    }

    /*tr td:nth-child(1),tr td:nth-child(3){
        width: 20%;
    }
    tr td:nth-child(2){
        width: 56%;
    }*/

    #tooltip {
        position: absolute;
        display: none;
    }

</style>
<div class="row">
    <!-- table dark start -->
    <div class="col-lg-6 mt-5" style="flex: none; max-width: 100%;">
        <div class="card">
            <div class="card-body">
                <div style="width: 100%;">
                    <input class="btn btn-dark mb-3" style="float:left;" type="button" data-toggle="modal"
                           data-target="#myModal" id="add_data" value="添加">
                    <div style="clear:both;"></div>
                </div>
                <div class="single-table">
                    <div class="table-responsive">
                        <table class="table text-center">

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- table dark end -->
</div>

<script>

    /**
     * 闭包
     * 页面事件处理
     */
    $(function () {

            //添加模态框
            $(document).on('click', '#add_data', function () {

                $(".submit-btn-area").attr("id", "add");
                query("../../auth/index/getParent.php", setData);

                //提交
                $("#add").off('click').on('click', function () {

                    //关闭
                    $('#myModal').modal('hide');
                    var tt = /^\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}$/, ss = /\W+/;
                    var name = $("#Uname").val(), email = $("#Email").val(), p1 = $("#Password").val(),
                        p2 = $("#Password1").val();

                    if (!tt.test(name) || name == "") {
                        if (ss.test(name) || name == "") {
                            loadAlert("用户名不可为空,不可使用符号!");
                            return false;
                        }
                    }

                    if (!tt.test(email) || email == "") {
                        loadAlert("邮箱不可为空或格式错误!");
                        return false;
                    }

                    if (p1 != p2 || p1 == "" || p2 == "") {
                        loadAlert("密码不可为空或两次不一样!");
                        return false;
                    }

                    var data = $("#form1").serializeArray();
                    query("../../auth/index/checkuser.php", checkdata, data);

                });
            });

            //修改模态框
            $(document).on('click', '.update_data', function () {

                $(".submit-btn-area").attr("id", "update");
                var data = {"Id": $(this).attr("sid")};
                query("../../auth/index/getParent.php", setData, data);

                //提交
                $("#update").off('click').on('click', function () {

                    //关闭
                    $('#myModal').modal('hide');
                    var tt = /^\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}$/, ss = /\W+/;
                    var name = $("#Uname").val(), email = $("#Email").val(), p1 = $("#Password").val(),
                        p2 = $("#Password1").val();

                    if (!tt.test(name) || name == "") {
                        if (ss.test(name) || name == "") {
                            loadAlert("用户名不可为空,不可使用符号!");
                            return false;
                        }
                    }

                    if (!tt.test(email) || email == "") {
                        loadAlert("邮箱不可为空或格式错误!");
                        return false;
                    }

                    if (p1 != p2) {
                        loadAlert("密码两次不一样!");
                        return false;
                    }

                    var data = $("#form1").serializeArray();
                    query("../../auth/index/checkuser.php", checkdataLogin, data);
                });
            });

            //删除数据
            $(document).on('click', '.delete_data', function () {

                var data = {"Id": $(this).attr("sid"), "Status": $(this).attr("status")};
                query("../../auth/index/disable.php", result, data);
            });

            /**
             * title样式
             * @type {number}
             */
            var x = 10;
            var y = 20;
            var newtitle = '';
            $('table td').mouseover(function (e) {
                newtitle = this.title;
                if (newtitle != '') {
                    this.title = '';
                    $('.main-content-inner').append('<div id="tooltip" class="alert alert-primary alert-dismissible fade show">' + newtitle + '</div>');
                    $('#tooltip').css({
                        'left': (e.pageX + x + 'px'),
                        'top': (e.pageY + y + 'px')
                    }).show();
                }
            }).mouseout(function () {
                this.title = newtitle;
                $('#tooltip').remove();
            }).mousemove(function (e) {
                $('#tooltip').css({
                    'left': (e.pageX + x + 'px'),
                    'top': (e.pageY + y + 'px')
                }).show();
            });


        }
    );


    /**
     * 加载内容
     */
    function checkdata(data) {

        if (!$.isEmptyObject(data)) {

            if (data.code == 600) {

                //console.log(data);
                var data = $("#form1").serializeArray();
                query("../../auth/index/add.php", result, data);
            } else {
                console.log(data.message);
                loadAlert(data.message);
            }
        } else {
            console.log("内容为空!", data);
            loadAlert("内容为空!");
        }
    }

    /**
     * 加载内容
     */
    function checkdataLogin(data) {

        if (!$.isEmptyObject(data)) {

            if (data.code == 605 && data.data.num > 0) {

                //console.log(data);
                var data = $("#form1").serializeArray();
                query("../../auth/index/update.php", result, data);
            } else {
                console.log(data.message);
                loadAlert("账号不存在!");
            }
        } else {
            console.log("内容为空!", data);
            loadAlert("内容为空!");
        }
    }

    /**
     * 操作回调
     */
    function result(data) {

        if (!$.isEmptyObject(data)) {
            if (data.code == 600) {
                loadAlert(data.message);
                console.log(data.message);
            } else {
                loadAlert(data.message);
                console.log(data.message);
            }
        } else {
            console.log("内容为空!", data);
        }

        //加载页面内容
        query("../../auth/index/userList.php", setTable);
    }

    /**
     * 模态框回调
     * @param data
     */
    function setData(data) {

        if (!$.isEmptyObject(data)) {
            if (data.code == 600) {

                var htmlContent = "";
                if (data.hasOwnProperty("checkdata")) {
                    htmlContent = data.checkdata.hasOwnProperty("id") ? "<input type='hidden' name='Id' value='" + data.checkdata.id + "' />" : "";
                }
                $(data.Modal).html(htmlContent);
                //初始化模板内容
                if (!$.isEmptyObject(data.content)) {

                    for (var key in data.content) {

                        var hides = "";
                        if ($.inArray(key, ["Email"]) != -1 && $("#update").length > 0){
                            hides = " readonly";
                        }

                        //修改参数获取
                        var vals = ""
                        if (data.hasOwnProperty("checkdata")) {
                            vals = data.checkdata.hasOwnProperty(key.toLowerCase()) ? data.checkdata[key.toLowerCase()] : "";
                        }

                        if (data.data.hasOwnProperty(key)) {

                            //特殊内容
                            if (!$.isEmptyObject(data.data[key])) {

                                var content = data.data[key];

                                //下拉框处理
                                if ($.inArray(content.type.toString(), ["standardSelect", "select"]) != -1) {

                                    //公共下拉样
                                    htmlContent += "<div class=\"form-group\">\n" +
                                        "<label for=\"example-text-input\" class=\"col-form-label\">" + data.content[key] + "</label>\n" +
                                        "<div style=\"width:100%;height: 48px;\">" +
                                        "<select " + content.style + " "+hides+" name=\"" + key + "\" id=\"" + key + "\" style=\"width:100%;height: 48px;\">\n";

                                    if (content.type.toString() == "standardSelect") {

                                        //多选层级下拉处理
                                        cvals = vals.split("|");
                                        for (var keys in content.data) {

                                            htmlContent += "<option value=\"\"></option>\n" +
                                                "<optgroup label=\"" + content.data[keys].name + "\" PsysCss=\"" + keys + "\">\n";

                                            for (var ks in content.data[keys].data) {

                                                var selectd = ($.inArray(ks, cvals) != -1) ? "selected" : "";
                                                htmlContent += "<option " + selectd + " value=\"" + ks + "\" sysCss=\"" + keys + "\">" + content.data[keys].name + " - " + content.data[keys].data[ks] + "</option>";
                                            }

                                            htmlContent += "</optgroup>\n";
                                        }
                                    } else {

                                        //普通多选和单选下拉
                                        for (var keys in content.data) {

                                            var selectd = (vals == keys) ? "selected" : ((!data.hasOwnProperty("checkdata") && keys == "1") ? "selected" : "");
                                            htmlContent += "<option " + selectd + " value=\"" + keys + "\" >" + content.data[keys] + "</option>";
                                        }

                                    }

                                    htmlContent += "</select>\n </div>\n </div>\n";
                                } else {
                                    //文本处理
                                    htmlContent += "<div class=\"form-group\">\n" +
                                        "<label for=\"example-text-input\" class=\"col-form-label\">" + data.content[key] + "</label>\n" +
                                        "<input class=\"form-control\" "+hides+" type=\"" + content.type.toString() + "\" placeholder=\"" + data.content[key] + "\" name=\"" + key + "\" id=\"" + key + "\" value='" + vals + "'>\n" +
                                        "</div>";
                                }
                            }
                        } else {

                            //普通文本处理
                            htmlContent += "<div class=\"form-group\">\n" +
                                "<label for=\"example-text-input\" class=\"col-form-label\">" + data.content[key] + "</label>\n" +
                                "<input class=\"form-control\" "+hides+" type=\"text\" placeholder=\"" + data.content[key] + "\" name=\"" + key + "\" id=\"" + key + "\" value='" + vals + "'>\n" +
                                "</div>";
                        }

                    }

                    //内容写入模态框
                    $(data.Modal).html(htmlContent);

                    //下拉搜索初始化
                    $(".standardSelect").chosen({
                        disable_search_threshold: 10,
                        no_results_text: "Oops, nothing found!",
                        width: "100%"
                    });
                }

            } else {
                console.log(data.message);
            }
        } else {
            console.log("内容为空!", data);
        }

    }

    /**
     * 设置table内容
     */
    function setTable(data) {
        if (!$.isEmptyObject(data)) {
            if (data.code == 600) {

                console.log(data);
                var tables = $(data.position);
                tables.html("");
                if (!$.isEmptyObject(data)) {
                    if (data.hasOwnProperty("title")) {

                        html = "<thead class=\"text-uppercase bg-dark\">\n" +
                            "<tr class=\"text-white\">\n";

                        for (var k in data.title) {
                            html += '<th scope="col">' + data.title[k] + '</th>';
                        }

                        html += "<th scope=\"col\">操作</th></tr>\n" +
                            "</thead>\n" +
                            "<tbody>\n" +
                            "</tbody>";
                        $(data.position).html(html);

                    }

                    if (data.hasOwnProperty("data")) {

                        var t = data.hasOwnProperty("popcheck") ? data.popcheck : {};
                        for (var k in data.data) {

                            var html = '<tr id="sid_' + data.data[k].id + '">';
                            var tmps = data.data[k], statu_s = "";
                            for (var ks in tmps) {

                                //获取母参数据
                                var ssd = {};
                                var tps = ks.replace(ks[0], ks[0].toUpperCase());
                                if (t.hasOwnProperty(tps)) {
                                    ssd = t[tps].data;
                                }

                                if ($.inArray(ks, ['id']) != -1) {

                                    continue;
                                } else if (ks == 'permission_group') {
                                    html += '<td title="' + data.permission_group[tmps[ks]] + '" >' + data.permission_group[tmps[ks]] + '</td>';
                                } else if (ks == 'status') {
                                    html += '<td title="' + data.status[tmps[ks]] + '" >' + data.status[tmps[ks]] + '</td>';
                                    if (tmps[ks] == "1") {
                                        statu_s = '<button class="ti-bolt btn btn-danger mb-3 delete_data" style="margin-bottom: 0 !important;" status = 2 sid = "' + data.data[k].id + '"> 冻结</button>';
                                    } else {
                                        statu_s = '<button class="ti-wand btn btn-success mb-3 delete_data" style="margin-bottom: 0 !important;" status = 1 sid = "' + data.data[k].id + '"> 解除</button>';
                                    }
                                } else {

                                    //普通输出
                                    html += '<td title="' + tmps[ks] + '" >' + tmps[ks] + '</td>';
                                }

                            }

                            html += '<td>' + statu_s +
                                '<button class="ti-pencil-alt btn btn-secondary mb-3 update_data" style="margin-bottom: 0 !important;" sid = "' + data.data[k].id + '" data-toggle="modal" data-target="#myModal" > 修改</button>' +
                                '</td></tr>';

                            if ($.trim($(data.position + " tbody").html()) != "") {

                                if ($("#sid_" + data.data[k].parent_id).length > 0) {

                                    $("#sid_" + data.data[k].parent_id).after(html);
                                } else {

                                    $(data.position + " tbody").append(html);
                                }

                            } else {
                                $(data.position + " tbody").html(html);
                            }

                        }

                    }

                } else {

                    var html = '<tr>' +
                        '<td><span class="ti-reddit icon-name" style="margin: 0 auto;"> 暂无内容...</span></td>' +
                        '</tr>';
                    $(data.position).html(html);
                    console.log(data.message);
                }
            } else {
                console.log("内容为空!", data);
            }
        }
    }

</script>