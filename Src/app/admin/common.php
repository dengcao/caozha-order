<?php
/**
 * 源码名：caozha-order
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-order   or   Gitee：https://gitee.com/caozha/caozha-order
 */

use app\admin\model\Roles;
use app\admin\model\WebConfig as WebConfigModel;
use think\facade\Config;
use think\facade\View;
use think\facade\Session;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;


//引入自动加载类
require_once "../vendor/autoload.php";
//使用Spreadsheet类
use PhpOffice\PhpSpreadsheet\Spreadsheet;
//xls格式类
use PhpOffice\PhpSpreadsheet\Writer\Xls;
//可以生成多种格式类
use PhpOffice\PhpSpreadsheet\IOFactory;


// 应用公共文件
/*if(!function_exists('cz_error')){
    }*/


/**
 *检查当前登陆用户执行操作的权限，如无权限输出警告。
 * @param string $role 权限标识
 */
function cz_auth($role)
{
    $role_id=Session::get("role_id");
    if(!$role_id){caozha_error("抱歉，登陆状态已失效，请重新登陆。", Request::header('referer'), 1);}
    $roles_data = get_roles($role_id);
    $authorize = explode(",", $roles_data["roles"]);
    $auth_config = Config::get("app.caozha_role_auths");
    if (!in_array($role, $authorize)) {
        $alert = '抱歉，您没有执行此操作的权限！<br><span style="font-size: 12px;color: #9c9da0;">【提示】此操作需要[' . $auth_config[$role]["name"] . ']的权限，您所在的权限组[' . $roles_data["role_name"] . ']没有此权限。</span>';
        caozha_error($alert, Request::header('referer'), 1);
    }
}

/**
 *检查当前登陆用户是否有某个标识的权限
 * @param string $role 权限标识
 * @return boolean
 */
function is_cz_auth($role)
{
    $role_id=Session::get("role_id");
    if(!$role_id){return false;}
    $roles_data = get_roles($role_id);
    $authorize = explode(",", $roles_data["roles"]);
    if (in_array($role, $authorize)) {
        return true;
    } else {
        return false;
    }
}

/**
 *获取某权限组的所有标识符
 * @param string $role_id 权限组ID
 * @return string
 */
function get_roles($role_id)
{
    if (!$role_id) {
        return array();
    }
    $roles_data = Cache::get('roles_data_' . $role_id);//优先从缓存读取
    if ($roles_data) {
        return $roles_data;
    } else {
        $roles = Roles::where('role_id', '=', $role_id)->findOrEmpty();
        if (!$roles->isEmpty()) {//获取该权限组所有标识符
            if ($roles->is_enabled == 1) {
                $roles_data = array("role_name" => trim($roles->role_name), "roles" => trim($roles->roles), "is_enabled" => $roles->is_enabled);
                Cache::set('roles_data_' . $role_id, $roles_data);
                return $roles_data;
            } else {
                //已停用
                return array();
            }
        } else {
            return array();
        }
    }
}

/**
 *获取系统设置数据
 * @return array
 */
function get_web_config()
{
    $web_config_data = Cache::get('web_config');
    if ($web_config_data) {
        return $web_config_data;
    } else {
        $web_config = WebConfigModel::where("id", ">=", 1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            return array();
        } else {
            $web_config_data = object_to_array($web_config->web_config);
            Cache::set('web_config', $web_config_data);
            return $web_config_data;
        }
    }
}


/**
 *记录系统操作日志
 * @param array $data_arr 插入的数据，格式：array("log_content"=>"","log_user"=>"","log_ip"=>"","log_datetime"=>"")，除log_content必填外其他可省略
 * @return string
 */
function write_syslog($data_arr)
{
    $data_arr = filter_sql_arr($data_arr);//过滤注入
    $data_arr["log_user"] = isset($data_arr["log_user"]) ? $data_arr["log_user"] : Session::get("admin_name") . "（ID:" . Session::get("admin_id") . "，姓名:" . Session::get("real_name") . "）";
    $data_arr["log_ip"] = isset($data_arr["log_ip"]) ? $data_arr["log_ip"] : getip();
    $data_arr["log_datetime"] = isset($data_arr["log_datetime"]) ? $data_arr["log_datetime"] : date("Y-m-d H:i:s", time());
    $data_arr["log_content"].="（".get_userbrowser()."，".get_userOS()."）";
    $log_id = Db::name('syslog')->insertGetId($data_arr);
    return $log_id;
}

/**
 *显示错误提示
 * @param string $alert 提示信息
 * @param string $url 点确定返回的URL
 * @param integer $is_exit 1立刻终止程序的执行
 * @return string
 */
