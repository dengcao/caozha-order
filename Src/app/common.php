<?php
/**
 * 源码名：caozha-admin
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-admin (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-admin   or   Gitee：https://gitee.com/caozha/caozha-admin
 */

// 应用公共文件

//系统基础配置
$GLOBALS["caozha_common_config"] = [
    "name" => "caozha-order",
    "version" => "1.1.0",
];

/**
 * 获取应用入口之前的目录，格式如：/public/或/
 * @return string
 */
function get_cz_path(){
    return substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'], '/')+1);
}

/**
 * 获取草札后台框架的名字
 * @return string
 */
function get_cz_name(){
    global $caozha_common_config;
    return $caozha_common_config["name"];
}

/**
 * 获取草札后台框架的版本号
 * @return string
 */
function get_cz_version(){
    global $caozha_common_config;
    return $caozha_common_config["version"];
}
