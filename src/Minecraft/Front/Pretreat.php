<?php
namespace Minecraft\Front;

class Pretreat {
    
    static $route;
    static $pretreats = [];
    public $sObj; // 实例化该类的原始对象
    
    public function init(\Minecraft\Router\Route $route, $sObj) {

        /* 不需要预处理 */
        if(!$route->pretreats || count($route->pretreats) <= 0) {
            return false;
        }
        self::$route = $route;
        $this->sObj = $sObj;
        $this->load();
    }
    
    /**
     * 加载对应的预处理类，并执行
     */
    public function load() {
        $space = \Minecraft\App::$config['pretreats'];
        foreach(self::$route->pretreats as $pretreat) {
            list($path, $method) = explode('.', $pretreat);
            if(strpos($path, '/')) {
               $pathArray = explode('/', $path);
                foreach($pathArray as $v) {
                    $npath .= ucfirst($v).'\\';
                }
                $class = $space.substr($npath, 0, -1);
            }
            else {
                $class = $space.ucfirst($path);
            }
            if(!self::$pretreats[$class]) {
                self::$pretreats[$class] = new $class();
            }
            
            $res = call_user_func_array(array(self::$pretreats[$class], $method), self::$route->paramets);
            if($res) {
                $this->sObj->resPretreat($res);
            }
        }
    }
    
}