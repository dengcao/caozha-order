<?php
/**
 * 源码名：caozha-admin
 * Copyright © 邓草 （官网：http://blog.5300.cn）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-admin (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/dengcao/caozha-admin   or   Gitee：https://gitee.com/dengzhenhua/caozha-admin
 */

namespace app\admin\controller;

use app\admin\model\Roles;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use think\facade\View;
use think\facade\Config;
use think\captcha\facade\Captcha;
use app\admin\model\Administrators as AdministratorsModel;

class Index
{
    protected $middleware = [
        'caozha_auth' => ['except' => 'login,checkLogin,captcha'],//验证管理员
    ];

    public function index()
    {
        // 赋值
        View::assign([
            'admin_name' => Session::get("admin_name"),
            'admin_id' => Session::get("admin_id"),
        ]);
        // 模板输出
        return View::fetch('index/index');
    }

    public function welcome()
    {
        $welcome_total = Cache::get('welcome_total');
        if (is_array($welcome_total)) {
            $order_total = $welcome_total["order_total"];
            $product_total = $welcome_total["product_total"];
            $syslog_total = $welcome_total["syslog_total"];
            $administrators_total = $welcome_total["administrators_total"];
        } else {

            $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀
            $order_query = Db::query("select count(*) as total from `" . $cz_prefix . "order`");
            $order_total = $order_query[0]["total"];
            $product_query = Db::query("select count(*) as total from `" . $cz_prefix . "product`");
            $product_total = $product_query[0]["total"];
            $syslog_query = Db::query("select count(*) as total from `" . $cz_prefix . "syslog`");
            $syslog_total = $syslog_query[0]["total"];
            $administrators_query = Db::query("select count(*) as total from `" . $cz_prefix . "administrators`");
            $administrators_total = $administrators_query[0]["total"];

            $welcome_total = array(
                "order_total" => $order_total,
                "product_total" => $product_total,
                "syslog_total" => $syslog_total,
                "administrators_total" => $administrators_total,
            );
            Cache::set('welcome_total', $welcome_total, 60*60);// 缓存在3600秒之后过期
        }

        // 赋值
        View::assign([
            'order_total' => $order_total,
            'product_total' => $product_total,
            'syslog_total' => $syslog_total,
            'administrators_total' => $administrators_total,
            'week_date_arr2str' => "",
            'week_product_arr2str' => "",
            'week_order_arr2str' => "",
        ]);
        // 模板输出
        return View::fetch('index/welcome');
    }

    public function welcome_echarts()//输出报表的一些配置
    {
        set_time_limit(0);//永不超时
        $welcome_echarts = Cache::get('welcome_echarts');
        if (is_array($welcome_echarts)) {
            $week_date_arr2str = $welcome_echarts["week_date_arr2str"];
            //$week_product_arr = $welcome_echarts["week_product_arr"];
            $week_order_arr = $welcome_echarts["week_order_arr"];
        } else {
            $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀

            //$week_date_arr=get_week('', 'Y-m-d');
            //$week_date_arr2str="'".implode("','",get_week('', 'm-d'))."'";
            $week_date_arr = get_dates(10, '', 'Y-m-d');
            $week_date_arr2str = "'" . implode("','", get_dates(10, '', 'm-d')) . "'";

//            $week_product_arr = array();
//            foreach ($week_date_arr as $key => $value) {
//                //$week_product_arr[]=\app\admin\model\Product::whereTime('updatetime', 'between', [$value." 00:00:00",$value." 23:59:59"])->paginate(['list_rows'=> 10])->total();
//                $product_query = Db::query("select count(*) as total from `" . $cz_prefix . "product` where `updatetime`>='" . $value . " 00:00:00' and `updatetime`<='" . $value . " 23:59:59'");
//                $week_product_arr[] = $product_query[0]["total"];
//            }
            $week_order_arr = array();
            foreach ($week_date_arr as $key => $value) {
                //$week_order_arr[]=\app\admin\model\Order::whereTime('addtime', 'between', [$value." 00:00:00",$value." 23:59:59"])->paginate(['list_rows'=> 10])->total();
                $order_query = Db::query("select count(*) as total from " . $cz_prefix . "order where `addtime`>='" . $value . " 00:00:00' and `addtime`<='" . $value . " 23:59:59'");
                $week_order_arr[] = $order_query[0]["total"];
            }

            $welcome_total = array(
                "week_date_arr2str" => $week_date_arr2str,
                //"week_product_arr" => $week_product_arr,
                "week_order_arr" => $week_order_arr,
            );
            Cache::set('welcome_echarts', $welcome_total, 60*60*24);// 缓存
        }

        echo "var week_date_arr2str=[".$week_date_arr2str."];\n
        var week_order_arr2str=[".implode(",", $week_order_arr)."];";
    }

