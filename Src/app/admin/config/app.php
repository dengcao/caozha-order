<?php
/**
 * 源码名：caozha-admin
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-admin (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-admin   or   Gitee：https://gitee.com/caozha/caozha-admin
 */

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 默认应用
    'default_app'      => 'admin',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => true,

    "order_payment"=>array(//订单付款方式
        "1"  =>  "货到付款",
        "2"  =>  "款到发货",
        "3"  =>  "在线支付",
    ),

    "order_status"=>array(//订单状态
        "1"  =>  "未处理",
        "2"  =>  "已处理",
        "3"  =>  "无效单",
    ),

    "product_status"=>array(//订单状态
        "1"  =>  "未审",
        "2"  =>  "已审",
        "3"  =>  "无效",
    ),

    //后台权限数组，开发过程中，必须把所有权限都列出来并与程序内部设定一致，以便验证。标识符必须保持唯一性，不能相同
    'caozha_role_auths'  => array(
        //格式为：'标识符' => array('name'=>'权限名','remarks'=>'权限说明'),
        'config'  =>  array('name'=>'网站配置','remarks'=>'管理网站名称、备案号等一些配置'),
        'roles'  =>  array('name'=>'权限组管理','remarks'=>'可以增删改权限组（拥有此权限相当于超级管理员）'),
        'admin'  =>  array('name'=>'管理员管理','remarks'=>'可以增删改管理员（拥有此权限相当于超级管理员）'),
        'log_view'  =>  array('name'=>'查看系统日志','remarks'=>'可以查看系统日志'),
        'log_del'  =>  array('name'=>'删除系统日志','remarks'=>'可以删除系统日志'),
        'mine'  =>  array('name'=>'修改自己资料','remarks'=>'可以查看修改自己的资料和密码'),
        'product'  =>  array('name'=>'产品管理','remarks'=>'可以增删改产品'),
        'order_view'  =>  array('name'=>'查看订单','remarks'=>'可以查看订单'),
        'order_todo'  =>  array('name'=>'设置订单状态','remarks'=>'可以设置订单状态'),
        'order_del'  =>  array('name'=>'删除订单','remarks'=>'可以删除订单'),
        'order_export'  =>  array('name'=>'导出订单','remarks'=>'可以导出订单'),
        'order_recycle'  =>  array('name'=>'订单回收站','remarks'=>'可以管理订单回收站'),
    ),

    //后台初始化菜单,json数据
    'caozha_init_config'=>'
 {
  "homeInfo": {
    "title": "首页",
    "href": "'.url("admin/index/welcome").'"
  },
  "logoInfo": {
    "title": "订单管理系统",
    "image": "'.get_cz_path().'static/admin/caozha/logo/logo.png",
    "href": ""
  },
  "menuInfo": [
    {
      "title": "常规管理",
      "icon": "fa fa-address-book",
      "href": "",
      "target": "_self",
      "child": [      
        {
          "title": "系统设置",
          "href": "'.url("admin/WebConfig/index").'",
          "icon": "fa fa-gears",
          "target": "_self"
        },
        {
          "title": "管理员",
          "href": "",
          "icon": "fa fa-user-circle",
          "target": "_self",
          "child": [
            {
              "title": "管理员",
              "href": "'.url("admin/administrators/index").'",
              "icon": "fa fa-user-circle-o",
              "target": "_self"
            },
            {
              "title": "权限组",
              "href": "'.url("admin/roles/index").'",
              "icon": "fa fa-users",
              "target": "_self"
            }
          ]
        },
        {
          "title": "订单管理",
          "href": "'.url("admin/order/index").'",
          "icon": "fa fa-list",
          "target": "_self"
        },
        {
          "title": "产品管理",
          "href": "'.url("admin/product/index").'",
          "icon": "fa fa-product-hunt",
          "target": "_self"
        },
        {
          "title": "订单回收站",
          "href": "'.url("admin/order/recycle").'",
          "icon": "fa fa-trash-o",
          "target": "_self"
        },
        {
          "title": "系统日志",
          "href": "'.url("admin/syslog/index").'",
          "icon": "fa fa-file-text",
          "target": "_self"
        }
        
      ]
    },
    {
      "title": "其他管理",
      "icon": "fa fa-slideshare",
      "href": "",
      "target": "_self",
      "child": [
        {
          "title": "后台地图",
          "href": "'.url("admin/index/menu").'",
          "icon": "fa fa-map-signs",
          "target": "_self"
        }
      ]
    }
  ]
}
',



];
