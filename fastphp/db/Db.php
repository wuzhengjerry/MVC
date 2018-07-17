<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 11:09
 */

namespace fastphp\db;

use PDO;
use PDOException;

/*
 *数据库操作类
 * 其$pdo属性为静态属性，所以在页面执行周期内只要赋值一次，以后获取还是首次赋值的内容。
 * 这里就是PDO对象，这样可以确保 运行期间只有一个数据库连接对象，一直简单的单例模式
 */
class Db
{
    private static $pdo = null;

    public static function pdo()
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=urf8', DB_HOST, DB_NAME);
            $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
            return self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $option);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
}