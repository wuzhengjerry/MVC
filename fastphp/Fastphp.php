<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 9:29
 */
namespace fastphp;

//框架目录
defined('CORE_PATH') or define('CORE_PATH', __DIR__);

//框架核心
class Fastphp
{
    //配置内容
    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    //运行程序
    public function run()
    {
        spl_autoload_register(array($this, 'loadClass'));
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->unregisterGlobals();
        $this->setDbConfig();
        $this->route();
    }

    //路由处理
    public function route()
    {
        $controllerName = $this->config['defaultController'];
        $actionName = $this->config['defaultAction'];
        $param = array();

        $url = $_SERVER['REQUEST_URI'];
        //清除？后的内容
        $position = strpos($url, '?');
        $url = $position === false ? $url : substr($url, 0, $position);
        //删除前后的 '/'
        $url = trim($url, '/');

        if ($url) {
            //使用'/'分割字符串，并保存到数组中
            $urlArray = explode('/', $url);
            //删除空的数组元素
            $urlArray = array_filter($urlArray);
            //获取控制器名称
            $controllerName = ucfirst($urlArray[0]);
            //获取动作名
            array_shift($urlArray);
            $actionName = $urlArray ? $urlArray[0] : $actionName;

            //获取URL参数
            array_shift($urlArray);
            $param = $urlArray ? $urlArray : array();
        }

        //判断控制器和操作是否存在
        $controller = 'app\\controllers\\' . $controllerName . 'Controller';
        if (!class_exists($controller)) {
            exit($controller . '控制器不存在');
        }
        if (!method_exists($controller, $actionName)) {
            exit($actionName. '方法不存在');
        }

        //如果控制器和操作名存在，则实例化控制器，因为控制器对象里面还会用到控制器名和操作名
        //所以实例化的时候把他们俩的名称也传进去
        $dispatch = new $controller($controllerName, $actionName);
        call_user_func_array(array($dispatch, $actionName), $param);
    }

    //检测开发环境
    public function setReporting()
    {
        if (APP_DEBUG === true) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'Off');
            ini_set('log_errors', 'On');
        }

    }

    //删除敏感字符
    public function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }

    //检测敏感字符并删除
    public function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
            $_GET = isset($_GET) ? $this->stripSlashesDeep($_GET) : '';
            $_POST = isset($_POST) ? $this->stripSlashesDeep($_POST) : '';
            $_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';

        }
    }

    //检测自定义全局变量并移除，因为register_global已经弃用，如果已经弃用的register_globals指令被设置为on,那么局部变量也将
    //在脚本的全局作用域中可用，例如 $_POST['foo']也将以$foo的形式存在，这样写不好实现的，会影响代码中其它变量
    //参考 http://php.net/manual/zh/faq.using.php#faq.register-globals
    public function unregisterGlobals()
    {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKOE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$value]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    //配置数据库信息
    public function setDbConfig()
    {
        if ($this->config['db']) {
            define('DB_HOST', $this->config['db']['host']);
            define('DB_NAME', $this->config['db']['dbname']);
            define('DB_USER', $this->config['db']['username']);
            define('DB_PASS', $this->config['db']['password']);
        }
    }

    //自动加载类
    public function loadClass($className)
    {
        $classMap = $this->classMap();
        if (isset($classMap[$className])) {
            //包含内核文件
            $file = $classMap[$className];
        } elseif (strpos($className, '\\') != false) {
            //包含应用（app中）文件
            $file = APP_PATH . str_replace('\\', '/', $className) . '.php';
            if (!is_file($file)) {
                return ;
            }
        } else {
            return ;
        }

        include $file;
    }

    //内核文件命名空间映射关系
    protected function classMap()
    {
        return [
            'fastphp\base\Controller' => CORE_PATH . '/base/Controller.php',
            'fastphp\base\Model' => CORE_PATH . '/base/Model.php',
            'fastphp\base\View' => CORE_PATH . '/base/View.php',
            'fastphp\db\Db' => CORE_PATH . '/db/Db.php',
            'fastphp\db\Sql' => CORE_PATH . '/db/Sql.php'
        ];
    }
}