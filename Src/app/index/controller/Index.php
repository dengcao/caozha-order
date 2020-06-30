<?php
/**
 * 源码名：caozha-order
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-order   or   Gitee：https://gitee.com/caozha/caozha-order
 */

namespace app\index\controller;

use think\facade\View;

class Index
{
    public function index()
    {
       return '<title>欢迎使用'.get_cz_name().' '.get_cz_version().'</title><style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:14px;} h1{ font-size: 32px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 18px }</style><div style="padding: 24px 48px;"> <h1>:) 欢迎使用</h1><p><br>'.get_cz_name().' '.get_cz_version().'<br><br>'.'后台管理地址：<a href="http://'.$_SERVER['HTTP_HOST'].url("admin/index/index").'" target="_blank">http://'.$_SERVER['HTTP_HOST'].url("admin/index/index").'</a> （账号：caozha &nbsp; 密码：123456）<br><br>草札官网：<a href="http://caozha.com" target="_blank">caozha.com</a><br><br>GitHub：<a href="https://github.com/cao-zha/caozha-order" target="_blank">https://github.com/cao-zha/caozha-order</a><br>Gitee：<a href="https://gitee.com/caozha/caozha-order" target="_blank">https://gitee.com/caozha/caozha-order</a><br><br>查看演示：<a href="'.url("/index/index/demo/sign/iphone11").'"  target="_blank" style="color:red;font-weight: bold;">下单表单页（default）</a> &nbsp; &nbsp; <a href="'.url("/index/index/demo/sign/mi10").'"  target="_blank" style="color:red;font-weight: bold;">下单表单页（green）</a> &nbsp; &nbsp; <a href="'.url("/index/index/demo/sign/p40pro").'"  target="_blank" style="color:red;font-weight: bold;">下单表单页（orangered）</a> &nbsp; &nbsp; </p></div>';
    }

    public function demo($sign)
    {
        View::assign([
            'sign' => $sign,
        ]);
        // 模板输出
        return View::fetch('index/demo');
    }

}
