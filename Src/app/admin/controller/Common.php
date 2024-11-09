<?php
/**
 * 源码名：caozha-admin
 * Copyright © 邓草 （官网：http://blog.5300.cn）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-admin (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/dengcao/caozha-admin   or   Gitee：https://gitee.com/dengzhenhua/caozha-admin
 */

namespace app\admin\controller;

use think\facade\Request;
use think\facade\View;

class Common
{

    function error()//显示错误信息
    {
        $alert = Request::param('alert');
        $url = Request::param('url');
        View::assign([
            'alert' => $alert,
            'url' => $url
        ]);
        return View::fetch('common/error');
    }

    function success()//显示成功信息
    {
        $alert = Request::param('alert');
        $url = Request::param('url');
        View::assign([
            'alert' => $alert,
            'url' => $url
        ]);
        return View::fetch('common/success');
    }

    function confirm()//确认信息
    {
        $alert = Request::param('alert');
        $js_code = Request::param('js_code');
        View::assign([
            'alert' => $alert,
            'js_code' => $js_code
        ]);
        return View::fetch('common/confirm');
    }

}