function caozha_error($alert, $url, $is_exit = 0)
{
    View::assign([
        'alert' => $alert,
        'url' => $url
    ]);
    echo View::fetch('common/error');
    //redirect(url("admin/common/error")."?alert=".urlencode($alert)."&url=".urlencode($url));
    if ($is_exit == 1) {
        exit;
    }
}

/**
 *显示成功提示
 * @param string $alert 提示信息
 * @param string $url 点确定返回的URL
 * @param integer $is_exit 1立刻终止程序的执行
 * @return string
 */
function caozha_success($alert, $url, $is_exit = 0)
{
    View::assign([
        'alert' => $alert,
        'url' => $url
    ]);
    echo View::fetch('common/success');
    //redirect(url("admin/common/success")."?alert=".urlencode($alert)."&url=".urlencode($url));
    if ($is_exit == 1) {
        exit;
    }
}

/**
 *判断是否登陆管理员
 * @return boolean
 */
function is_login()
{
    $role_id = Session::get("role_id");
    $admin_id = Session::get("admin_id");
    $admin_name = Session::get("admin_name");
    if (!is_numeric($role_id) || !is_numeric($admin_id) || !$admin_name) {
        return false;
    } else {
        return true;
    }
}

/**
 *md5加强型，防止破解
 * @param string $str 点确定返回的URL
 * @return string
 */
function md5_plus($str)
{
    return md5("caozha.com|" . md5($str));
}


/**
 * 返回json格式的处理结果，主要用于ajax
 * @param string $code 状态码，1成功，0失败
 * @param string $msg 返回的信息
 * @return string
 */
function result_json($code, $msg)
{
    $str = array("code" => $code, "msg" => $msg);
    return json($str);
}

/**
 * 过滤参数，防SQL注入
 * @param string $str 接受的参数
 * @return string
 */
