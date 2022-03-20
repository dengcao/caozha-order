<?php
/**
 * 源码名：caozha-order
 * Copyright © 邓草 （官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-order   or   Gitee：https://gitee.com/caozha/caozha-order
 */

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;
use app\admin\model\Product as ProductModel;
use think\facade\Cache;

class Product
{
    protected $product_status;

    protected $middleware = [
        'caozha_auth' 	=> ['except' => '' ],//验证是否管理员
    ];

    public function __construct(){
        cz_auth("product");//检测是否有权限
        $this->product_status = Config::get("app.product_status");
    }

    public function index()
    {
        $web_config=get_web_config();
        $limit=$web_config["product_limit"];
        if(!is_numeric($limit)){
            $limit=15;//默认显示15条
        }

        View::assign([
            'product_limit'  => $limit,
            'product_status'  => $this->product_status,
        ]);
        // 模板输出
        return View::fetch('product/index');
    }

    public function add()
    {
        View::assign([
            'product_status' => $this->product_status,
            'order_templates' => $this->getTemplates(),
            'order_payment' => Config::get("app.order_payment"),
        ]);
        // 模板输出
        return View::fetch('product/add');
    }

    public function addSave()
    {
        if(!Request::isAjax()){
            // 如果不是AJAX
            return result_json(0,"error");
        }
        $update_data=Request::param('','','filter_sql');//过滤注入
        $update_data["status"]=isset($update_data["status"])?$update_data["status"]:1;
        $update_data["tips_type"]=isset($update_data["tips_type"])?$update_data["tips_type"]:1;
        $update_data["pro_payment_checked"]=isset($update_data["pro_payment_checked"])?$update_data["pro_payment_checked"]:0;
        $update_data["pro_payment"]=isset($update_data["pro_payment"])?implode(",",$update_data["pro_payment"]):"";
        $update_data["is_captcha"]=isset($update_data["is_captcha"])?$update_data["is_captcha"]:0;
        $update_data["anti_time"]=is_numeric($update_data["anti_time"])?$update_data["anti_time"]:0;
        $update_data["anti_num"]=is_numeric($update_data["anti_num"])?$update_data["anti_num"]:0;

        if(!$update_data["pro_sign"]){
            return json(array("code"=>0,"update_num"=>0,"msg"=>"标识符不能为空"));
        }

        $product=ProductModel::where("pro_sign","=",$update_data["pro_sign"])->findOrEmpty();
        if (!$product->isEmpty()) {
            return json(array("code"=>0,"update_num"=>0,"msg"=>"产品标识符 ".$update_data["pro_sign"]." 已经存在，请换个再提交。"));
        }

        $update_data["updatetime"]=date("Y-m-d H:i:s",time());

        $pro_id = Db::name('product')->insertGetId($update_data);

        if($pro_id>0){
            write_syslog(array("log_content"=>"新增产品，ID：".$pro_id));//记录系统日志
            $list=array("code"=>1,"update_num"=>1,"msg"=>"添加成功");
        }else{
            $list=array("code"=>0,"update_num"=>0,"msg"=>"添加失败");
        }
        return json($list);
    }

    public function edit()
    {
        $pro_id=Request::param("pro_id",'','filter_sql');
        if(!is_numeric($pro_id)){
            caozha_error("参数错误","",1);
        }
        $product=ProductModel::where("pro_id","=",$pro_id)->findOrEmpty();
        if ($product->isEmpty()) {
            caozha_error("[ID:".$pro_id."]产品不存在。","",1);
        }

        View::assign([
            'product_status' => $this->product_status,
            'product'  => $product,
            'order_templates' => $this->getTemplates(),
            'order_payment' => Config::get("app.order_payment"),
            'pro_payment' => explode(",",$product->pro_payment),
        ]);

        // 模板输出
        return View::fetch('product/edit');
    }

