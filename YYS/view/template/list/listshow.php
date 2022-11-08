<style type="text/css">

    #tooltip {
        position: absolute;
        display: none;
    }

    .tables {
        border: 1px solid #dee2e6 !important;
    }

    .tables td, th {
        vertical-align: middle !important;
        border-right: 1px solid #dee2e6 !important;
    }

    .tables tbody tr td:first-child {
        width: 30%;
    }

    .tables_none td {
        border-top: 0 !important;
    }

    td span, input, th span, input {
        background-color: #fff !important;
        border: 0 !important;
    }

    td input, th input {
        border-bottom: 1px solid #ced4da !important;
    }

</style>
<div class="row">
    <!-- table dark start -->
    <div class="col-lg-6 mt-5" style="flex: none; max-width: 100%;">
        <div class="card">
            <div class="card-body">
                <div style="width: 100%;">
                    <h4 class="header-title"><?php echo $path; ?></h4>
                </div>
                <div class="single-table">
                    <div class="table-responsive">
                        <table class="table text-center">
                            <thead class="text-uppercase bg-dark">
                            <tr class="text-white">
                                <?php
                                if (isset($data["title"]) && count($data["title"]) > 0) {
                                    foreach ($data["title"] as $value) {
                                        echo '<th scope="col">' . $value . '</th>';
                                    }
                                    echo '<th scope="col">操作</th>';
                                }
                                ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            // id,title,user,uid,uname,add_time
                            if (isset($data["data"]) && count($data["data"]) > 0) {

                                $t = &$data['popcheck'];
                                foreach ($data["data"] as $ks => $value) {
                                    echo '<tr><td title="' . $ks . '" >' . $ks . '</td>';
                                    foreach ($value as $k => $v) {

                                        //获取母参数据
                                        $ssd = [];
                                        if (isset($t[ucfirst($k)])) {
                                            $ssd = $t[ucfirst($k)]['data'];
                                        }

                                        if ($k == 'id' || $k == 'uid') {

                                            continue;
                                        } else {
                                            //普通输出
                                            echo '<td title="' . $v . '" >' . $v . '</td>';
                                        }
                                    }
                                    echo '
                                                <td>
                                                    <button class="ti-trash btn btn-danger mb-3 delete_data" style="margin-bottom: 0 !important;" sid = "' . $value["id"] . '"> 删除</button>
                                                    <button class="fa fa-space-shuttle btn btn-primary mb-3 show_data" style="margin-bottom: 0 !important;" sid = "' . $value["id"] . '" > 查看方案</button>
                                                </td>
                                            </tr>';
                                }
                            } else {
                                echo '<tr>
                                        <td colspan="' . (count($data["title"]) + 1) . '"><span class="ti-reddit icon-name" style="margin: 0 auto;"> 暂无内容...</span></td>
                                    </tr>';
                            }
                            ?>
                            </tbody>
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

        //修改模态框
        $('.show_data').on('click', function () {

            var data = {"Id": $(this).attr("sid")};
            //加载页面内容
            query("../list/Program.php", show, data);
        });

        //删除数据
        $('.delete_data').on('click', function () {

            var data = {"Id": $(this).attr("sid")};
            query("../list/delete.php", result, data);
        });

        function show(data) {

            if (!$.isEmptyObject(data)) {
                if (data.code == 600) {

                    //document.location.href = data.URL;
                    window.open(data.URL);
                } else {
                    console.log(data.message);
                }
            } else {
                console.log("内容为空!", data);
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
            query("../list/listshow.php", load);
        }

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

    });

    /**
     * base64加密解密
     * @constructor
     */
    var Base64 = {
        _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
        encode: function (e) {
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
        decode: function (e) {
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
        _utf8_encode: function (e) {
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
        _utf8_decode: function (e) {
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