function filter_sql($str)
{
    $farr = array(
        //"/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dump/is"
        "/insert into|drop table|truncate|delete from/is"
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
 * 过滤HTML参数
 * @param string $str 接受的参数
 * @return string
 */
function filter_html($str)
{
    $farr = array(
        "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
        "/<(\\/?)(script|i?frame|style|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU"
    );
    $str = preg_replace($farr, '', $str);
    return trim(htmlspecialchars($str));
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
 * 后台地图，多维数组递归解析，输出菜单二维数组
 * @param array $arr 多维数组
 * @param integer $parentId 父ID
 * @return string
 */
function tree_menus($arr, $parentId = 0)
{
    global $tree_menus_arr, $treeID;
    if (!is_array($tree_menus_arr)) {
        $tree_menus_arr = array();
    }
    foreach ($arr as $key => $val) {
        $treeID += 1;//菜单ID
        $treePID = $parentId;//菜单父ID
        $tree_menus_arr[] = array(
            "treeID" => $treeID,
            "treePID" => $treePID,
            "title" => $val["title"],
            "href" => $val["href"],
            "icon" => $val["icon"],
            "target" => $val["target"]
        );
        if (isset($val["child"])) {
            if (is_array($val["child"])) { //如果键值是数组，则进行函数递归调用
                tree_menus($val["child"], $treeID);
            }
        }

    } //end foreach
    return $tree_menus_arr;
}

/**
 * 对象转数组
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj)
{
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }
    return $obj;
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
 * 导出到EXCEL
 * @param array $source_arr_data 导出的源内容，数组
 * @param array $format_arr_data 导出的列格式，数组
 * @param string $export_type 导出的数据类型，如csv,xls,xlsx等
 * @param boolean $is_syslog 是否记录系统日志
 * @return boolean or none
 */
function export_to_excel($source_arr_data,$format_arr_data,$export_type="csv",$is_syslog=false){
    if(!$source_arr_data || !$format_arr_data){return false;}
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    set_time_limit(0);
    $export_count=count($source_arr_data);
    if($export_type=="csv"){
        $csv_txt=implode(",",$format_arr_data);
        foreach ($source_arr_data as $k => $v) {
            $csv_txt_temp="";
            foreach ($format_arr_data as $k_temp => $v_temp) {
                $csv_txt_temp.=',"'.$v[$k_temp].'"';
            }
            $csv_txt.="\r\n".mb_substr($csv_txt_temp, 1);
        }
        $filename="客户订单(共".$export_count."条)_".date('YmdHis').".csv";
        if($is_syslog){
            write_syslog(array("log_content" => "下载订单：".$filename."，" . $_SERVER['QUERY_STRING']));//记录系统日志
        }
        return download($csv_txt, $filename, true);
    }elseif ($export_type=="xls" || $export_type=="xlsx"){

        //总结规律 设置参数的时候如果用$sheet $sheet->setTitle('Hello');而用$spreadsheet $spreadsheet->getActiveSheet()->setTitle('Hello'); 所有参数应该都可用这里两种方法
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //设置sheet的名字  两种方法
        //$sheet->setTitle('phpspreadsheet——demo');
        $sheet->setTitle('订单');
        //设置第一行小标题
        $k = 1;
        $list_i=0;
        foreach ($format_arr_data as $list_k => $list_v) {
            $list_i+=1;
            $sheet->setCellValue(getExcelValue($list_i).$k, $list_v);
            $sheet->getColumnDimension(getExcelValue($list_i))->setWidth(15);//设置列的宽度
            //$sheet->getColumnDimension(getExcelValue($list_i))->setAutoSize(true);//自动设置列宽
            $sheet->getStyle(getExcelValue($list_i).$k)->getFont()->setBold(true);// 一定范围内字体加粗
            $sheet->freezePane('A2');//固定首行
        }

        //设置A单元格的宽度 同理设置每个
        //$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        //设置第三行的高度
        //$spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(50);
        //A1水平居中
//        $styleArray = [
//            'alignment' => [
//                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
//            ],
//        ];
//        $sheet->getStyle('A1')->applyFromArray($styleArray);
        //将A3到D4合并成一个单元格
        //$spreadsheet->getActiveSheet()->mergeCells('A3:D4');
        //拆分合并单元格
        //$spreadsheet->getActiveSheet()->unmergeCells('A3:D4');
        //将A2到D8表格边框 改变为红色
//        $styleArray = [
//            'borders' => [
//                'outline' => [
//                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
//                    'color' => ['argb' => 'FFFF0000'],
//                ],
//            ],
//        ];
//        $sheet->getStyle('A2:D8')->applyFromArray($styleArray);
        //设置超链接
//        $sheet->setCellValue('D6', 'www.baidu.com');
//        $spreadsheet->getActiveSheet()->setCellValue('E6', 'www.baidu.com');
        //循环赋值
        $k = 2;
        foreach ($source_arr_data as $key => $value) {

            $list_i=0;
            foreach ($format_arr_data as $list_k => $list_v) {
                $list_i+=1;
                $sheet->setCellValue(getExcelValue($list_i).$k, $value[$list_k]);
            }
            $k++;
        }

        $filename="客户订单(共".$export_count."条)_".date('YmdHis').".".$export_type;
        if($is_syslog){
            write_syslog(array("log_content" => "下载订单：".$filename."，" . $_SERVER['QUERY_STRING']));//记录系统日志
        }

       //第一种保存方式
        /*$writer = new Xls($spreadsheet);
        //保存的路径可自行设置
        $file_name = '../'.$file_name . ".xls";
        $writer->save($file_name);*/
       //第二种直接页面上显示下载
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        if($export_type=="xls"){
            $writer = IOFactory::createWriter($spreadsheet, 'Xls'); //注意createWriter($spreadsheet, 'Xls') 第二个参数首字母必须大写
        }else{
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); //注意createWriter($spreadsheet, 'Xlsx') 第二个参数首字母必须大写
        }

        $writer->save('php://output');
    }

}


/**
 * 将数字$index转为excel列号
 * @param string $index 数字索引，比如：1
 * @return string
 */
function getExcelValue($index)
{
    $index = (int)$index;if ($index <= 0) return; //输入检测
    $dimension = ceil(log(25 * $index + 26, 26)) - 1;  //算结果一共有几位，实际算的是位数减1，记住是26进制的位数
    $n = $index - 26 * (pow(26, $dimension- 1) - 1) / 25; //算结果在所在位数总数中排第几个
    $n--; //转化为索引

    return str_pad(
        str_replace(
            array_merge(range(0, 9), range('a', 'p')),
            range('A', 'Z'), base_convert($n, 10, 26)
        ), $dimension, 'A', STR_PAD_LEFT
    ); //翻译加补齐
}


/**
 * 获取本周所有日期
 */
function get_week($time = '', $format='Y-m-d'){
    $time = $time != '' ? $time : time();
    //获取当前周几
    $week = date('w', $time);
    $date = [];
    for ($i=1; $i<=7; $i++){
        $date[$i] = date($format ,strtotime( '+' . $i-$week .' days', $time));
    }
    return $date;
}

/**
 * 获取最近$n天所有日期
 */
function get_dates($n=7,$time = '', $format='Y-m-d'){
    $time = $time != '' ? $time : time();
    //组合数据
    $date = [];
    for ($i=1; $i<=$n; $i++){
        $date[$i] = date($format ,strtotime( '+' . $i-$n .' days', $time));
    }
    return $date;
}