    public function editSave()
    {
        if(!Request::isAjax()){
            // 如果不是AJAX
            return result_json(0,"error");
        }
        $update_data=Request::param('','','filter_sql');//过滤注入
        if(!is_numeric($update_data["pro_id"])){
            caozha_error("参数错误","",1);
        }

        $update_data["status"]=isset($update_data["status"])?$update_data["status"]:1;
        $update_data["tips_type"]=isset($update_data["tips_type"])?$update_data["tips_type"]:1;
        $update_data["pro_payment_checked"]=isset($update_data["pro_payment_checked"])?$update_data["pro_payment_checked"]:0;
        $update_data["pro_payment"]=isset($update_data["pro_payment"])?implode(",",$update_data["pro_payment"]):"";
        $update_data["is_captcha"]=isset($update_data["is_captcha"])?$update_data["is_captcha"]:0;
        $update_data["anti_time"]=is_numeric($update_data["anti_time"])?$update_data["anti_time"]:0;
        $update_data["anti_num"]=is_numeric($update_data["anti_num"])?$update_data["anti_num"]:0;

        if(!$update_data["pro_sign"]){
            return json(array("code"=>0,"update_num"=>0,"msg"=>"标识符不能为空"));
        }

        $product_check=ProductModel::where("pro_sign","=",$update_data["pro_sign"])->where("pro_id","<>",$update_data["pro_id"])->findOrEmpty();
        if (!$product_check->isEmpty()) {
            return json(array("code"=>0,"update_num"=>0,"msg"=>"产品标识符 ".$update_data["pro_sign"]." 已经存在，请换个再提交。"));
        }

        $update_field=['pro_name','pro_options','pro_sign','hits','listorder','status','templates','tips_type','tips_text','tips_url','pro_payment','pro_payment_checked','is_captcha','anti_time','anti_num'];//允许更新的字段
        $product=ProductModel::where("pro_id","=",$update_data["pro_id"])->findOrEmpty();
        if ($product->isEmpty()) {//数据不存在
            $update_result=false;
        }else{
            $update_result=$product->allowField($update_field)->save($update_data);
        }

        if($update_result){
            write_syslog(array("log_content"=>"修改产品，ID：".$update_data["pro_id"]));//记录系统日志
            Cache::delete('product_data_' . $update_data["pro_sign"]);//删除缓存
            $list=array("code"=>1,"update_num"=>1,"msg"=>"保存成功");
        }else{
            $list=array("code"=>0,"update_num"=>0,"msg"=>"保存失败");
        }
        return json($list);
    }

    public function view()
    {
        $web_config=get_web_config();

        $pro_id=Request::param("pro_id",'','filter_sql');
        if(!is_numeric($pro_id)){
            caozha_error("参数错误","",1);
        }
        $product=ProductModel::where("pro_id","=",$pro_id)->findOrEmpty();
        if ($product->isEmpty()) {
            caozha_error("[ID:".$pro_id."]产品不存在。","",1);
        }else{
            View::assign([
                'product'  => $product,
                'order_url' => $web_config["share_url"].url("/index/order/view/sign/".$product->pro_sign),
            ]);
        }
        // 模板输出
        return View::fetch('product/view');
    }

    public function get()//获取数据
    {
        $page=Request::param("page");
        if(!is_numeric($page)){$page=1;}
        $limit=Request::param("limit");
        if(!is_numeric($limit)){
            $web_config=get_web_config();
            $limit=$web_config["product_limit"];
            if(!is_numeric($limit)){
                $limit=15;//默认显示15条
            }
        }

        $list=Db::name('product');
        $list=$list->withAttr('status', function($value) {
            return $this->product_status[$value];
        })->withAttr('tips_type', function($value) {
            $tips_type = [1=>'弹框后跳转',2=>'直接跳转',3=>'成功页跳转'];
            return $tips_type[$value];
        })->withAttr('is_captcha', function($value) {
            $is_captcha = [1=>'开启',0=>'关闭'];
            return $is_captcha[$value];
        })->order('pro_id', 'desc');

        $action=Request::param('','','filter_sql');//过滤注入
        if(isset($action["keyword"])){
            if($action["keyword"]!=""){
                $list=$list->where("pro_name|pro_options|pro_sign|templates|tips_text|tips_url","like","%".$action["keyword"]."%");
            }
        }
        $action_arr=['status'];
        foreach ($action_arr as $value){
            if(isset($action[$value])){
                if($action[$value]!="") {
                    $list = $list->where($value, "=", $action[$value]);
                }
            }
        }

        $list=$list->paginate([
            'list_rows'=> $limit,//每页数量
            'page' => $page,//当前页
        ]);
        //print_r(Db::getLastSql());
        return json($list);
    }

    public function delete()//删除数据
    {
        //执行删除
        $pro_id=Request::param("pro_id",'','filter_sql');
        $del_num=0;
        if($pro_id){
            $del_num=ProductModel::where("pro_id","in",$pro_id)->delete();
        }
        if($del_num>0){
            write_syslog(array("log_content"=>"删除产品(ID)：".$pro_id));//记录系统日志
            $list=array("code"=>1,"del_num"=>$del_num);
        }else{
            $list=array("code"=>0,"del_num"=>0);
        }

        return json($list);
    }

    public function getTemplates(){//获取订单模板数组
        $tpl_path=base_path()."index/view/order/";
        $tpl_arr=array();
        if(is_dir($tpl_path)){
            $tpl_dir=scandir($tpl_path);//遍历目录，获取文件名
            foreach($tpl_dir as $value){
                if(substr($value,0,1)=="."){//判断是否目录，去除目录
                    continue;
                }
                $tpl_arr[]=str_ireplace(".html","",$value);
            }
        }
        return $tpl_arr;
    }

}
