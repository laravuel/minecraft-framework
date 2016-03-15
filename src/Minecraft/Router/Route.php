<?php
namespace Minecraft\Router;


abstract class Route {
    static $url;
    static $routes;
    
    protected $pathStr = '';
    protected $pathArray = [];
    
    public $uriPath;
    
    public $keys = [
        'pretreat' => '_PRETREAT_',
        'auto_request' => '_AUTO_REQUEST_',
        'del_flg' => '~',
    ];
    
    /**
     * 相关路由方案注册
     * @routes array 路由类名称
     * @return bool
     */
    final public static function register($routes = [], $configs = []) {
        
        foreach($routes as $k=>$route) {
            if(!self::$routes[$route]) {
                $route = '\Minecraft\Router\\'.$route;
                self::$routes[$route] = new $route($configs[$k]);
            }
        }
        return true;
    }
    
    /**
     * 初始化路由方案
     * @return Route
     */
    public static function init() {
        self::$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        foreach(self::$routes as $routeObj) {
            if($routeObj->check()) {
                return $routeObj;
            }
        }
        return false;
    }
    
    /**
     * 解析url路径
     * @return
     */
    protected function parseUrl() {
        $urlArray = parse_url(self::$url);

        $this->uriPath = $this->pathStr = preg_replace('/[\s\S]*\.php\//', '', $urlArray['path']);
        if(\Minecraft\App::$config['WebPath']) {
            $this->uriPath = $this->pathStr = preg_replace('/^'.str_replace('/', '\\/', \Minecraft\App::$config['WebPath']).'/', '', $this->pathStr);
        }
        if($this->pathStr[0] == '/') {
            $this->pathStr = substr($this->pathStr, 1, strlen($this->pathStr));
        }
        $this->pathArray = explode('/', $this->pathStr);
    }
    
    /**
     * 根据url检测要返回的路由方案
     */
    abstract public function check();
}
?>