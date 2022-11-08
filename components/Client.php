<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/2
 * Time: 10:57
 */

namespace components;

#use components\Redisdb;

class Client
{
    public $msg = '';

    public $data = [];

    public function lianjie()
    {

        $cli = new \swoole_client(SWOOLE_SOCK_TCP);
        //判断连接状态（同步连接模式）
        $res = $cli->connect('127.0.0.1', 81);
        if (empty($res)) {
            return "连接失败";
        }

        if (!empty($this->data)) {
            //发送消息给server
            $rel = $cli->send(json_encode($this->data));
        } else {
            //发送消息给server
            $rel = $cli->send($this->msg);
        }
        if (!empty($rel)) {
            return $rel;
        } else {
            return flash;
        }
    }

}