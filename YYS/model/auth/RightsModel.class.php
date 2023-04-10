<?php

namespace YYS\model\auth;

use components\Tool;
use YYS\model\Model;

class RightsModel extends Model
{

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
            $data = $db->prepare('insert into permission(`name`,parent_id,status,model,path,uid,created_at,updated_at) values(?,?,?,?,?,?,?,?)');
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
            $data = $db->prepare('update permission set ' . $fd . ' where id=? ');
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
            $data = $db->prepare("update permission set status=?,updated_at=? where id=? ");
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
    public function GetParent($find = [], $where = [], $cnt = "one", $field = "id,`name`,parent_id,status,model,path,uid,created_at,updated_at")
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

            $pgs = isset($where['permission_group_s']) ? " or id in (?)" : "";
            $data = Model::iniPdo()->link()->prepare('select ' . $field . ' from permission where ' . $fd . $pgs);
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
            $data = Model::iniPdo()->link()->prepare('select ' . $field . ' from permission');
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
     * 获取组
     * @return array
     */
    public function GetGroup($uid = "")
    {
        $whe = empty($uid) ? "" : "uid = ?";
        $data = Model::iniPdo()->link()->prepare('select id,name from permission where ' . $whe);
        if (!empty($uid)) {
            $data->bindValue(1, $uid);
        }
        $data->execute();
        $rows = Tool::PopArray($data->fetchAll(\PDO::FETCH_ASSOC), "id", "name");
        Model::iniRedis()->hSet($this->pgroup, $uid, json_encode($rows));
        return $rows;
    }


}
