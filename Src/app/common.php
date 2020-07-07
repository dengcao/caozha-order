<?php
/**
 * 源码名：caozha-order
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-order   or   Gitee：https://gitee.com/caozha/caozha-order
 */

// 应用公共文件

//应用的名称及版本
$GLOBALS["caozha_common_config"] = [
    "name" => "caozha-order",
    "version" => "1.7.1",
    "gitee" => "caozha/caozha-order",
    "github" => "cao-zha/caozha-order",
];

//caozha-admin 程序名称及版本，用于标识和升级，勿删改
$GLOBALS["caozha_admin_sys"] = array(
    "name" => "caozha-admin",
    "version" => "1.6.0",
    "url" => "https://gitee.com/caozha/caozha-admin",
);

/**
 * 获取应用入口之前的目录，格式如：/public/或/
 * @return string
 */
function get_cz_path(){
    return substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'], '/')+1);
}

/**
 * 获取系统应用的名字
 * @return string
 */
function get_cz_name(){
    global $caozha_common_config;
    return $caozha_common_config["name"];
}

/**
 * 获取系统应用的版本号
 * @return string
 */
function get_cz_version(){
    global $caozha_common_config;
    return $caozha_common_config["version"];
}
