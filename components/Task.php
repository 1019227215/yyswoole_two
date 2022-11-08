<?php
/**
 * 代表的是  swoole里面 后续 所有  task异步 任务 都放这里来
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/2
 * Time: 10:55
 */

namespace components;

#use components\Redisdb;

class Task
{
    public $rediscf = ['host'=>'127.0.0.1'];
//异步创建房间
    public function chuangjian($data, $serv)
    {
        $time = $data['time'] * 1000;
        swoole_timer_after($time, function () use ($data) {
            //创建房间(修改拍卖商品状态)
            self::post("https://code.77wx.cn/index/index/in");
        });
    }

    //进入房间并缓存信息
    public function jingru($data, $serv)
    {
        $fd = Redisdb::getInstance($this->rediscf)->get('fd');
        //加入分组
        Redisdb::getInstance($this->rediscf)->hset($data['name'], $data['uid'], $fd);
        //加入组集合
        Redisdb::getInstance($this->rediscf)->sadd('group', $data['name']);
    }


    public function post($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        //执行
        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        //关闭url请求
        curl_close($ch);
        return json_decode($response, 1);
    }

}