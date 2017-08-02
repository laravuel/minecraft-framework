<?php
namespace Minecraft\Router;

class InterfaceRoute extends Route {
    
    public $config = [];
    public $pretreats = [];
    public $paramets = [];
    public $route = [];
    public $resName = '';
    public $resFields = [];
    public $needParamets = [];
    
    public function __construct($config) {
        $this->config = $config;
        $this->keys['interfaces'] = '_INTERFACES_';
    }
    
    /**
     * 检测是否返回该实例
     */
    public function check() {
        $this->parseUrl();
        if($this->pathArray[0] == 'interface') {
            array_shift($this->pathArray);
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
        if($group[$this->keys['interfaces']]) {
            $this->route = $this->searchInterface($group[$this->keys['interfaces']]);
            if($this->route['pretreat']) {
                $this->pretreats = array_merge($this->pretreats, $this->route['pretreat']);
            }
            $this->resName .= $this->route['name'];
        }
        if(!$this->route) {
            // 匹配路由组
            if($this->pathArray[0] && $group[$this->pathArray[0]]) {
                $group = $group[$this->pathArray[0]];
                $this->resName .= $this->pathArray[0].'_';
                $this->setParamets($group);
                $this->pathStr = str_replace($this->pathArray[0].'/', '', $this->pathStr);
               
                array_shift($this->pathArray);
                
                $this->search($group);
            }
        }
        return $this;
    }
    
    protected function searchInterface($interfaces) {
        foreach($interfaces as $interface) {
            if($currentInterface = $this->matchInterface($interface)) {
                return $currentInterface;
            }
        }
        return array();
    }
    
    protected function matchInterface($interface) {
        $currentInterface = array();
        list(
            $currentInterface['type'], 
            $currentInterface['name'], 
            $currentInterface['interface'], 
            $currentInterface['get_paramets'], 
            $currentInterface['post_paramets'], 
            $currentInterface['pretreat'], 
        ) = $interface;
        
        if($_SERVER['REQUEST_METHOD'] != $currentInterface['type']) {
            return false;
        }
        if($currentInterface['name'] == $this->pathArray[0]) {
            if($currentInterface['get_paramets']) {
                
                foreach($currentInterface['get_paramets'] as $paramet_key=>$pattern) {
                    $getParamet = $_GET[$paramet_key];
                    if(is_array($getParamet)) {
                        $getParamet = json_encode($getParamet, JSON_NUMERIC_CHECK);
                    }
                    $this->paramets[$paramet_key] = $_GET[$paramet_key];
                    if(!$pattern) {
                        continue;
                    }
                    if(!preg_match('/^'.$pattern.'$/', $getParamet, $m)) {
                        return false;
                    }
                }
            }
            if($currentInterface['post_paramets']) {

                foreach($currentInterface['post_paramets'] as $paramet_key=>$pattern) {
                    $postParamet = $_POST[$paramet_key];
                    $this->paramets[$paramet_key] = $_POST[$paramet_key];

                    if(is_array($pattern)) {
                        foreach($pattern as $name=>$v) {
                            if(!$v) {
                                continue;
                            }
                            if(!preg_match('/^'.$v.'$/', $postParamet[$name], $m)) {
                                $this->needParamets[] = $paramet_key.'.'.$name;
                            }
                        }
                    }
                    else {
                        if(is_array($postParamet)) {
                            $postParamet = json_encode($postParamet, JSON_NUMERIC_CHECK);
                        }

                        if(!$pattern) {
                            continue;
                        }
                        if(!preg_match('/^'.$pattern.'$/', $postParamet, $m)) {
                            $this->needParamets[] = $paramet_key;
                        }
                    }
                }
            }
            return $currentInterface;
        }
        return false;
    }
    
    protected function setParamets($group) {
        if($group[$this->keys['pretreat']]) {
            $this->pretreats = array_merge($this->pretreats, $group[$this->keys['pretreat']]);
        }
    }
}
?>