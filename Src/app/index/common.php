<?php
/**
 * 源码名：caozha-admin
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-admin (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-admin   or   Gitee：https://gitee.com/caozha/caozha-admin
 */

// 应用公共文件

use think\facade\Request;

/**
 * 过滤参数，防SQL注入
 * @param string $str 接受的参数
 * @return string
 */
function filter_sql($str)
{
    $farr = array(
        //"/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dump/is"
        "/select|insert|update|delete/is"
    );
    $str = preg_replace($farr, '', $str);
    return trim(addslashes($str));
}

/**
 * 过滤接受的参数或者数组,如$_GET,$_POST
 * @param array|string $arr 接受的参数或者数组
 * @return array|string
 */
function filter_sql_arr($arr)
{
    if (is_array($arr)) {
        foreach ($arr as $k => $v) {
            $arr[$k] = filter_sql($v);
        }
    } else {
        $arr = filter_sql($arr);
    }
    return $arr;
}


/**
 * 获取客户端IP
 * @return string
 */
function getip() { //获取客户端IP
    if ( isset($_SERVER[ "HTTP_CDN_SRC_IP" ]) ) { //获取网宿CDN真实客户IP
        return replace_ip( $_SERVER[ "HTTP_CDN_SRC_IP" ] );
    }
    if ( isset($_SERVER[ "HTTP_X_FORWARDED_FOR" ]) ) { //获取网宿、阿里云真实客户IP，参考：https://help.aliyun.com/knowledge_detail/40535.html
        return replace_ip( $_SERVER[ "HTTP_X_FORWARDED_FOR" ] );
    }
    if ( isset($_SERVER[ "HTTP_CLIENT_IP" ]) ) {
        return $_SERVER[ "HTTP_CLIENT_IP" ];
    }
    if ( isset($_SERVER[ "HTTP_X_FORWARDED" ]) ) {
        return $_SERVER[ "HTTP_X_FORWARDED" ];
    }
    if ( isset($_SERVER[ "HTTP_FORWARDED_FOR" ]) ) {
        return $_SERVER[ "HTTP_FORWARDED_FOR" ];
    }
    if ( isset($_SERVER[ "HTTP_FORWARDED" ]) ) {
        return $_SERVER[ "HTTP_FORWARDED" ];
    }
    $httpip = $_SERVER[ 'REMOTE_ADDR' ];
    if ( !preg_match( "/^(\d+)\.(\d+)\.(\d+)\.(\d+)$/", $httpip ) ) {
        $httpip = "127.0.0.1";
    }
    return $httpip;
}


/**
 * 拆分代理IP
 * @return string
 */
function replace_ip($ip)
{

    if (!$ip) {
        return "";
    }

    $httpip_array = explode(",", $ip);

    if ($httpip_array[0]) {

        return $httpip_array[0];

    } else {

        return $ip;

    }

}



/**
 * 获取浏览器以及版本号
 * @return string
 */
function get_userbrowser()
{
    $agent = Request::header('USER-AGENT');
    $browser = '';
    $browser_ver = '';

    if (preg_match('/UBrowser/i', $agent, $regs) || preg_match('/UC/i', $agent, $regs)) {
        $browser = 'UC浏览器';
        $browser_ver = '';
    }elseif (preg_match('/QQBrowser/i', $agent, $regs)) {
        $browser = 'QQ浏览器';
        $browser_ver = '';
    }elseif(preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'OmniWeb';
        $browser_ver = $regs[2];
    }elseif(preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Netscape';
        $browser_ver = $regs[2];
    }elseif(preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Safari';
        $browser_ver = $regs[1];
    }elseif(preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = 'IE';
        $browser_ver = $regs[1];
    }elseif(preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
        $browser = 'Opera';
        $browser_ver = $regs[1];
    }elseif(preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
        $browser = '(IE ' . $browser_ver . ') NetCaptor';
        $browser_ver = $regs[1];
    }elseif(preg_match('/Maxthon/i', $agent, $regs)) {
        $browser = '(IE ' . $browser_ver . ') Maxthon';
        $browser_ver = '';
    }elseif(preg_match('/360SE/i', $agent, $regs)) {
        $browser = '(IE ' . $browser_ver . ') 360SE';
        $browser_ver = '';
    }elseif(preg_match('/SE 2.x/i', $agent, $regs)) {
        $browser = '(IE ' . $browser_ver . ') 搜狗';
        $browser_ver = '';
    }elseif(preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'FireFox';
        $browser_ver = $regs[1];
    }elseif(preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Lynx';
        $browser_ver = $regs[1];
    }elseif(preg_match('/Chrome\/([^\s]+)/i', $agent, $regs)) {
        $browser = 'Chrome';
        $browser_ver = $regs[1];

    }

    if ($browser != '') {
        return $browser . ' ' . $browser_ver;
    } else {
        return 'unknow browser';
    }
}


