<?php
/**
 * Created by PhpStorm.
 * User: zhangyu
 * Date: 2018/11/19
 * Time: 17:27
 */

namespace YYS\model\template;

use Cassandra\Varint;
use YYS\model\Model;

class TemplateModel extends Model
{

    /**
     * 列表内容获取
     * @param array $find
     * @param array $where
     * @return array
     */
    public function Index($find = [], $where = [])
    {
        if (count($where) > 0 && count($find) > 0) {

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

            $data = Model::iniPdo()->link()->prepare('select id,REPLACE(parent_like,"|","") parent_like,name,title,css cssd,type typed,parent_id from template where ' . $fd . ' order by parent_id asc,id desc');
            $tmp = 0;
            foreach ($where as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);

            }
            //var_dump($data->queryString,$data->debugDumpParams());
            $data->execute();
            $rows = $data->fetchAll(\PDO::FETCH_ASSOC);

        } else {

            $data = Model::iniPdo()->link()->prepare('select id,REPLACE(parent_like,"|","") parent_like,name,title,css cssd,type typed,parent_id from template order by parent_id asc,id desc');
            $data->execute();
            $rows = $data->fetchAll(\PDO::FETCH_ASSOC);

        }
        $data->closeCursor();
        return $rows;
    }

    /**
     * 添加
     * @param $value
     * @return int
     */
    public function Add($value)
    {
        $rows = 0;
        if (count($value)) {

            $db = Model::iniPdo()->link();
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->beginTransaction();
            $data = $db->prepare('insert into template(name,title,parent_id,type,css,parent_like,uid,uname,update_time) values(?,?,?,?,?,?,?,?,?) ');
            $tmp = 0;
            foreach ($value as $k => $v) {
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
    public function Update($value, $where)
    {
        $rows = 0;
        if (count($value) && count($where)) {

            $db = Model::iniPdo()->link();
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->beginTransaction();
            $data = $db->prepare('update template set name=?,title=?,parent_id=?,type=?,css=?,parent_like=?,uid=?,uname=? where id=?');
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
            $data->closeCursor();
        }
        return $rows;
    }

    /**
     * 删除
     * @param $where
     * @return int
     */
    public function Delete($where)
    {
        $rows = 0;
        if (count($where)) {

            $db = Model::iniPdo()->link();
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->beginTransaction();
            $data = $db->prepare("delete from template where id=? or parent_like like ?");
            $data->bindValue(1, $where["id"]);
            $data->bindValue(2, '%|' . $where["parent_like"] . '|%');
            //$query->bindValue(':name', '%'.$name.'%', \PDO::PARAM_STR);
            //var_dump($data->queryString,$data->debugDumpParams());
            try {
                $data->execute();
                $rows = $data->rowCount();
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
     * 查询数据
     * @param string $field
     * @param array $find
     * @param array $where
     * @return array
     */
    public function GetParent($find = [], $where = [],$field = "id,Parent_like,name")
    {
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

            $data = Model::iniPdo()->link()->prepare('select ' . $field . ' from template where ' . $fd . ' order by parent_id asc');
            $tmp = 0;
            foreach ($where as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);

            }
            $data->execute();
            $rows = $data->fetchAll(\PDO::FETCH_ASSOC);

        } else {

            $data = Model::iniPdo()->link()->prepare('select ' . $field . ' from template order by parent_id asc');
            $data->execute();
            $rows = $data->fetchAll(\PDO::FETCH_ASSOC);

        }
        $data->closeCursor();
        return $rows;
    }

}