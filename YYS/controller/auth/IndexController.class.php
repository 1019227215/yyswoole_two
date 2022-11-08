<?php

namespace YYs\controller\auth;

use components\Controller;
use components\Tool;
use YYS\model\auth\AuthModel;

class IndexController extends Controller
{
    //表头
    private $title = ["Uname"=>"用户名", "Email"=>"邮箱","Password"=>"密码","Password1"=>"确认密码", "Permission_group"=>"权限组", "Status"=>"状态"];

    /**
     * 视图内容
     * @return false|string
     */
    public function actionList()
    {
        $interfaceUrl = '../index/userList.php';//数据路由
        $htmlurl = '../auth/index/list.php';//左边导航选中路由
        $iUrl = "template/auth/index.php";//主页面路由
        $pathUrl = "/list.php";//内容切片页面
        $func = "setTable";
        return self::renderView($iUrl, ['interfaceUrl' => $interfaceUrl, 'htmlurl' => $htmlurl, 'pathUrl' => $pathUrl, 'func' => $func, 'path' => '用户管理页面', 'title' => '用户管理页面']);
    }

    /**
     * 视图内容
     * @return false|string
     */
    public function actionUserList()
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

        $temp = new AuthModel(self::$server);
        unset($this->title["Password"]);
        unset($this->title["Password1"]);
        $data = ["title" => $this->title, "data" => []];
        $data["data"] = $temp->GetParent($find, $where, "all", "id,username,email,permission_group,status");
        $data["permission_group"] = ["超级管理员"] + $temp->Getpermission_group($uinfo['id']);
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
     * 查找用户是否存在
     * @return false|string
     */
    public function actionCheckuser()
    {
        $username = self::$server->post["Uname"] ?? "";
        $email = self::$server->post["Email"] ?? "";
        $find = $where = [];
        if (!empty($username)) {
            $find += ["username" => "or"];
            array_push($where, $username);
        }
        if (!empty($email)) {
            $find += ["email" => "or"];
            array_push($where, $email);
        }

        if (count($find) && count($where)) {

            $amodel = new  AuthModel(self::$server);
            $data["data"] = $amodel->Checkuser($find, $where);
        }

        if ($t =& $data["data"] && empty($t['num'])) {

            $data["code"] = 600;
            $data["message"] = "成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "不可使用,已存在!";
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
        $POSTDATA = Tool::getPostdata(self::$server->post, ["Cssd" => "|"]);;//获取post参数,转成1维数组
        $Browser = self::$server->header['user-agent'] ?? "";//浏览器信息
        $IP = self::$server->server['remote_addr'] ?? "";//访问ip
        if (!empty($POSTDATA["Email"]) && !empty($POSTDATA["Uname"]) && !empty($POSTDATA["Password"]) && !empty($Browser) && !empty($IP) && $IP != '0.0.0.0') {

            $request["auth_key"] = md5(base64_encode($POSTDATA["Email"] . $IP . $Browser));//auth(身份证)只在注册时生成,后续不再修改
            $request["username"] = $POSTDATA["Uname"] ?? "";
            $request["email"] = $POSTDATA["Email"] ?? "";
            $request["permission_group"] = $POSTDATA["Permission_group"] ?? 1;
            $request["status"] = $POSTDATA["Status"] ?? 1;
            $request["created_at"] = time();
            $request["updated_at"] = $request["created_at"];
            $request["password"] = $POSTDATA["Password"] ?? "";;

            $amodel = new  AuthModel(self::$server);
            $token = $amodel->Getpasswordandtoken($request);
            $request["password_hash"] = $token['password'];
            $request["password_reset_token"] = $token['token'];
            unset($request["password"]);
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
        $POSTDATA = Tool::getPostdata(self::$server->post, ["Cssd" => "|"]);
        $token = self::$server->cookie["token"] ?? "";
        if (!empty($token)) {

            $amodel = new  AuthModel(self::$server);
            $request["updated_at"] = time();
            $userinfo = [];

            //管理员修改密码
            if ($Id =& $POSTDATA["Id"]) {

                ($Uname =& $POSTDATA["Uname"]) ? ($request["username"] = $Uname) : "";
                ($Permission_group =& $POSTDATA["Permission_group"]) ? ($request["permission_group"] = $Permission_group) : "";
                ($Status =& $POSTDATA["Status"]) ? ($request["status"] = $Status) : "";
                //($Email =& $POSTDATA["Email"]) ? ($request["email"] = $Email) : "";

                $admininfo = self::Getuserinfo($token);
                $adminpermission_group = $amodel->Getpermission_group($admininfo['id']);
                $adminpermission_group = array_keys($adminpermission_group);
                $userinfo = self::Getuserinfo_id($Id);

                if ($Password =& $POSTDATA['Password']) {
                    $userinfo['password'] = $Password;
                    $token = $amodel->Getpasswordandtoken($userinfo);
                    $request["password_hash"] = $token['password'];
                    $request["password_reset_token"] = $token['token'];
                }

                if (!in_array($userinfo['permission_group'], $adminpermission_group)) {
                    $userinfo = [];
                }

            } else {

                //用户自己修改密码
                if (($Password =& $POSTDATA['Password']) && ($Passwordnew =& $POSTDATA["Passwordnew"])) {

                    $userinfo = self::Getuserinfo($token);
                    $userinfo['password'] = $Password;
                    $token = $amodel->Getpasswordandtoken($userinfo);
                    $userinfo['password'] = $Passwordnew;
                    $newtoken = $amodel->Getpasswordandtoken($userinfo);
                    $request["password_hash"] = $newtoken['password'];
                    $request["password_reset_token"] = $newtoken['token'];

                    //判断旧密码是否正常
                    if ($token['password'] != $userinfo['password_hash']) {
                        $userinfo = [];
                    }
                }
            }

            if (!empty($userinfo)) {
                $where["id"] = $userinfo["id"] ?? "";
                $data["data"] = $amodel->Update($request, $where);
            }

            if ($t =& $data["data"]) {

                $data["code"] = 600;
                $data["message"] = "成功!";
            } else {

                $data["code"] = 605;
                $data["message"] = "修改失败!";
            }
        } else {
            $data["code"] = 601;
            $data["message"] = "失败!找不到需要修改的数据.";
            $data["data"] = "";
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
            $amodel = new  AuthModel(self::$server);
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
     * 登录
     * @return false|string
     */
    public function actionLogin()
    {
        $data["URL"] = "../../template/index/index.php";
        $request["email"] = self::$server->post["Email"] ?? "";
        $password = self::$server->post["Password"] ?? "";
        if (!empty($request["email"]) && !empty($password)) {
            $amodel = new  AuthModel(self::$server);
            $find = ["email" => "and","status" => "and"];
            $request["status"] = "1";
            $rda = $amodel->GetParent($find, $request);
            $rda['password'] = $password;
            $token = $amodel->Getpasswordandtoken($rda);
            if ($rda['password_hash'] == $token['password']) {

                unset($rda['password']);
                $rda['token'] = $token['token'];
                $rda['updated_at'] = time();
                if ($amodel->Login($rda)) {

                    $data["data"] = ["uname" => $rda['username'], 'email' => $request["email"], 'token' => $token['token']];
                }

            }

        }

        if ($t =& $data["data"]) {

            $data["code"] = 600;
            $data["message"] = "登录成功!";
        } else {

            $data["code"] = 605;
            $data["message"] = "登录失败!\n密码错误!";
        }
        return json_encode($data);
    }

    /**
     * 登出
     * @return false|string
     */
    public function actionLoginout()
    {
        $data["URL"] = "../../template/auth/login.html";
        $token = self::$server->cookie["token"] ?? "";
        if (!empty($token)) {
            $amodel = new  AuthModel(self::$server);
            $data["data"] = $amodel->Loginout($token);
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
     * 用户信息
     * @return false|string
     */
    public function actionUserinfo()
    {
        $data["Modal"] = "#userinfo";
        $token = self::$server->cookie["token"] ?? "";
        if (!empty($token)) {
            $data["data"] = self::Getuserinfo($token);
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
        if (empty($id)){
            if(isset($uinfo["permission_group"]) && !empty($uinfo['permission_group']) && $uinfo['status'] == "1"){
                $where["permission_group"] = 1;
                $find["permission_group"] = "or";
                $where["permission_group_s"] = $uinfo['id'];
            }
        }

        if (!empty($id)) {
            $temp = new AuthModel(self::$server);
            $ck = $temp->GetParent($find, $where, "one", "id,username uname,email,permission_group,status");
            if (!empty($ck)){

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
        $temp = new AuthModel(self::$server);
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
            "data" => $temp->Getpermission_group($uinfo["id"])
        ];
        $data["Status"] = [
            "type" => "select",
            "style" => 'data-placeholder="请选择..." class="standardSelect" tabindex="1"',
            "data" => $pub["status"]
        ];
        return $data;
    }

}