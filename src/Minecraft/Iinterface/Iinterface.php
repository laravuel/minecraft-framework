<?php
namespace Minecraft\Iinterface;

class Iinterface {
    
    static $route;
    static $interfaces;
    
    protected $resData = [];
    public $pager = [];
    
    public function __construct() {
        $this->pager['page'] = intval($_GET['page']) ? intval($_GET['page']) : 0;
        $this->pager['length'] = intval($_GET['length']) ? intval($_GET['length']) : 10;
        $this->pager['start'] = $this->pager['page'] * $this->pager['length'] - $this->pager['length'];
        $this->pager = json_decode(json_encode($this->pager));
    }
    
    public static function init(\Minecraft\Router\Route $route) {

        if(!$route->route) {
            // 抛出一个错误异常类 ??????
            self::res(['res'=>404, 'msg'=>'请求错误']);
        }
        self::$route = $route;
        return new static;
    }
    
    public function validate($fieldMsg) {
        if(!self::$route->needParamets) {
            return true;
        }
        foreach(self::$route->needParamets as $v) {
            self::res(['res'=>1, 'msg'=>$fieldMsg[$v]]);
        }
    }
    
    public function load(\Minecraft\Front\Pretreat $pretreat) {
        /* 是否需要预处理 */
        if(self::$route->pretreats) {
            $this->removeElement(self::$route->pretreats);
            $pretreat->init(self::$route, $this);
        }
        $space = \Minecraft\App::$config['interfaces'];
        list($path, $method) = explode('.', self::$route->route['interface']);
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

        if(!self::$interfaces[$class]) {
            self::$interfaces[$class] = new $class();
        }
        
        $resData = call_user_func_array(array(self::$interfaces[$class], $method), self::$route->paramets);
        
        self::res($resData);
    }
    
    /**
     * 移除路由所定义的元素
     */
    protected function removeElement(&$datas) {
        $newFronts = [];
        foreach($datas as $key=>$data) {
            if($data[0] == self::$route->keys['del_flg']) {
                $delFront = substr($data, 1, strlen($data));
                for($i = 0; $i < $key; $i++) {
                    if($datas[$i] == $delFront) {
                        unset($datas[$i]);
                    }
                }
                unset($datas[$key]);
            }
        }
    }
    
    /**
     * 返回响应数据
     */
    public static function res($resData) {
        if(!is_array($resData)) {
            $resData = ['res'=>0];
        }

        $resData['res'] = $resData['res'] ? $resData['res'] : 0;
        $resData['msg'] = $resData['msg'] ? $resData['msg'] : '';
        $resData['url'] = $resData['url'] ? $resData['url'] : '';
        $newResData = $resData;
        unset($resData['res']);
        unset($resData['msg']);
        unset($resData['url']);
        
        //$resData = $this->filter(self::$route->resFields, []);
        
        $class = str_replace('\\', '_', $class);
        $data = [
            'res' => $newResData['res'], 
            'msg' => $newResData['msg'],
            'url' => \Minecraft\App::$config['WebPath'].$newResData['url'],
            // 'result' => [self::$route->resName => $resData]
            'result' => $resData
        ];
        exit(json_encode($data));
    }
    
    /**
     * 当预处理return数据时，执行该方法
     * @data 预处理所返回的数据
     */
    public function resPretreat($data) {
        self::res($data);
    }
    
    protected function filter($resFields, $filterData) {
        if(!$resFields || !is_array($resFields) || count($resFields) == 0) {
            return false;
        }
        /*
        foreach($this->resData as $key=>$data) {
            if(!in_array($key, $resFields)) {
                unset($this->resData[$key]);
            }
            else {
                if(is_array($this->resData[$key])) {
                    
                }
            }
        }
        */
        foreach($resFields as $key=>$field) {
            if(is_array($field)) {
                $this->filter($field, $filterData);
            }
            else {
                $filterData[$field] = $this->resData[$field];
            }
            
        }
        return $filterData;
    }
}