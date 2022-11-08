<?php
/**
 * Created by PhpStorm.
 * User: zhangyu
 * Date: 2018/11/14
 * Time: 17:36
 */

namespace YYS\controller\template;

use components\Controller;
use components\Tool;
use YYS\model\template\ListModel;

class ListController extends Controller
{
    //title
    private $title = ["序号", "方案", "客户", "创建人", "创建日期"];

    /**
     * 列表视图
     * @return string
     */
    public function actionIndex()
    {
        $view = self::$server->get["view"] ?? "";
        $styleop = self::$server->get["styleop"] ?? "";
        $interfaceUrl = (in_array($view, ["listshow"])) ? '../list/' . $view . '.php?styleop=' . $styleop : '../list/list.php?view=' . $view . '&styleop=' . $styleop;
        $htmlurl = (in_array($view, ["listshow"])) ? '../list/' . $view . '.html' : '../list/index.html';
        return self::renderTempView("template/list/index.php", ['interfaceUrl' => $interfaceUrl, 'htmlurl' => $htmlurl, 'path' => '佑商爆销方案', 'title' => '爆销方案'], true, "");
    }

    /**
     * 视图内容
     * @return false|string
     */
    public function actionList()
    {
        $view = empty(self::$server->get["view"]) ? "show" : self::$server->get["view"];
        $styleop = self::$server->get["styleop"] ?? "";
        $parameter = json_encode(["styleop" => $styleop]);
        $data["position"] = ".main-content-inner";
        $data["data"] = self::renderView("template/list/{$view}.php", ['path' => '佑商爆销方案', 'title' => '爆销方案', "parameter" => $parameter], false);
        if (!empty($data["data"])) {

            $data["code"] = 600;
            $data["message"] = "成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "失败!";
        }

        return json_encode($data);
    }

    /**
     * 视图内容
     * @return false|string
     */
    public function actionListShow()
    {
        $where = $find = [];
        if (!empty(self::$server->post["Id"])) {

            $where["id"] = self::$server->post["Id"] ?? "";
            $find["id"] = "and";
        }
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        if (isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1") {
            $find["uid"] = "and";
            $where["uid"] = $uinfo['id'];
        }
        $temp = new ListModel(self::$server);
        $pdata = ["title" => $this->title, "data" => []];
        $pdata["data"] = $temp->Program($find, $where, "id,title,user,uid,uname,add_time");
        $data["position"] = ".main-content-inner";
        //var_dump($pdata);
        $data["data"] = self::renderView("template/list/listshow.php", ['path' => '佑商爆销方案', 'title' => '爆销方案', "data" => $pdata], false);
        if (!empty($data["data"])) {

            $data["code"] = 600;
            $data["message"] = "成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "失败!";
        }

        return json_encode($data);
    }

    /**
     * 添加
     * @return false|string
     */
    public function actionAdd()
    {
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $POSTDATA = Tool::getPostdata(self::$server->post);
        $request["user"] = $POSTDATA["User"] ?? "";
        $request["title"] = $POSTDATA["Title"] ?? "";
        $request["template_ids"] = self::$server->post["Template_ids"] ?? "";
        $request["template_ids"] = empty($request["template_ids"]) ? "" : json_encode($request["template_ids"]);
        $request["additional_content"] = $POSTDATA["Additional_content"] ?? "";
        $request["uid"] = $uinfo['id'];
        $request["uname"] = $uinfo['username'];

        $temp = new ListModel(self::$server);
        $data["data"] = $temp->Add($request);
        if ($data["data"]) {

            $data["code"] = 600;
            $data["message"] = "成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "失败!";
        }

        return json_encode($data);
    }

    /**
     * 查看方案
     * @return false|string
     */
    public function actionProgram()
    {
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        if (isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1") {
            $find["uid"] = "and";
            $where["uid"] = $uinfo['id'];
        }
        $find["id"] = "and";
        $where["id"] = self::$server->post["Id"] ?? "";
        $temp = new ListModel(self::$server);
        $rdata = $temp->Program($find, $where, "title,additional_content,add_time");
        if (isset($rdata[0])) {
            $data["URL"] = "../../tourist/index/list.php?TouristId=" . self::SetPublicId($where["id"], strtotime($rdata[0]["add_time"]));
            if ($rdata[0]['additional_content']) {
                $temp = self::renderTempView("template/tourist/index_{$where["id"]}.php", ["rdata" => $rdata[0], 'title' => $rdata[0]['title'] . '方案'], true, "template/tourist/index.php");
                if (!empty($temp)) {
                    $data["data"] = true;
                }
            }
        }

        if ($t =& $data["data"]) {

            $data["code"] = 600;
            $data["message"] = "成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "失败!";
        }
        return json_encode($data);
    }

    /**
     * 删除
     * @return false|string
     */
    public function actionDelete()
    {
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        if (isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1") {
            $where["uid"] = $uinfo['id'];
        }
        $where["id"] = self::$server->post["Id"] ?? "";

        if (isset($where["id"])) {
            $temp = new ListModel(self::$server);
            $data["data"] = $temp->Delete($where);
            if ($data["data"]) {

                $data["code"] = 600;
                $data["message"] = "成功!";
            } else {

                $data["code"] = 605;
                $data["message"] = "失败!";
            }
        } else {
            $data["code"] = 601;
            $data["message"] = "失败!找不到需要删除的数据.";
            $data["data"] = "";
        }

        return json_encode($data);
    }

    /**
     * 获取内容
     * @return false|string
     */
    public function actionGetParent()
    {
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $find = $where = [];
        if (isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1") {
            $find["uid"] = "and";
            $where["uid"] = $uinfo['id'];
        }
        $temp = new ListModel(self::$server);
        $data["Modal"] = ".table-responsive table";
        $data["Users"] = ["用户", "客户", "合作商", "合作伙伴", "商户", "企业", "合作企业", "政客"];
        $data["styleop"] = self::$server->post["styleop"] ?? "";;
        $data["data"] = $temp->Index($find, $where);

        if (count($data["data"])) {

            $data["code"] = 600;
            $data["message"] = "成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "失败!";
        }
        return json_encode($data);
    }

}