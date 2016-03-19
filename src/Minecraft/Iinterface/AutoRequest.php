<?php
namespace Minecraft\Iinterface;

class AutoRequest extends \Minecraft\Iinterface\Iinterface {
    static $interfaces = [];
    
    public function request(\Minecraft\Router\Route $route, $front) {

        /* 不需要自动请求 */
        if(!$route->autoRequests || count($route->autoRequests) <= 0) {
            return false;
        }
        $this->frontRoute = $route;
        $front->res = [];
        
        foreach($this->frontRoute->autoRequests as $name=>$request) {
            $front->res[$name] = $this->process($request, $this->frontRoute->paramets);
        }
        $front->res = json_encode($front->res);
    }
}