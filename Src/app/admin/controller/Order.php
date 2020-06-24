<?php
/**
 * 源码名：caozha-order
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-order   or   Gitee：https://gitee.com/caozha/caozha-order
 */

namespace app\admin\controller;

use app\admin\model\Order as OrderModel;
use think\Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Order
{
    protected $order_payment,$order_status,$order_client;

    protected $middleware = [
        'caozha_auth' => ['except' => ''],//验证是否管理员
    ];

    public function __construct()
    {
        cz_auth("order_view");//检测是否有权限
        $this->order_payment = Config::get("app.order_payment");
        $this->order_status = Config::get("app.order_status");
    }

    public function index()
    {
        $web_config = get_web_config();
        $limit = $web_config["order_limit"];
        if (!is_numeric($limit)) {
            $limit = 15;//默认显示15条
        }
        View::assign([
            'order_limit' => $limit,
            'order_payment' => $this->order_payment,
            'order_status' => $this->order_status,
        ]);
        // 模板输出
        return View::fetch('order/index');
    }

    public function recycle()//订单回收站
    {
        cz_auth("order_recycle");//检测是否有权限
        $web_config = get_web_config();
        $limit = $web_config["order_limit"];
        if (!is_numeric($limit)) {
            $limit = 15;//默认显示15条
        }
        View::assign([
            'order_limit' => $limit,
            'order_payment' => $this->order_payment,
            'order_status' => $this->order_status,
            'order_client' => $this->order_client,
        ]);
        // 模板输出
        return View::fetch('order/recycle');
    }


    public function view()
    {
        $order_id = Request::param("order_id", '', 'filter_sql');
        if (!is_numeric($order_id)) {
            caozha_error("参数错误", "", 1);
        }

        $is_del = Request::param("is_del");
        if (!is_numeric($is_del)) {
            $is_del = 0;
        }elseif ($is_del == 1){
            cz_auth("order_recycle");//检测是否有权限
        }

        $order = OrderModel::where("order_id", "=", $order_id)->where("is_del", "=", $is_del)->withAttr('status', function ($value) {
            try {
                $order_status=$this->order_status[$value];
            }catch(Exception $e){
                $order_status="";
            }
            return $order_status;
        })->withAttr('addresss', function ($value) {
            return str_ireplace("/@/"," ",$value);
        })->withAttr('payment', function ($value) {
            try {
                $order_payment=$this->order_payment[$value];
            }catch(Exception $e){
                $order_payment="";
            }
            return $order_payment;
        })->findOrEmpty();
        if ($order->isEmpty()) {
            caozha_error("[ID:" . $order_id . "]订单不存在。", "", 1);
        } else {

            View::assign([
                'order' => $order
            ]);

        }
        // 模板输出
        return View::fetch('order/view');
    }

    public function get()//获取订单数据
    {
        $page = Request::param("page");
        if (!is_numeric($page)) {
            $page = 1;
        }
        $limit = Request::param("limit");
        if (!is_numeric($limit)) {
            $web_config = get_web_config();
            $limit = $web_config["order_limit"];
            if (!is_numeric($limit)) {
                $limit = 15;//默认显示15条
            }
        }

        $list = Db::name('order');
        $list = $list->withAttr('status', function ($value) {
            try {
                $order_status=$this->order_status[$value];
            }catch(Exception $e){
                $order_status="";
            }
            return $order_status;
        })->withAttr('payment', function ($value) {
            try {
                $order_payment=$this->order_payment[$value];
            }catch(Exception $e){
                $order_payment="";
            }
            return $order_payment;
        })->withAttr('addresss', function ($value) {
            return str_ireplace("/@/"," ",$value);
        })->order('addtime', 'desc');

        $action = Request::param('', '', 'filter_sql');//过滤注入
        if (isset($action["keyword"])) {
            if ($action["keyword"] != "") {
                $list = $list->where("realname|tel|addresss|remarks|pro_name|pro_options|pro_sign|wechat|qq|email|postal_code|admin_remarks|pro_url|from_url", "like", "%" . $action["keyword"] . "%");
            }
        }
        $action_arr = ['status', 'payment'];
        foreach ($action_arr as $value) {
            if (isset($action[$value])) {
                if ($action[$value] != "") {
                    $list = $list->where($value, "=", $action[$value]);
                }
            }
        }

        $is_del = Request::param("is_del");
        if (!is_numeric($is_del)) {
            $is_del = 0;
        }elseif ($is_del == 1){
            cz_auth("order_recycle");//检测是否有权限
        }

        $list = $list->where("is_del", "=", $is_del);

        $is_export = Request::param("is_export");
        if($is_export==1){//导出excel
            cz_auth("order_export");//检测是否有权限
            $export_type = Request::param("export_type");
            $format_arr_data=array(
                "realname"=>"收货人",
                "gender"=>"性别",
                "tel"=>"电话",
                "addresss"=>"地址",
                "pro_name"=>"产品名称",
                "pro_options"=>"订购选项",
                "pro_sign"=>"产品标识符",
                "quantity"=>"订购数量",
                "amount"=>"订单金额",
                "payment"=>"付款方式",
                "remarks"=>"客户备注",
                "wechat"=>"微信号",
                "qq"=>"QQ",
                "email"=>"客户邮箱",
                "postal_code"=>"邮政编码",
                "addtime"=>"下单时间",
                "status"=>"订单状态",
                "admin_remarks"=>"管理员备注",
                "ip"=>"客户IP",
                "client"=>"客户端",
                "pro_url"=>"下单网址",
                "from_url"=>"访客来路",
            );
            return export_to_excel($list->select()->toArray(),$format_arr_data,$export_type,true);
        }

        $list = $list->paginate([
            'list_rows' => $limit,//每页数量
            'page' => $page,//当前页
        ]);
        //print_r(Db::getLastSql());
        return json($list);
    }

    public function todo()//操作数据
    {
        $order_id = Request::param("order_id", '', 'filter_sql');
        $act = Request::param("act", '', 'filter_sql');

        if($act=="del"){
            if(!is_cz_auth("order_del")){//检测是否有权限
                $list = array("code" => 0, "msg"=>"删除失败！您没有删除订单的权限。");
                $act="";
            }
        }elseif($act=="permanently_del"){
            if(!is_cz_auth("order_recycle")){//检测是否有权限
                $list = array("code" => 0, "msg"=>"删除失败！您没有彻底删除订单的权限。");
                $act="";
            }
        }elseif($act=="recover"){
            if(!is_cz_auth("order_recycle")){//检测是否有权限
                $list = array("code" => 0, "msg"=>"恢复失败！您没有恢复订单的权限。");
                $act="";
            }
        }else{
            if(!is_cz_auth("order_todo")){//检测是否有权限
                $list = array("code" => 0, "msg"=>"操作失败！您没有权限。");
                $act="";
            }
        }

        if($act=="del"){
            //删除
            $del_num = 0;
            if ($order_id) {
                //$del_num = OrderModel::where("order_id", "in", $order_id)->delete();
                $del_num = Db::name('order')->where('order_id', "in", $order_id)->update(['is_del' => 1]);//软删除
            }
            if ($del_num > 0) {
                write_syslog(array("log_content" => "软删除订单(ID)：" . $order_id));//记录系统日志
                $list = array("code" => 1, "msg"=>"删除成功，共删除了 <font color='red'>".$del_num."</font> 条订单！");
            } else {
                $list = array("code" => 0, "msg"=>"删除失败！");
            }
        }elseif($act=="status_1"){
            $todo_name="设订单为未处理";
            $do_num = 0;
            if ($order_id) {
                $do_num = Db::name('order')->where('order_id', "in", $order_id)->update(['status' => 1]);
            }
            if ($do_num > 0) {
                write_syslog(array("log_content" => $todo_name."(ID)：" . $order_id));//记录系统日志
            }
            $list = array("code" => 1, "msg"=>$todo_name."完成，共更新了 <font color='red'>".$do_num."</font> 条订单！");
        }elseif($act=="status_2"){
            $todo_name="设订单为已处理";
            $do_num = 0;
            if ($order_id) {
                $do_num = Db::name('order')->where('order_id', "in", $order_id)->update(['status' => 2]);
            }
            if ($do_num > 0) {
                write_syslog(array("log_content" => $todo_name."(ID)：" . $order_id));//记录系统日志
            }
            $list = array("code" => 1, "msg"=>$todo_name."完成，共更新了 <font color='red'>".$do_num."</font> 条订单！");
        }elseif($act=="status_3"){
            $todo_name="设订单为无效";
            $do_num = 0;
            if ($order_id) {
                $do_num = Db::name('order')->where('order_id', "in", $order_id)->update(['status' => 3]);
            }
            if ($do_num > 0) {
                write_syslog(array("log_content" => $todo_name."(ID)：" . $order_id));//记录系统日志
            }
            $list = array("code" => 1, "msg"=>$todo_name."完成，共更新了 <font color='red'>".$do_num."</font> 条订单！");
        }elseif($act=="recover"){
            $todo_name="恢复订单";
            $do_num = 0;
            if ($order_id) {
                $do_num = Db::name('order')->where('order_id', "in", $order_id)->update(['is_del' => 0]);
            }
            if ($do_num > 0) {
                write_syslog(array("log_content" => $todo_name."(ID)：" . $order_id));//记录系统日志
            }
            $list = array("code" => 1, "msg"=>$todo_name."完成，共更新了 <font color='red'>".$do_num."</font> 条订单！");
        }elseif($act=="permanently_del"){

        //彻底删除
        $del_num = 0;
        if ($order_id) {
            $del_num = OrderModel::where("order_id", "in", $order_id)->delete();
        }
        if ($del_num > 0) {
            write_syslog(array("log_content" => "彻底删除订单(ID)：" . $order_id));//记录系统日志
            $list = array("code" => 1, "msg"=>"删除成功，共删除了 <font color='red'>".$del_num."</font> 条订单！");
        } else {
            $list = array("code" => 0, "msg"=>"删除失败！");
        }

        }else{
            if(!$list){
                $list = array("code" => 0, "msg"=>"未定义操作！");
            }
        }


        return json($list);
    }



}
