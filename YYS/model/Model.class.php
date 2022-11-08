<?php
/**
 * Created by PhpStorm.
 * User: zhangyu
 * Date: 2018/11/19
 * Time: 17:26
 */

namespace YYS\model;

use components\Pdodb;
use components\Redisdb;

class Model
{

    public $server;

    public function __construct($server)
    {
        $this->server = $server;
        //defined('Dom') ?? define('Dom',preg_match('/\w*\.\w*$/', $server->header['host'], $_hosts)[0]);
    }

    /**
     * 初始化mysqlpdo
     * @param string $type
     * @param string $dbname
     * @return Pdodb
     */
    public static function iniPdo($dom = Dom, $dbname = '', $type = 'mysql')
    {
        $dbname = $dbname ? (config[Ips][$dom][$type] ? config[Ips][$dom][$type] : 'db') : 'db';
        $conter = main[$type][$dbname];

        return new Pdodb($conter['hosts'], $conter['username'], $conter['password'], $conter['options']);
    }

    /**
     * 初始化redis
     * @param string $type
     * @param string $dbname
     * @param array $attr
     * @return Redisdb
     */
    public static function iniRedis($dom = Dom, $dbname = '', $type = 'redis', $attr = [])
    {
        $dbname = $dbname ? (config[Ips][$dom][$type] ? config[Ips][$dom][$type] : 'db') : 'db';
        $conter = main[$type][$dbname];

        return new Redisdb($conter, $attr);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

}