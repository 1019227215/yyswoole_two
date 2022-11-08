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

class ListModel extends Model
{

    /**
     * 列表内容获取
     * @param array $find
     * @param array $where
     * @return array
     */
    public function Index($find = [], $where = [])
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

            $data = Model::iniPdo()->link()->prepare('select id,REPLACE(parent_like,"|","") parent_like,parent_id,name,title,REPLACE(css,"|","") cssd,type typed from template where ' . $fd . ' order by parent_id asc');
            $tmp = 0;
            foreach ($where as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);

            }
            //var_dump($data->queryString,$data->debugDumpParams());
            $data->execute();
            $rows = $data->fetchAll(\PDO::FETCH_ASSOC);

        } else {

            $data = Model::iniPdo()->link()->prepare('select id,REPLACE(parent_like,"|","") parent_like,parent_id,name,title,REPLACE(css,"|","") cssd,type typed from template order by parent_id asc');
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
            $data = $db->prepare('insert into user_template(user,title,template_ids,additional_content,uid,uname) values(?,?,?,?,?,?) ');
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

            $data = $db->prepare("delete from user_template where id=?");
            $data->bindValue(1, $where["id"]);
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
     * 查看方案
     * @param array $find
     * @param array $where
     * @param string $field
     * @return array
     */
    public function Program($find = [], $where = [], $field = "id,title,user,template_ids,additional_content")
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

            $data = Model::iniPdo()->link()->prepare('select ' . $field . ' from user_template where ' . $fd);
            $tmp = 0;
            foreach ($where as $k => $v) {
                $tmp++;
                $data->bindValue($tmp, $v);

            }
            $data->execute();
            $rows = $data->fetchAll(\PDO::FETCH_ASSOC);

        } else {

            $data = Model::iniPdo()->link()->prepare('select ' . $field . ' from user_template ');
            $data->execute();
            $rows = $data->fetchAll(\PDO::FETCH_ASSOC);

        }
        $data->closeCursor();
        return $rows;
    }

}