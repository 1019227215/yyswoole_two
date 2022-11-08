<?php
/**
 * Created by PhpStorm.
 * User: zhangyu
 * Date: 2018/11/14
 * Time: 17:37
 */

namespace components;

use YYS\model\Model;

class Controller
{
    private $authkey = "AUTHKEY";
    private $token = "TOKEN";
    private $pgroup = "PGROUP";
    public static $server;//get/post参数
    public static $response;//更多请求信息
    public static $swoole;//cookie信息
    public $request = [];//swoole对象

    public function __construct($server, $response, $swoole)
    {
        ini_set('date.timezone', 'Asia/Shanghai');
        self::$server = $server;
        self::$response = $response;
        self::$swoole = $swoole;
        self::setGetPost();
    }

    /**
     * 判断是否登录
     * @return string
     */
    public function AuthCheck()
    {
        $token = self::$server->cookie["token"];
        return self::Getuserinfo($token);
    }

    /**
     * 合并get和post数据
     * @return array
     */
    private function setGetPost()
    {

        //得到上传参数Post&Get
        if (is_array(self::$server->get) && is_array(self::$server->post)) {

            $this->request = self::$server->get + self::$server->post;
        } else {

            $this->request = is_array(self::$server->get) ? self::$server->get : $this->request;
            $this->request = is_array(self::$server->post) ? self::$server->post : $this->request;
        }

        //获取body内容(逻辑需要可以按照这个获取)
        if (!empty(self::$server->rawContent())) {

            $this->request['body'] = self::$server->rawContent();
        }

        //$this->request = Tool::handle($this->request);//删除特殊符号(下架参数过滤，让逻辑处理)

        //写日志
        if (!empty($this->request)) {

            Tool::setFile(LogDir . 'Request.log', ['url' => self::$server->server['request_uri'], 'ip' => self::$server->server['remote_addr']] + $this->request);
        }

        return $this->request;
    }

    /**
     * 加载视图文件
     * @param $files
     * @param $data
     * @return string
     */
    public static function renderView($files, $data, $caches = true)
    {
        $data = Run::render(OBJ . View . $files, $data);

        if ($caches) {

            self::staticHtml($files, $data);
        }
        return $data;
    }

    /**
     * 静态化html
     * @param $fname
     * @param $fconten
     * @return bool
     */
    public static function staticHtml($fname, $fconten)
    {
        $fname = explode('.', $fname);
        $fname = Puc . config['render']['static_url'] . '/' . $fname[0] . '.html';

        return Tool::setFile($fname, $fconten, 0, false, false);
    }

    /**
     * 加载视图模板文件
     * @param $files
     * @param $data
     * @param bool $caches
     * @param string $tempurl
     * @return string
     */
    public static function renderTempView($files, $data, $caches = true, $tempurl = "template/public/index.php")
    {
        $tempurl = empty($tempurl) ? $files : $tempurl;
        $data["interfaceUrl"] = empty($data["interfaceUrl"]) ? $files : $data["interfaceUrl"];
        $data = Run::render(OBJ . View . $tempurl, $data);

        if ($caches) {

            self::staticTempHtml($files, $data);
        }
        return $data;
    }

    /**
     * 模板视图静态化处理
     * @param $fname
     * @param $fconten
     * @return bool
     */
    public static function staticTempHtml($fname, $fconten)
    {
        $fname = explode('.', $fname);
        $fname = Puc . $fname[0] . '.html';

        return Tool::setFile($fname, $fconten, 0, false, false);
    }

    /**
     * 根据token获取用户信息
     * @param $token
     * @return array|mixed
     */
    public function Getuid($token)
    {
        return Model::iniRedis()->hGet($this->token, $token);
    }

    /**
     * 根据token获取用户信息
     * @param $token
     * @return array|mixed
     */
    public function Getuserinfo($token)
    {
        $data = [];
        $uid = Model::iniRedis()->hGet($this->token, $token);
        if (!empty($uid)) {
            $data = json_decode(Model::iniRedis()->hGet($this->authkey, $uid), true);
        }
        return $data;
    }

    /**
     * 根据id获取用户信息
     * @param $id
     * @return array|mixed
     */
    public function Getuserinfo_id($id)
    {
        return json_decode(Model::iniRedis()->hGet($this->authkey, $id), true);
    }

    /**
     * 获取公共配置
     * @param string $t
     * @return mixed|string
     */
    public function GetPulicContent($file = 'public_content', $t = "")
    {
        $data = Tool::setConfig($file, false, OBJ . "/config/");
        return empty($t) ? $data : $data[$t];
    }

    /**
     * 获取加密id
     * base64,随机7,$id,随机6
     * @param $id
     * @return string
     */
    public function SetPublicId($id,$time)
    {
        return 'Tourist'.strlen($time).'.'.base64_encode(substr($time,0,6) . $id . substr($time,6,strlen($time)));
    }

    /**
     * 解析加密id
     * @param $b4
     * @return false|string
     */
    public function GetPublicId($b4){
        $b4 = explode(".",$b4);
        $len = substr($b4[0],7,strlen($b4[0]));
        $str = base64_decode($b4[1]);
        return substr($str,6,strlen($str)-$len);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

}