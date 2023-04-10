<?php


namespace YYS\model\auth;

use components\Tool;
use YYS\model\Model;

class AuthModel extends Model
{
    private $authkey = "AUTHKEY";
    private $token = "TOKEN";
    private $pgroup = "PGROUP";

    /**
     * 查找用户是否存在
     * @param $rq
     * @param $ws
     * @return array
     */
    public function Checkuser($find = [], $where = [], $fds = "count(1) num")
    {
        $rows = [];
        if (count($where) > 0 && count($find) > 0) {

            $fd = "";
            $ed = count($find);
            $coun = 1;
            foreach ($find as $k => $v) {
                if ($ed == $coun) {
                    $fd .= " {$k} = ? ";
                } else {
                    $fd .= " {$k} = ? {$v} ";
                }
                $coun++;
            }

            $data = Model::iniPdo()->link()->prepare('select ' . $fds . ' from user where ' . $fd);
            $tmp = 0;
            foreach ($where as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);

            }
            //var_dump($data->queryString,$data->debugDumpParams());
            $data->execute();
            if ($data && $data->rowCount()) {
                $rows = $data->fetch(\PDO::FETCH_ASSOC);
            }
            $data->closeCursor();
        }
        return $rows;
    }

    /**
     * 添加
     * @param $value
     * @return int
     */
    public function Add($value)
    {
        $rows = [];
        if (count($value)) {

            $db = Model::iniPdo()->link();
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->beginTransaction();
            $data = $db->prepare('insert into user(auth_key,username,email,permission_group,`status`,created_at,updated_at,password_hash,password_reset_token) values(?,?,?,?,?,?,?,?,?)');
            $tmp = 0;
            foreach ($value as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);
            }
            //var_dump($data->queryString, $data->debugDumpParams());
            try {
                $data->execute();
                $uid = $db->lastinsertid();
                $db->commit();

                //用户数据存入redis
                $redis = Model::iniRedis();
                $redis->multi();
                $r1 = $redis->hSet($this->token, $value['password_reset_token'], $uid);
                $value['id'] = $uid;
                $r2 = $redis->hSet($this->authkey, $uid, json_encode($value));
                $redis->exec();
                $redis->close();
                if (is_object($r1) && is_object($r2) && empty((array)$r1) && empty((array)$r2)) {
                    $rows = ["uname" => $value['username'], 'email' => $value["email"], 'token' => $value['password_reset_token']];
                }
            } catch (\PDOException $e) {
                var_dump("错误:", $e->getMessage(), $data->queryString);
                $db->rollBack();
            }
            $data->closeCursor();
        }
        return $rows;
    }

    /**
     * 修改
     * @param $value
     * @param $where
     * @return int
     */
    public function Update($find, $where)
    {
        $rows = 0;
        if (count($find) && count($where)) {

            $fd = array_keys($find);
            $fd = implode('=?,', $fd) . "=?";
            $db = Model::iniPdo()->link();
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->beginTransaction();
            $data = $db->prepare('update user set ' . $fd . ' where id=? ');
            $tmp = 0;
            foreach ($find as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);
            }
            foreach ($where as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);
            }
            try {
                $data->execute();
                $rows = $data->rowCount();
                $db->commit();

                //删除用户登录信息,强制踢下线
                if ($rows) {
                    $userinfo = Model::iniRedis()->hGet($this->authkey, $where["id"]);
                    if (!empty($userinfo)) {
                        $userinfo = json_decode($userinfo, true);
                        $redis = Model::iniRedis();
                        $redis->hdel($this->authkey, $where["id"]);
                        $redis->hdel($this->token, $userinfo["token"]);
                        $redis->exec();
                        $redis->close();
                    }
                }
            } catch (\PDOException $e) {
                var_dump("错误:", $e->getMessage(), $data->queryString);
                $db->rollBack();
            }
        }
        return $rows;
    }

    /**
     * 删除
     * @param $where
     * @return int
     */
    public function Disable($value, $where)
    {
        $rows = 0;
        if (count($where)) {

            $db = Model::iniPdo()->link();
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->beginTransaction();
            $data = $db->prepare("update user set status=?,updated_at=? where id=? ");
            $tmp = 0;
            foreach ($value as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);
            }
            foreach ($where as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);
            }
            try {
                $data->execute();
                $rows = $data->rowCount();
                $db->commit();

                //删除用户登录信息,强制踢下线
                if ($rows) {
                    $userinfo = Model::iniRedis()->hGet($this->authkey, $where["id"]);
                    if (!empty($userinfo)) {
                        $userinfo = json_decode($userinfo, true);
                        $redis = Model::iniRedis();
                        $redis->hdel($this->authkey, $where["id"]);
                        $redis->hdel($this->token, $userinfo["token"]);
                        $redis->exec();
                        $redis->close();
                    }
                }
            } catch (\PDOException $e) {
                var_dump("错误:", $e->getMessage(), $data->queryString);
                $db->rollBack();
            }
        }
        return $rows;
    }

    /**
     * 查询数据
     * @param string $field
     * @param array $find
     * @param array $where
     * @return array
     */
    public function GetParent($find = [], $where = [], $cnt = "one", $field = "id,username,auth_key,password_hash,password_reset_token,email,permission_group,status,created_at")
    {
        $rows = [];
        if (count($where) && count($find)) {

            $fd = "";
            $ed = count($find);
            $coun = 1;
            foreach ($find as $k => $v) {
                if ($coun == $ed) {
                    $fd .= " {$k} = ? ";
                } else {
                    $fd .= " {$k} = ? {$v} ";
                }
                $coun++;
            }

            $pgs = isset($where['permission_group_s']) ? " or permission_group in (select id from permission_group where uid = ?)" : "";
            $data = Model::iniPdo()->link()->prepare('select ' . $field . ' from user where ' . $fd . $pgs);
            $tmp = 0;
            foreach ($where as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);

            }
            $data->execute();
            if ($cnt == "one") {
                if ($data && $data->rowCount()) {
                    $rows = $data->fetch(\PDO::FETCH_ASSOC);
                }
            } else {
                $rows = $data->fetchAll(\PDO::FETCH_ASSOC);
            }
            $data->closeCursor();
        } else {
            $data = Model::iniPdo()->link()->prepare('select ' . $field . ' from user');
            $data->execute();
            if ($cnt == "one") {
                if ($data && $data->rowCount()) {
                    $rows = $data->fetch(\PDO::FETCH_ASSOC);
                }
            } else {
                $rows = $data->fetchAll(\PDO::FETCH_ASSOC);
            }
        }
        return $rows;
    }

    /**
     * 获取用户组
     * @return array
     */
    public function Getpermission_group($uid = "")
    {
        $rows = json_decode(Model::iniRedis()->hGet($this->pgroup, $uid), true);
        if (empty($rows)) {
            $whe = empty($uid) ? "" : " or uid = ?";
            $data = Model::iniPdo()->link()->prepare('select id,name from permission_group where id = 1 ' . $whe);
            if (!empty($uid)) {
                $data->bindValue(1, $uid);
            }
            $data->execute();
            $rows = Tool::PopArray($data->fetchAll(\PDO::FETCH_ASSOC), "id", "name");
            Model::iniRedis()->hSet($this->pgroup, $uid, json_encode($rows));
        }
        return $rows;
    }

    /**
     * 登录设置
     * @param $data
     * @return bool
     */
    public function Login($data)
    {
        $token = $data["token"];
        $n = false;
        if (!empty($token)) {

            self::Loginout($token);
            $redis = Model::iniRedis();
            $redis->multi();
            $r1 = $redis->hSet($this->token, $token, $data["id"]);
            $r2 = $redis->hSet($this->authkey, $data["id"], json_encode($data));
            $redis->exec();
            $redis->close();
            if (is_object($r1) && is_object($r2) && empty((array)$r1) && empty((array)$r2)) {
                $db = Model::iniPdo()->link();
                $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $db->beginTransaction();
                $sql = $db->prepare('update user set password_reset_token=?,updated_at=? where id=?');

                $sql->bindValue(1, $token);
                $sql->bindValue(2, $data['updated_at']);
                $sql->bindValue(3, $data['id']);
                try {
                    $sql->execute();
                    if ($sql->rowCount()) {
                        $n = true;
                    }
                    $db->commit();
                } catch (\PDOException $e) {
                    var_dump("错误:", $e->getMessage(), $sql->queryString);
                    $db->rollBack();
                }
                $sql->closeCursor();
            }
        }

        return $n;
    }

    /**
     * 登出
     * @param $token
     * @return bool
     */
    public function Loginout($token)
    {
        $n = false;
        $uid = Model::iniRedis()->hGet($this->token, $token);
        if (!empty($uid)) {
            $redis = Model::iniRedis();
            $r1 = $redis->hdel($this->authkey, $uid);
            $r2 = $redis->hdel($this->token, $token);
            $redis->exec();
            $redis->close();
            if (is_object($r1) && is_object($r2) && empty((array)$r1) && empty((array)$r2)) {
                $n = true;
            }
        }

        return $n;
    }

    /**
     * 生成密码和token
     * @param $where
     * @return array
     */
    public function Getpasswordandtoken($where)
    {

        $data = [];
        if (!empty($where)) {
            $data['password'] = md5($where['auth_key'] . base64_encode($where['password'] . $where['created_at']));
            $data['token'] = md5($where['username'] . time() . uniqid(rand(), true));
        }
        return $data;
    }


}
