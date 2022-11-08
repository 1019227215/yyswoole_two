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
use YYS\model\template\TemplateModel;

class IndexController extends Controller
{
    /**
     * title
     * @var array
     */
    private $title = ["Parent_id" => "层级", "Name" => "內容", "Title" => "说明", "Cssd" => "样式", "Typed" => "类型"];

    /**
     * 列表视图
     * @return string
     */
    public function actionIndex()
    {
        return self::renderTempView("template/table/index.php", ['interfaceUrl' => '../index/list.php', 'htmlurl' => '../table/index.html', 'path' => '模板管理页', 'title' => '后台页面']);
    }

    /**
     * 视图内容
     * @return false|string
     */
    public function actionList()
    {
        $data["position"] = ".main-content-inner";
        $data["data"] = self::renderView("template/table/index.php", ['path' => '模板管理页', 'title' => '后台页面'], false);
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
     * 获取弹窗内容
     * @return mixed
     */
    public function GetPopContent()
    {
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $find = $where = [];
        if(isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1"){
            $find = ["uid" => "and"];
            $where = [$uinfo['id']];
        }
        $temp = new TemplateModel(self::$server);
        $pub = self::GetPulicContent();
        $data["Cssd"] = [
            "type" => "standardSelect",
            "style" => 'data-placeholder="请选择..." multiple class="standardSelect"',
            "data" => $pub["cssd"]];
        $data["Typed"] = [
            "type" => "select",
            "style" => 'data-placeholder="请选择..." class="standardSelect" tabindex="1"',
            "data" => $pub["typed"]];
        $data["Parent_id"] = [
            "type" => "select",
            "style" => 'data-placeholder="请选择..." class="standardSelect" tabindex="1"',
            "data" => $temp->GetParent($find, $where)];
        return $data;
    }

    /**
     * 获取公共配置
     * @param string $t
     * @return mixed|string
     */
    public function GetPulicContent($t = "", $file = 'public_content')
    {
        $data = Tool::setConfig($file, false, OBJ . "/config/");
        return empty($t) ? $data : $data[$t];
    }

    /**
     * 视图内容
     * @return false|string
     */
    public function actionListData()
    {
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $find = $where = [];
        if ((isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1")) {
            $find = ["uid" => "and"];
            $where = [$uinfo['id']];
        }
        $temp = new TemplateModel(self::$server);
        $popdata = self::GetPopContent();
        $popdata["Parent_like"]["data"] = Tool::PopArray($popdata["Parent_id"]["data"], "id", "name");
        $data = ["title" => $this->title, "popcheck" => $popdata];
        $data["position"] = ".table-responsive table";
        $data["data"] = $temp->Index($find, $where);
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
        $POSTDATA = Tool::getPostdata(self::$server->post, ["Cssd" => "|"]);
        $request["name"] = $POSTDATA["Name"] ?? "";
        $request["title"] = $POSTDATA["Title"] ?? "";
        $request["parent_id"] = empty($POSTDATA["Parent_id"]) ? 0 : $POSTDATA["Parent_id"];
        $request["type"] = $POSTDATA["Typed"] ?? "";
        $request["css"] = $POSTDATA["Cssd"] ?? "";
        $request["parent_like"] = ($request["parent_id"] == 0) ? "|{$request["parent_id"]}|" : $POSTDATA["Parent_like"] . "-|{$request["parent_id"]}|";
        $request["uid"] = $uinfo['id'];
        $request["uname"] = $uinfo['username'];
        $request["update_time"] = date("Y-m-d H:i:s", time());

        $temp = new TemplateModel(self::$server);
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
     * 修改
     * @return false|string
     */
    public function actionUpdate()
    {
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $POSTDATA = Tool::getPostdata(self::$server->post, ["Cssd" => "|"]);
        $request["name"] = $POSTDATA["Name"] ?? "";
        $request["title"] = $POSTDATA["Title"] ?? "";
        $request["parent_id"] = empty($POSTDATA["Parent_id"]) ? 0 : $POSTDATA["Parent_id"];
        $request["type"] = $POSTDATA["Typed"] ?? "";
        $request["css"] = $POSTDATA["Cssd"] ?? "";
        $request["parent_like"] = ($request["parent_id"] == 0) ? "|{$request["parent_id"]}|" : $POSTDATA["Parent_like"] . "-|{$request["parent_id"]}|";
        $request["uid"] = $uinfo['id'];
        $request["uname"] = $uinfo['username'];

        $where["id"] = $POSTDATA["Id"] ?? "";
        (isset($uinfo["permission_group"]) && empty($uinfo['permission_group']) && $uinfo['status'] == "1") ? "" : ($where["uid"] = $uinfo['id']);

        if (isset($where["id"])) {
            $temp = new TemplateModel(self::$server);
            $data["data"] = $temp->Update($request, $where);
            if ($data["data"]) {

                $data["code"] = 600;
                $data["message"] = "成功!";
            } else {

                $data["code"] = 605;
                $data["message"] = "失败!";
            }
        } else {
            $data["code"] = 601;
            $data["message"] = "失败!找不到需要修改的数据.";
            $data["data"] = "";
        }

        return json_encode($data);
    }

    /**
     * 删除
     * @return false|string
     */
    public function actionDelete()
    {
        $where["id"] = self::$server->post["Id"] ?? "";
        $where["parent_like"] = $where["id"];
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        (isset($uinfo["permission_group"]) && empty($uinfo['permission_group']) && $uinfo['status'] == "1") ? "" : ($where["uid"] = $uinfo['id']);

        if (isset($where["id"])) {
            $temp = new TemplateModel(self::$server);
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
     * 获取弹窗内容
     * @return false|string
     */
    public function actionGetParent()
    {
        $data["Modal"] = "#myModal .modal-body";
        $data["content"] = $this->title;
        $data["data"] = self::GetPopContent();
        $id = self::$server->post["Id"] ?? "";
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $find = ["id" => "and"];
        $where = [$id];
        if(isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1"){
            $find = ["id" => "and", "uid" => "and"];
            $where = [$id, $uinfo['id']];
        }
        if (!empty($id)) {
            $temp = new TemplateModel(self::$server);
            $data["checkdata"] = $temp->GetParent($find, $where, "id,parent_id,parent_like,name,title,css cssd,type typed")[0];
        }

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