<?php

/**
 * 通过 PhpStorm 创建.
 * 创建人: 21498
 * 日期: 2016/6/28
 * 时间: 11:14
 */
namespace LaravelAdmin\Logics;

use App\Models\Menu;
use Illuminate\Support\Facades\Route;
use LaravelAdmin\Facades\UserLogic;

class MenuLogicService{

    /**
     * 获取导航条
     * 返回: array|static
     */
    public function getNavbar(){
        $menu = $this->getNowMenu();
        $menu AND $menu->url = 'end';
        return $menu ? collect(Menu::parents($menu)->orderBy('left_margin')->get()->toArray())->push($menu) : collect([]);
    }

    /**
     * 获取当前菜单路由
     * @return mixed
     */
    public function getNowMenu(){
        $method = array_get(array_flip(Menu::getFieldsMap('method')->toArray()),strtolower(app('request')->method()));
        $route = app('request')->getPathInfo();
        $menu = Menu::where('url','=',$route)
            //->where('is_page','=',1)
            ->where('method','&',$method)
            ->orderBy('right_margin')
            ->first();
        if(!$menu){
            $route = Route::getCurrentRoute()->getCompiled()->getStaticPrefix(); //当前路由
            $menu = Menu::where('url','=',$route)
                //->where('is_page','=',1)
                ->where('method','&',$method)
                ->orderBy('right_margin')
                ->first(); //最底层路由
            if(!$menu){
                $menu = Menu::where('url','like',$route.'%')
                    //->where('is_page','=',1)
                    ->where('method','&',$method)
                    ->orderBy('right_margin')
                    ->first(); //最底层路由
            }
        }
        return $menu;
    }

    /**
     * 判断url是否在菜单目录里
     * @param $url
     * @param $menus
     * @return bool
     */
    public function isUrlInMenus($url,$menus,$method=1){
        $isIn = false;
        collect($menus)->each(function($item) use (&$isIn,$url,$method){
            if((strpos($item['url'],$url)===0 || strpos('/data'.$item['url'],$url)===0)&&(in_array($method,$item['method']))){
                $isIn = true;
            }
        });
        return $isIn;
    }


    /**
     * 获取自己的所有权限,
     * 并将传入的参数菜单列表选中
     * param $menus
     */
    public function getMainCheckedMenus($menus){
        $have = collect($menus)->pluck('id')->all();
        return collect(UserLogic::getUserInfo('menus'))->map(function($item) use ($have){
            if(in_array($item['id'],$have)){
                $item['checked'] = 1;
            }else{
                $item['checked'] = 0;
            }
            return $item;
        });
    }


    public function getPageMenus(){
        return Menu::where('is_page','=',1)->get();
    }
}