/**
 * 获取客户端操作系统
 * @return string
 */
function get_userOS()
{
    $agent = Request::header('USER-AGENT');
    $os = false;
    if (strpos($agent, 'Android') !== false) {//strpos()定位出第一次出现字符串的位置，这里定位为0
        preg_match("/(?<=Android )[\d\.]{1,}/", $agent, $version);
        $os = 'Android '.$version[0];
    } elseif (strpos($agent, 'iPhone') !== false) {
        preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $agent, $version);
        $os = 'iPhone '.str_replace('_', '.', $version[0]);
    } elseif (strpos($agent, 'iPad') !== false) {
        preg_match("/(?<=CPU OS )[\d\_]{1,}/", $agent, $version);
        $os = 'iPad '.str_replace('_', '.', $version[0]);
    } else if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
        $os = 'Windows 95';
    } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
        $os = 'Windows ME';
    } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
        $os = 'Windows 98';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
        $os = 'Windows Vista';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
        $os = 'Windows 7';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
        $os = 'Windows 8';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
        $os = 'Windows 10';#添加win10判断
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
        $os = 'Windows XP';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
        $os = 'Windows 2000';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
        $os = 'Windows NT';
    } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
        $os = 'Windows 32';
    } else if (preg_match('/linux/i', $agent)) {
        $os = 'Linux';
    } else if (preg_match('/unix/i', $agent)) {
        $os = 'Unix';
    } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'SunOS';
    } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'IBM OS/2';
    } else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent)) {
        $os = 'Macintosh';
    } else if (preg_match('/PowerPC/i', $agent)) {
        $os = 'PowerPC';
    } else if (preg_match('/AIX/i', $agent)) {
        $os = 'AIX';
    } else if (preg_match('/HPUX/i', $agent)) {
        $os = 'HPUX';
    } else if (preg_match('/NetBSD/i', $agent)) {
        $os = 'NetBSD';
    } else if (preg_match('/BSD/i', $agent)) {
        $os = 'BSD';
    } else if (preg_match('/OSF1/i', $agent)) {
        $os = 'OSF1';
    } else if (preg_match('/IRIX/i', $agent)) {
        $os = 'IRIX';
    } else if (preg_match('/FreeBSD/i', $agent)) {
        $os = 'FreeBSD';
    } else if (preg_match('/teleport/i', $agent)) {
        $os = 'teleport';
    } else if (preg_match('/flashget/i', $agent)) {
        $os = 'flashget';
    } else if (preg_match('/webzip/i', $agent)) {
        $os = 'webzip';
    } else if (preg_match('/offline/i', $agent)) {
        $os = 'offline';
    } else {
        $os = 'Unknown';
    }
    return $os;
}

/**
 * 输出JS信息
 * @param string $js_code JS代码
 * @param boolean $is_back 是否返回上一页
 * @param boolean $is_exit 是否停止执行
 * @return string
 */
function echo_js($js_code,$is_back=true,$is_exit=true){
    echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta content=\"IE=10.000\" http-equiv=\"X-UA-Compatible\">
    <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0\">
    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\">
    <meta name=\"apple-touch-fullscreen\" content=\"yes\">
    <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black\">
    <meta name=\"MobileOptimized\" content=\"320\">
    <meta name=\"format-detection\" content=\"telephone=no\">
    <meta charset=\"UTF-8\"><title>提示</title></head>
<body><script>".$js_code."</script>";
    if($is_back){
        echo "<script>history.back();</script>";
    }
    echo "</body></html>";
    if($is_exit){exit();}
}