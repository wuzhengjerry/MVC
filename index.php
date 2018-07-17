<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 9:28
 */
//应用程序为当前目录
define('APP_PATH', __DIR__ . '/');

//开启调试模式
define('APP_DEBUG', true);

//加载框架文件
require(APP_PATH . 'fastphp/Fastphp.php');

//加载配置文件
$config = require(APP_PATH . 'config/config.php');
header("Content-Type: text/html; charset=utf-8");
(new fastphp\Fastphp($config))->run();