    public function login()
    {
        // 模板输出
        return View::fetch('index/login');
    }

    public function menu()
    {
        // 模板输出
        return View::fetch('index/menu');
    }

    public function checkLogin()//验证登陆
    {
        if (!Request::isAjax()) {
            // 如果不是AJAX
            return result_json(0, "error");
        }
        $username = filter_sql(Request::param("username"));
        $password = filter_sql(Request::param("password"));
        $captcha = filter_sql(Request::param("captcha"));
        if (!$username) {
            return result_json(0, "请输入管理员账号。");
        }
        if (!$password) {
            return result_json(0, "请输入密码。");
        }
        // 检测输入的验证码是否正确
        if (!$captcha) {
            return result_json(0, "请输入验证码。");
        } elseif (!captcha_check($captcha)) {
            // 验证失败
            return result_json(0, "验证码错误，请刷新后重新输入。");
        }

        $admin = AdministratorsModel::where([['admin_name', '=', $username], ['admin_password', '=', md5_plus($password)]])->findOrEmpty();
        if ($admin->isEmpty()) {
            write_syslog(array("log_content" => "尝试使用管理员账号[" . $username . "]登陆失败，可能原因：账号或密码错误。", "log_user" => "未知"));//记录系统日志
            return result_json(0, "登陆失败，管理员账号或密码错误。");
        } else {
            if ($admin->is_enabled != 1) {
                write_syslog(array("log_content" => "尝试登陆失败，该管理员账号[" . $username . "]已被暂停。", "log_user" => $admin->admin_name . "(" . $admin->admin_id . "," . $admin->real_name . ")"));//记录系统日志
                return result_json(0, "登陆失败，该管理员账号已被暂停，如有疑问请联系技术。");
            }
            Session::set('admin_id', $admin->admin_id);
            Session::set('admin_name', $admin->admin_name);
            Session::set('role_id', $admin->role_id);
            Session::set('real_name', $admin->real_name);
            Session::set('pro_signs', $admin->pro_signs);//所属的产品标识符

            //更新管理员数据
            $admin->last_login_ip = getip();
            $admin->last_login_time = date("Y-m-d H:i:s", time());
            $admin->login_times = $admin->login_times + 1;
            $admin->save();//保存更新

            //session赋值给外部程序调用
            ini_set('session.gc_maxlifetime', "86400"); // 有效期，86400秒=24小时
            ini_set("session.cookie_lifetime", "86400");
            session_start();
            $_SESSION["caozha_admin_id"] = $admin->admin_id;
            $_SESSION["caozha_admin_name"] = $admin->admin_name;
            //end

            write_syslog(array("log_content" => "登陆成功"));//记录系统日志
            return result_json(1, "登陆成功！加载中，请稍候……");
        }

    }

    public function logout()//注销
    {
        $admin = AdministratorsModel::where("admin_id", "=", Session::get("admin_id"))->findOrEmpty();
        if (!$admin->isEmpty()) {
            //更新管理员数据
            $admin->last_logout_time = date("Y-m-d H:i:s", time());
            $admin->save();//保存更新
        }
        write_syslog(array("log_content" => "退出登陆"));//记录系统日志
        Session::clear();//清空

        //清空 （原session赋值给外部程序调用）
        session_start();
        if (isset($_SESSION["caozha_admin_id"])) {
            unset($_SESSION["caozha_admin_id"]);
        }
        if (isset($_SESSION["caozha_admin_name"])) {
            unset($_SESSION["caozha_admin_name"]);
        }
        //end

        caozha_success("退出登陆成功！", url("admin/index/login"));
    }

    public function cacheClear()//清空缓存
    {
        //Log::clear();//清空日志
        Cache::clear();//清空缓存
        array_map('unlink', glob(runtime_path() . 'temp\\' . '*.php'));//清除模版缓存 不删除 temp目录
        return result_json(1, "系统缓存清理成功！");
    }

    public function czInit()//初始化菜单
    {
        $init_config = Config::get("app.caozha_init_config");
        return json(json_decode($init_config));
    }

    public function captcha()//显示验证码
    {
        return Captcha::create();
    }

    public function mapMenus()//后台地图菜单
    {
        $init_config = Config::get("app.caozha_init_config");
        $menus = json_decode($init_config, true)["menuInfo"];
        $menus_data = tree_menus($menus);
        $menus_arr = array(
            "code" => 0,
            "msg" => "",
            "count" => count($menus_data),
            "data" => $menus_data
        );
        return json($menus_arr);
    }
}
