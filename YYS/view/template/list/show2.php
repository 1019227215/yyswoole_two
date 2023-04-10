<style type="text/css">


    .tables {
        border: 1px solid #dee2e6 !important;
    }

    .tables td,th {
        vertical-align: middle !important;
        border-right: 1px solid #dee2e6 !important;
        /*word-wrap:break-word;word-break:break-all;*/
    }

    .tables tbody tr td:first-child{
        width: 30%;
    }

    td span,input,th span,input {
        background-color: #fff !important;
        border: 0 !important;
    }

    td input,th input {
        border-bottom: 1px solid #ced4da !important;
    }

</style>
<div class="row">
    <!-- table dark start -->
    <div class="col-lg-6 mt-5" style="flex: none; max-width: 100%;">
        <div class="card">
            <form id="form2">
                <div class="card-body">
                    <h4 class="header-title" style="text-align: center;font-size: 28px;"><?php echo $path; ?></h4>
                    <div class="single-table">
                        <div class="table-responsive">
                            <table class="table table-hover text-center tables">

                            </table>
                        </div>
                    </div>
                </div>
            </form>
            <div class="btn-group mb-xl-3" role="group" aria-label="Basic example">
                <button class="btn btn-success" type="button" style="width: 50%;" id="Refresh">刷新</button>
                <button class="btn btn-primary" type="button" style="width: 50%;" id="AddPost">预览</button>
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

        /**
         * 刷新
         */
        function Refresh(){

            //加载页面内容
            query("../list/getParent.php", setData,<?php echo $parameter;?>);
        }

        /**
         * 刷新数据
         */
        $("#Refresh").click(function(){

            //加载页面内容
            Refresh();
        });

        /**
         * 保存数据
         */
        $("#AddPost").click(function () {

            if ($(this).html() == "保存"){

                var data = {};
                data["User"] = $("#User").val();
                data["Title"] = $("thead").eq(0).find("th").html();
                data["Template_ids"] = $("#form2").serializeArray();
                data["Additional_content"] = Base64.encode($("#form2").html());

                //保存数据
                query("../list/add.php", result, data);
            }else{

                $(this).html("保存");
                $("td div").css("display", "none");
                $("#form2 input").each(function(){
                    if($.inArray($(this).attr("type"),["radio","checkbox"]) != -1){
                        if($(this).prop('checked')) {
                            $(this).parent("div").css("display", "block");
                            $(this).attr({"disabled": "disabled","checked":"checked"});
                        }
                    }else{
                        $(this).parent("div").css("display","block");
                        $(this).prev("span").css("float","left");
                        var htmls = "<div style=\"min-width: 50%;min-height: 36px;border-bottom: 1px solid #ced4da;float: left;\">"+$(this).val()+"</div><div style=\"clear: both;\"></div>";
                        $(this).before(htmls).remove();
                    }
                });
                $("td div:hidden").remove();
            }

        });


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
            location.href = "../list/index.php?view=listshow";
        }

        /**
         * 模态框回调
         * @param data
         */
        function setData(data) {

            if (!$.isEmptyObject(data)) {
                if (data.code == 600) {

                    var tables = $(data.Modal);
                    tables.html("");
                    if (!$.isEmptyObject(data.data)) {

                        var tops = [];
                        for (var k in data.data) {

                            if (!$.isEmptyObject(data.data[k])) {

                                var temp = data.data[k];
                                var titles = (temp.title != "") ? temp.name + "(" + temp.title + ")" : temp.name;
                                var kname = "c_" + temp.id + "_" + temp.parent_id;
                                var cssd = (temp.cssd != "" && data.styleop == 'open') ? temp.cssd : "";
                                var trtd = temp.parent_like.split("-");

                                if (temp.typed == "radio") {
                                    //var tpid = $("[ctd='" + temp.parent_id + "']").parent("tr").attr("ctr");
                                    kname = (trtd.hasOwnProperty(2) && "" != trtd[2]) ? {
                                        "id": kname,
                                        "name": "c_" + trtd[2]
                                    } : {"id": kname, "name": "c_" + temp.id};
                                }

                                if (temp.parent_id == "0") {

                                    tops.push(temp.id);
                                    var html = "<thead class=\"text-uppercase bg-light\" phead='" + temp.id + "'>\n" +
                                        "<tr>\n" +
                                        "    <th scope=\"col\" style='" + cssd + "' colspan=\"2\">" + titles + "</th>\n" +
                                        "</tr>\n" +
                                        "</thead>\n" +
                                        "<tbody pbody='" + temp.id + "'>\n</tbody>\n";
                                    tables.append(html);
                                } else {

                                    if ($.inArray(temp.parent_id, tops) != -1) {

                                        var label = getLabel(temp.typed, titles, kname);
                                        var html = "<tr ctr='" + temp.id + "' ptr='" + temp.parent_id + "'>\n" +
                                            "<td style='" + cssd + "' ctd = '" + temp.id + "' ptd='" + temp.parent_id + "' rowspan=\"1\" " + cssd + ">" + label + "</td>\n" +
                                            "<td style='" + cssd + "' ctd = '" + temp.id + "_s' ptd='" + temp.parent_id + "_s' rowspan=\"1\"></td>\n" +
                                            "</tr>";

                                        $("[pbody = '" + temp.parent_id + "']").append(html);
                                    } else {

                                        var num = $("[ctd = '" + trtd[2] + "_s']").children("div").length;
                                        var label = getLabel(temp.typed, titles, kname,cssd);
                                        if (num % 3 == 0 && num > 0){
                                            label = "<div style='clear:both;'></div>"+label;
                                        }
                                        $("[ctd = '" + trtd[2] + "_s']").append(label);
                                    }

                                }

                            }

                        }

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

        //加载页面内容
        Refresh();

    });

    /**
     * 模板标签
     * @param t
     * @param n
     * @param f
     * @param attr
     * @returns {string}
     */
    function getLabel(t, n, f, attr = "") {

        html = "";
        switch (t) {
            case "text":
                html = "<div class=\"input-group-prepend\">\n" +
                    "        <span class=\"input-group-text\" id=\"" + f + "_s\">" + n + "</span>\n" +
                    "        <input type=\"text\" " + attr + " id=\"" + f + "\" name=\"" + f + "\" class=\"form-control\" placeholder=\"" + n + "\" aria-label=\"" + f + "\" aria-describedby=\"" + f + "_s\">\n" +
                    "    </div>\n";
                break;
            case "radio":
                html = "<div class=\"custom-control custom-radio\" style=\"text-align: left;\">\n" +
                    "    <input type=\"radio\" id=\"" + f.id + "\" " + attr + " name=\"" + f.name + "\"  class=\"custom-control-input\">\n" +
                    "    <label class=\"custom-control-label\" for=\"" + f.id + "\">" + n + "</label>\n" +
                    "</div>";
                break;
            case "checkbox":
                html = "<div class=\"custom-control custom-checkbox\" style=\"text-align: left;\">\n" +
                    "    <input type=\"checkbox\" class=\"custom-control-input\" " + attr + " id=\"" + f + "\" name='" + f + "'>\n" +
                    "    <label class=\"custom-control-label\" for=\"" + f + "\">" + n + "</label>\n" +
                    "</div>";
                break;
            case "select":
                html = "<div class=\"input-group\"><select data-placeholder=\"Choose a Country...\" " + attr + " id=\"" + f + "\" name='" + f + "' class=\"standardSelect\" tabindex=\"1\"><option value=\"\"></option>";
                for (var k in n) {
                    html += "<option value=\"" + k + "\">" + n[k] + "</option>";
                }
                html += "</select></div>";
                break;
            case "standardSelect":
                html = "<div class=\"input-group\"><select data-placeholder=\"Choose a Country...\" " + attr + " id=\"" + f + "\" name='" + f + "' multiple class=\"standardSelect\" tabindex=\"1\"><option value=\"\"></option>";
                for (var k in n) {
                    html += "<option value=\"" + k + "\">" + n[k] + "</option>";
                }
                html += "</select></div>";
                break;
            case "email":
                html = "    <div class=\"input-group-prepend\">\n" +
                    "        <span class=\"input-group-text\" id=\"" + f + "_s\">" + n + "</span>\n" +
                    "        <input type=\"email\" " + attr + " id=\"" + f + "\" name=\"" + f + "\" class=\"form-control\" placeholder=\"" + n + "\" aria-label=\"" + f + "\" aria-describedby=\"" + f + "_s\">\n" +
                    "    </div>\n";
                break;
            case "tel":
                html = "    <div class=\"input-group-prepend\">\n" +
                    "        <span class=\"input-group-text\" id=\"" + f + "_s\">" + n + "</span>\n" +
                    "        <input type=\"tel\" " + attr + " id=\"" + f + "\" name=\"" + f + "\" class=\"form-control\" placeholder=\"" + n + "\" aria-label=\"" + f + "\" aria-describedby=\"" + f + "_s\">\n" +
                    "    </div>\n";
                break;
            case "textarea":
                html = "    <div class=\"input-group-prepend\">\n" +
                    "        <span class=\"input-group-text\" id=\"" + f + "_s\">" + n + "</span>\n" +
                    "        <textarea class=\"form-control\" " + attr + " placeholder='" + n + "' id='" + f + "' name='" + f + "' rows=\"9\" aria-label=\"" + f + "\" aria-describedby=\"" + f + "_s\"></textarea>" +
                    "    </div>\n";
                break;
            case "datetime":
                html = "    <div class=\"input-group-prepend\">\n" +
                    "        <span class=\"input-group-text\" id=\"" + f + "_s\">" + n + "</span>\n" +
                    "        <input type=\"datetime-local\" " + attr + " id=\"" + f + "\" name=\"" + f + "\" class=\"form-control\" placeholder=\"" + n + "\" aria-label=\"" + f + "\" aria-describedby=\"" + f + "_s\">\n" +
                    "    </div>\n";
                break;
            case "file":
                html = "    <div class=\"input-group-prepend\">\n" +
                    "        <span class=\"input-group-text\" id=\"" + f + "_s\">" + n + "</span>\n" +
                    "        <input type=\"file\" " + attr + " id=\"" + f + "\" name=\"" + f + "\" class=\"form-control\" placeholder=\"" + n + "\" aria-label=\"" + f + "\" aria-describedby=\"" + f + "_s\">\n" +
                    "    </div>\n";
                break;
            case "image":
                html = "<div class=\"input-group\"><img src=\"" + f + "\" alt=\"" + n + "\" " + attr + " ></div>";
                break;
            default:
                html = "<dd style=\"width: 100%;text-align: left;\" id=\"" + f + "\" " + attr + ">" + n + "</dd>";
                break;
        }
        return html;
    }


    /**
     * base64加密解密
     * @constructor
     */
    var Base64 = {
        _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
        encode: function(e) {
            var t = "";
            var n, r, i, s, o, u, a;
            var f = 0;
            e = Base64._utf8_encode(e);
            while (f < e.length) {
                n = e.charCodeAt(f++);
                r = e.charCodeAt(f++);
                i = e.charCodeAt(f++);
                s = n >> 2;
                o = (n & 3) << 4 | r >> 4;
                u = (r & 15) << 2 | i >> 6;
                a = i & 63;
                if (isNaN(r)) {
                    u = a = 64
                } else if (isNaN(i)) {
                    a = 64
                }
                t = t + this._keyStr.charAt(s) + this._keyStr.charAt(o) + this._keyStr.charAt(u) + this._keyStr.charAt(a)
            }
            return t
        },
        decode: function(e) {
            var t = "";
            var n, r, i;
            var s, o, u, a;
            var f = 0;
            e = e.replace(/[^A-Za-z0-9+/=]/g, "");
            while (f < e.length) {
                s = this._keyStr.indexOf(e.charAt(f++));
                o = this._keyStr.indexOf(e.charAt(f++));
                u = this._keyStr.indexOf(e.charAt(f++));
                a = this._keyStr.indexOf(e.charAt(f++));
                n = s << 2 | o >> 4;
                r = (o & 15) << 4 | u >> 2;
                i = (u & 3) << 6 | a;
                t = t + String.fromCharCode(n);
                if (u != 64) {
                    t = t + String.fromCharCode(r)
                }
                if (a != 64) {
                    t = t + String.fromCharCode(i)
                }
            }
            t = Base64._utf8_decode(t);
            return t
        },
        _utf8_encode: function(e) {
            e = e.replace(/rn/g, "n");
            var t = "";
            for (var n = 0; n < e.length; n++) {
                var r = e.charCodeAt(n);
                if (r < 128) {
                    t += String.fromCharCode(r)
                } else if (r > 127 && r < 2048) {
                    t += String.fromCharCode(r >> 6 | 192);
                    t += String.fromCharCode(r & 63 | 128)
                } else {
                    t += String.fromCharCode(r >> 12 | 224);
                    t += String.fromCharCode(r >> 6 & 63 | 128);
                    t += String.fromCharCode(r & 63 | 128)
                }
            }
            return t
        },
        _utf8_decode: function(e) {
            var t = "";
            var n = 0;
            var r = c1 = c2 = 0;
            while (n < e.length) {
                r = e.charCodeAt(n);
                if (r < 128) {
                    t += String.fromCharCode(r);
                    n++
                } else if (r > 191 && r < 224) {
                    c2 = e.charCodeAt(n + 1);
                    t += String.fromCharCode((r & 31) << 6 | c2 & 63);
                    n += 2
                } else {
                    c2 = e.charCodeAt(n + 1);
                    c3 = e.charCodeAt(n + 2);
                    t += String.fromCharCode((r & 15) << 12 | (c2 & 63) << 6 | c3 & 63);
                    n += 3
                }
            }
            return t
        }
    }

</script>