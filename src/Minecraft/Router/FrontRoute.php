<?php
namespace Minecraft\Router;

class FrontRoute extends Route {
    
    public $config = [];
    public $route = [];
    public $pretreats = [];
    public $autoRequests = [];
    public $paramets = [];
    public $fronts = [];
    public $beforeFronts = [];
    public $afterFronts = [];
    
    public function __construct($config) {
        $this->config = $config;
        $this->keys['routes'] = '_ROUTES_';
        $this->keys['before_fronts'] = '_BEFORE_FRONTS_';
        $this->keys['after_fronts'] = '_AFTER_FRONTS_';
    }
    
    /**
     * 检测是否返回该实例
     */
    public function check() {
        $this->parseUrl();
        if($this->pathArray[0] != 'interface') {
            return true;
        }
        return false;
    }
    
    public function search($group = []) {
        if(!$group) {
            $group = $this->config;
            $this->setParamets($group);
        }
        // 自上而下匹配全局路由条目
        if($group[$this->keys['routes']]) {
            $this->route = $this->searchRoutes($group[$this->keys['routes']]);
            if($this->route['pretreat']) {
                $this->pretreats = array_merge($this->pretreats, $this->route['pretreat']);
            }
            if($this->route['auto_request']) {
                $this->autoRequests = array_merge($this->autoRequests, $this->route['auto_request']);
            }
            if($this->route['fronts']) {
                if(is_array($this->route['fronts'])) {
                    $this->fronts = array_merge($this->fronts, $this->route['fronts']);
                }
                else {
                    $this->fronts[] = $this->route['fronts'];
                }
            }
        }
        if(!$this->route) {
            // 匹配路由组
            
            if($this->pathArray[0] && $group[$this->pathArray[0]]) {
                $group = $group[$this->pathArray[0]];
                $this->setParamets($group);
                $this->pathStr = str_replace($this->pathArray[0].'/', '', $this->pathStr);
               
                array_shift($this->pathArray);
                
                $this->search($group);
            }
        }
        return $this;
    }
    
    protected function setParamets($group) {
        if($group[$this->keys['pretreat']]) {
            $this->pretreats = array_merge($this->pretreats, $group[$this->keys['pretreat']]);
        }
        if($group[$this->keys['auto_request']]) {
            $this->autoRequests = array_merge($this->autoRequests, $group[$this->keys['auto_request']]);
        }
        if($group[$this->keys['before_fronts']]) {
            $this->beforeFronts = array_merge($this->beforeFronts, $group[$this->keys['before_fronts']]);
        }
        if($group[$this->keys['after_fronts']]) {
            $this->afterFronts = array_merge($this->afterFronts, $group[$this->keys['after_fronts']]);
        }
    }
    
    protected function searchRoutes($routes) {
        foreach($routes as $route) {
            if($currentRoute = $this->matchRoute($route)) {
                return $currentRoute;
            }
        }
        return array();
    }
    
    protected function matchRoute($route) {
        $currentRoute = array();
        list(
            $currentRoute['type'], 
            $currentRoute['resource'], 
            $currentRoute['fronts'], 
            $currentRoute['pretreat'], 
            $currentRoute['auto_request'], 
            $currentRoute['where']
        ) = $route;
        
        $resArray = explode('/', $currentRoute['resource']);
        array_shift($resArray);
        $result = $this->comparResource($currentRoute, $this->pathArray, $resArray);
        $result = array_flip($result);
        if(!isset($result[0])) {
            return $currentRoute;
        }
        return false;
    }
    
    protected function comparResource($currentRoute, $pathArray, $resArray, $result = []) {
        if(!$resArray || !is_array($resArray) || count($resArray) <= 0) return $result;
        if($_SERVER['REQUEST_METHOD'] != $currentRoute['type']) {
            array_push($result, 0);
            return $result;
        }
        /* 匹配参数 */
        if(preg_match('/\{([\s\S]+?)\}/', $resArray[0], $match)) {
            if($pattern = $currentRoute['where'][$match[1]]) {
                if(!preg_match('/^'.$pattern.'$/', $pathArray[0], $m)) {
                    array_push($result, 0);
                    return $result;
                }
            }
            $this->paramets[$match[1]] = $pathArray[0];
        }
        else if($pathArray[0] != $resArray[0]) {
            array_push($result, 0);
            return $result;
        }
        array_shift($pathArray);
        array_shift($resArray);
        array_push($result, 1);
        return $this->comparResource($currentRoute, $pathArray, $resArray, $result);
    }
}
?>