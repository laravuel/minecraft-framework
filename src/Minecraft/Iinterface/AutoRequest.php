<?php
namespace Minecraft\Iinterface;

class AutoRequest {
    public $route;
    static $interfaces = [];
    
    public function init(\Minecraft\Router\Route $route) {

        /* 不需要自动请求 */
        if(!$route->autoRequests || count($route->autoRequests) <= 0) {
            return false;
        }
        $this->route = $route;
        
        $this->request();
    }
    
    /**
     * 请求对应的接口
     */
    public function request() {
        
    }
}