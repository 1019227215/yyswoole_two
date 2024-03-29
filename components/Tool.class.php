<?php
/**
 * Created by PhpStorm.
 * User: zhangyu
 * Date: 2018/12/3
 * Time: 17:48
 */

namespace components;


class Tool
{

    private static $files = [];
    private static $execs = [];

    /**
     * 处理提交数据
     * @param $part
     * @return mixed
     */
    public static function handle($part)
    {

        if (is_array($part) && $part = &$part) {

            foreach ($part as $k => $value) {

                $k = self::prep($k);

                if (is_array($value) && $value = &$value) {

                    $part[$k] = self::handle($value);
                } else {

                    $value = self::prep($value);
                    $part[$k] = $value;
                }

            }

            return $part;
        } else {

            return self::prep($part);
        }
    }

    /**
     * 替换内容
     * @param $part
     * @param string $exp
     * @param string $cont
     * @return mixed
     */
    public static function prep($part, $exp = '/[^\w\-\_]/', $cont = '')
    {

        return preg_replace($exp, $cont, $part);
    }

    /**
     * 设置config下的文件为常量
     */
    public static function setConfig($name = '', $define = false, $cfg = S_ROOT . '/config/')
    {

        $config = array_diff(scandir($cfg), ['.', '..']);
        $state = "Without this file!";

        if (is_array($config) && $config =& $config) {

            foreach ($config as &$vl) {

                $fname = explode('.', $vl);

                if (is_array($fname) && $fname =& $fname) {

                    if ($name == $fname[0]) {

                        $cval = include $cfg . $vl;
                        $state = ($define && !defined($name)) ? define($fname[0], $cval) : $cval;
                    }
                }
            }
        } else {

            $state = "Empty directory!";
        }

        return $state;
    }

    /**
     * 异步写入文件，无返回值；所以对外直接返回true
     * @param $fname
     * @param $fconten
     * @param int $flags
     * @param bool $section
     */
    public static function setFile($fname, $fconten, $flags = -1, $section = false, $tc = true)
    {
        $fconten = is_string($fconten) ? $fconten : json_encode($fconten);

        $fname = self::catFile($fname, $tc);

        $fn = explode('.', $fname);
        $iftime = (end($fn) == 'log') ? date('Y-m-d H:i:s ', time()) : "";

        if ($section) {

            swoole_async_write($fname, $iftime . $fconten . PHP_EOL, $flags, function ($filename) {

                //echo "分段写入 " . $filename . " 成功！" . PHP_EOL;
            });
        } else {

            swoole_async_writefile($fname, $iftime . $fconten . PHP_EOL, function ($filename) {

                //echo "全量写入 " . $filename . " 成功！" . PHP_EOL;
            }, $flags);
        }

        return true;
    }

    /**
     * 跟进文件路径判断目录是否存在，不存在则创建
     * @param $fname
     * @param bool $t
     * @return string
     */
    public static function catFile($fname, $t = false)
    {
        $path = explode("/", $fname);
        $name = array_pop($path);
        $name = explode('.', $name);
        $path = implode('/', $path);

        if (!file_exists($path)) {

            mkdir($path, 0777, true);
        }

        return $t ? $path . "/{$name[0]}-" . date('Y-m-d', time()) . ".{$name[1]}" : $fname;
    }

    /**
     * 读取文件
     * @param $fname
     * @param int $flags
     * @param int $size
     */
    public static function getFile($fname, $flags = -1, $size = 0)
    {
        if ($size) {

            swoole_async_read($fname, function ($name, $conten) {

                self::$files = ['name' => $name, 'conten' => $conten];
            }, $size, $flags);
        } else {

            swoole_async_readfile($fname, function ($name, $conten) {

                self::$files = ['name' => $name, 'conten' => $conten];
            });
        }

        return self::$files;
    }

    /**
     * 获取备案信息
     * @param $hs
     */
    public static function getFiling(&$hs)
    {
        if (preg_match('/\w*\.\w*$/', $hs->header['host'], $_hosts)) {

            $hs->header['filing'] = config[Ips][$_hosts[0]]['filing'] ?? json_decode(@file_get_contents('http://icp.fleacloud.com/api/v1/icp?domain=' . $_hosts[0]), true)['icp'];
        } else {
            $hs->header['filing'] = null;
        }
    }

    /**
     * 返回post数据
     * @param $post
     * @param array $par
     * @return array
     */
    public static function getPostdata($post, $par = [])
    {
        $data = [];
        if ($p =& $post && count($post)) {

            //处理数据
            foreach ($post as $k => $v) {

                if (is_array($v)) {

                    foreach ($v as $ks => $tmp) {
                        if ($vl = &$data[$tmp["name"]]) {

                            if (is_array($vl)) {

                                $data[$tmp["name"]][] = $tmp["value"];
                            } else {

                                $data[$tmp["name"]] = [$vl, $tmp["value"]];
                            }

                        } else {

                            $data[$tmp["name"]] = $tmp["value"];
                        }
                    }

                } else {

                    $data[$k] = $v;
                }
            }

            //数组转字符串
            if (count($par)) {
                foreach ($par as $k => $v) {

                    $tmp = &$data[$k];
                    if (is_array($tmp)) {
                        $data[$k] = implode($v, $tmp);
                    }
                }
            }

        }

        return $data;
    }

    /**
     * 二维数组处理
     * @param $arr
     * @param $km
     * @param string $vm
     * @return array
     */
    public static function PopArray($arr, $km, $vm = "")
    {
        $data = [];
        if ($tmp = &$arr) {
            foreach ($tmp as $value) {

                $data[$value[$km]] = $vm ? $value[$vm] : $value;
            }
        }

        return $data;
    }

}

