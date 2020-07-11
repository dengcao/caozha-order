<?php
/**
 * 源码名：caozha-admin
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-admin (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-admin   or   Gitee：https://gitee.com/caozha/caozha-admin
 */

namespace app\admin\controller;

use app\admin\model\WebConfig as WebConfigModel;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class WebConfig
{
    protected $middleware = [
        'caozha_auth' => ['except' => ''],//验证是否管理员
    ];

    public function __construct(){
        cz_auth("config");//检测是否有权限
    }

    public function index()
    {
        $web_config=WebConfigModel::where("id",">=",1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。","",1);
        }else{
            $web_config_data=object_to_array($web_config->web_config);
            View::assign([
                'web_config'  => $web_config_data
            ]);
        }
        // 模板输出
        return View::fetch('web_config/index');
    }

    public function save()
    {
        if (!Request::isAjax()) {
            // 如果不是AJAX
            return result_json(0, "error");
        }
        $edit_data = Request::param('', '', 'filter_sql');//过滤注入
        $edit_data["order_upload_limit"]=is_numeric($edit_data["order_upload_limit"])?$edit_data["order_upload_limit"]:20;
        $edit_data["order_upload_memory_limit"]=is_numeric($edit_data["order_upload_memory_limit"])?$edit_data["order_upload_memory_limit"]:1000;

        $edit_data=array("web_config"=>$edit_data);

        $update = WebConfigModel::where("id", ">=", 1)->limit(1)->findOrEmpty();
        if ($update->isEmpty()) { //数据不存在
            $update_result = false;
        } else {
            $update_result = $update->save($edit_data);
        }

        if ($update_result) {
            write_syslog(array("log_content"=>"修改系统设置"));//记录系统日志
            $list = array("code" => 1, "update_num" => 1, "msg" => "保存成功");
            Cache::delete('web_config'); //清空缓存
        } else {
            $list = array("code" => 0, "update_num" => 0, "msg" => "保存失败");
        }
        return json($list);
    }

    public function getFields()//获取检测重复数据的字段名称数组
    {
        $is_own_key = "LAY_CHECKED";
        $is_own_ok = true;
        $is_own_no = false;
        $order_repeat_check_field_arr=[];

        $web_config=WebConfigModel::where("id",">=",1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。","",1);
        }else{
            $web_config_data=object_to_array($web_config->web_config);
            $order_repeat_check_field_arr=explode(",",$web_config_data["order_repeat_check_fields"]);
        }

        //获取所有字段名
        $fields_arr=array();
        $cz_prefix=config('database.connections.mysql.prefix');//数据表前缀
        $row_field = Db::query("SHOW FULL COLUMNS FROM ".$cz_prefix."order");
        //print_r($row_field);exit();
        foreach ($row_field as $value){
            $fields_arr[]=array(
                "Field"=>$value["Field"],
                "Comment"=>$value["Comment"],
            );
        }

        $list=[];

        foreach ($fields_arr as $key => $val) {

            if(in_array($val["Field"],$order_repeat_check_field_arr)) {//判断是否包含
                $is_own = $is_own_ok;
            } else {
                $is_own = $is_own_no;
            }

            $list[] = array($is_own_key => $is_own, "field" => $val["Field"], "comment" => $val["Comment"]);
        }

        return json($list);
    }

    public function getExportFields()//获取导出字段名称数组
    {
        $is_own_key = "LAY_CHECKED";
        $is_own_ok = true;
        $is_own_no = false;
        $order_export_fields_arr=[];

        $web_config=WebConfigModel::where("id",">=",1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。","",1);
        }else{
            $web_config_data=object_to_array($web_config->web_config);
            $order_export_fields_arr=explode(",",$web_config_data["order_export_fields"]);
        }

        //获取所有字段名
        $fields_arr=array();
        $cz_prefix=config('database.connections.mysql.prefix');//数据表前缀
        $row_field = Db::query("SHOW FULL COLUMNS FROM ".$cz_prefix."order");
        //print_r($row_field);exit();
        foreach ($row_field as $value){
            $fields_arr[]=array(
                "Field"=>$value["Field"],
                "Comment"=>$value["Comment"],
            );
        }

        $list=[];

        foreach ($fields_arr as $key => $val) {

            if(in_array($val["Field"],$order_export_fields_arr)) {//判断是否包含
                $is_own = $is_own_ok;
            } else {
                $is_own = $is_own_no;
            }

            $list[] = array($is_own_key => $is_own, "field" => $val["Field"], "comment" => $val["Comment"]);
        }

        return json($list);
    }

    public function order_reset()//重置订单重复数据
    {
        $res=Db::name('order')->where('id',">",0)->update(['is_repeat' => 0]);
        write_syslog(array("log_content" => "重置订单重复数据。"));//记录系统日志
        caozha_success("已成功重置订单重复数据，共更新了".$res."条订单数据。",url("admin/WebConfig/index"),1);
    }

}
