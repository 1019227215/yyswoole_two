<?php

namespace YYs\controller\auth;

use components\Controller;
use components\Tool;
use YYS\model\auth\RightsModel;

class RightsController extends Controller
{
    //表头
    private $title = ["Uname" => "用户名", "Email" => "邮箱", "Password" => "密码", "Password1" => "确认密码", "Permission_group" => "权限组", "Status" => "状态"];

    /**
     * 视图内容
     * @return false|string
     */
    public function actionList()
    {
        $interfaceUrl = '../rights/showList.php';//数据路由
        $htmlurl = '../auth/rights/list.php';//左边导航选中路由
        $iUrl = "template/auth/index.php";//主页面路由
        $pathUrl = "/rights.php";//内容切片页面
        $func = "setTable";
        return self::renderView($iUrl, ['interfaceUrl' => $interfaceUrl, 'htmlurl' => $htmlurl, 'pathUrl' => $pathUrl, 'func' => $func, 'path' => '用户管理页面', 'title' => '用户管理页面']);
    }

    /**
     * 视图内容
     * @return false|string
     */
    public function actionShowList()
    {
        $where = $find = [];
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        if (!empty(self::$server->post["Id"])) {
            $where["id"] = self::$server->post["Id"] ?? "";
            $find["id"] = "or";
            if (isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1") {
                $where["permission_group"] = 1;
                $find["permission_group"] = "or";
                $where["permission_group_s"] = $uinfo['id'];
            }

        } else {

            if (isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1") {
                $find["id"] = "or";
                $where["id"] = $uinfo['id'];
                $where["permission_group"] = 1;
                $find["permission_group"] = "or";
                $where["permission_group_s"] = $uinfo['id'];
            }
        }

        $temp = new RightsModel(self::$server);
        unset($this->title["Password"]);
        unset($this->title["Password1"]);
        $data = ["title" => $this->title, "data" => []];
        $data["data"] = $temp->GetParent($find, $where, "all", "id,`name`,parent_id,status,model,path,uid,created_at");
        $pub = self::GetPulicContent();
        $data["status"] = $pub["status"];
        $data["position"] = ".table-responsive table";
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
     * 添加用户
     * @return false|string
     */
    public function actionAdd()
    {
        $data["URL"] = "../../template/index/index.php";
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $POSTDATA = Tool::getPostdata(self::$server->post, ["Cssd" => "|"]);;//获取post参数,转成1维数组
        $request["name"] = $POSTDATA["Name"] ?? "";
        $request["parent_id"] = $POSTDATA["Parent_id"] ?? 0;
        $request["status"] = $POSTDATA["Status"] ?? 1;
        $request["path"] = $POSTDATA["Path"] ?? "";
        $request["model"] = $POSTDATA["Model"] ?? "";
        $request["uid"] = $uinfo['id'];
        $request["created_at"] = time();
        $request["updated_at"] = $request["created_at"];
        if (!empty($request["name"])) {


            $amodel = new  RightsModel(self::$server);;
            $data["data"] = $amodel->Add($request);

        }

        if ($t =& $data["data"]) {

            $data["code"] = 600;
            $data["message"] = "成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "注册失败!缺少数据!";
        }

        return json_encode($data);
    }

    /**
     * 修改用户
     * @return false|string
     */
    public function actionUpdate()
    {
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $POSTDATA = Tool::getPostdata(self::$server->post, ["Cssd" => "|"]);;//获取post参数,转成1维数组
        $where["id"] = $POSTDATA["Id"] ?? "";
        $request["name"] = $POSTDATA["Name"] ?? "";
        $request["parent_id"] = $POSTDATA["Parent_id"] ?? 0;
        $request["status"] = $POSTDATA["Status"] ?? 1;
        $request["path"] = $POSTDATA["Path"] ?? "";
        $request["model"] = $POSTDATA["Model"] ?? "";
        $request["uid"] = $uinfo['id'];
        $request["created_at"] = time();
        $request["updated_at"] = $request["created_at"];

        if (!empty($where["id"])) {

            $amodel = new  RightsModel(self::$server);;
            $data["data"] = $amodel->Update($request, $where);
        }

        if ($t =& $data["data"]) {

            $data["code"] = 600;
            $data["message"] = "成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "修改失败!";
        }

        return json_encode($data);
    }

    /**
     * 禁用/冻结/锁定等
     * @return false|string
     */
    public function actionDisable()
    {
        $POSTDATA = Tool::getPostdata(self::$server->post, ["Cssd" => "|"]);
        $request["status"] = $POSTDATA["Status"] ?? "";
        $request["updated_at"] = time();
        $where["id"] = $POSTDATA["Id"] ?? "";

        if (!empty($where["id"]) && !empty($request["status"])) {
            $amodel = new  RightsModel(self::$server);
            $data["data"] = $amodel->Disable($request, $where);
            if ($t =& $data["data"]) {

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
        $where = ["id" => $id];
        if (empty($id)) {
            if (isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1") {
                $where["uid"] = $uinfo['id'];
                $find["uid"] = " and ";
            }
        }

        if (!empty($id)) {
            $temp = new RightsModel(self::$server);
            $ck = $temp->GetParent($find, $where, "one", "id,`name`,parent_id,status,model,path,uid,created_at");
            if (!empty($ck)) {

                $data["checkdata"] = $ck;
            }
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

    /**
     * 获取弹窗内容
     * @return mixed
     */
    public function GetPopContent()
    {
        $temp = new RightsModel(self::$server);
        $pub = self::GetPulicContent();
        $token = self::$server->cookie["token"];
        $uinfo = self::Getuserinfo($token);
        $data["Password"] = [
            "type" => "password",
        ];
        $data["Password1"] = [
            "type" => "password",
        ];
        $data["Permission_group"] = [
            "type" => "select",
            "style" => 'data-placeholder="请选择..." class="standardSelect" tabindex="1"',
            "data" => $temp->GetGroup($uinfo["id"])
        ];
        $data["Status"] = [
            "type" => "select",
            "style" => 'data-placeholder="请选择..." class="standardSelect" tabindex="1"',
            "data" => $pub["status"]
        ];
        return $data;
    }

}