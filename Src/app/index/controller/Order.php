<?php
/**
 * 源码名：caozha-order
 * Copyright © 2020 草札 （草札官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-order   or   Gitee：https://gitee.com/caozha/caozha-order
 */

namespace app\index\controller;

use think\facade\Request;
use think\facade\Db;
use think\facade\View;
use app\index\model\Product as ProductModel;
use think\facade\Cache;

class Order
{
    public function view($sign){
        $sign=filter_sql($sign);
        $product = Cache::get('product_data_' . $sign);//优先从缓存读取
        if (!$product) {
            $product=ProductModel::where("pro_sign","=",$sign)->findOrEmpty();
            Cache::set('product_data_' . $sign, $product);
        }
        View::assign([
            'product' => $product,
        ]);
        // 模板输出
        return View::fetch('order/'.$product->templates);
    }

    public function save()
    {
        $update_data=Request::param('','','filter_sql');//过滤注入
        $update_data["payment"]=isset($update_data["payment"])?$update_data["payment"]:1;
        $update_data["quantity"]=isset($update_data["quantity"])?$update_data["quantity"]:1;
        $update_data["amount"]=isset($update_data["amount"])?$update_data["amount"]:0;

        if(!$update_data["pro_name"]){
            echo_js("alert('产品名称不能为空。');");
        }elseif(!$update_data["pro_sign"]){
            echo_js("alert('产品标识符不能为空。');");
        }elseif(!$update_data["pro_options"]){
            echo_js("alert('请选择订购选项。');");
        }elseif(!$update_data["addresss"]){
            echo_js("alert('请填写地址。');");
        }elseif(!$update_data["realname"]){
            echo_js("alert('请填写姓名。');");
        }elseif(!$update_data["tel"]){
            echo_js("alert('请填写手机号码。');");
        }

        $product=ProductModel::where("pro_sign","=",$update_data["pro_sign"])->findOrEmpty();

        $insert_data=array(
            "pro_name"=>$update_data["pro_name"],
            "pro_sign"=>$update_data["pro_sign"],
            "pro_url"=>$update_data["pro_url"],
            "from_url"=>$update_data["from_url"],
            "pro_options"=>$update_data["pro_options"],
            "addresss"=>$update_data["addresss"],
            "payment"=>$update_data["payment"],
            "quantity"=>$update_data["quantity"],
            "amount"=>$update_data["amount"],
            "realname"=>$update_data["realname"],
            "tel"=>$update_data["tel"],
            "remarks"=>$update_data["remarks"],
            "ip"=>getip(),
            "client"=>get_userOS()."（".get_userbrowser()."）",
        );

        $order_id = Db::name('order')->insertGetId($insert_data);

        if($order_id>0){
            if($product->tips_type==1){
                if(!$product->tips_text){
                    $product->tips_text="订单提交成功！我们会尽快给您发货，谢谢您的支持！";
                }
                echo_js("alert('".$product->tips_text."');window.location.href='".$_SERVER["HTTP_REFERER"]."';",false);
            }elseif($product->tips_type==2){
                if(!$product->tips_url){
                    $product->tips_url=$_SERVER["HTTP_REFERER"];
                }
                echo_js("window.location.href='".$product->tips_url."';",false);
            }

        }else{
            echo_js("alert('订单提交失败，请联系我们客服。');");
        }
    }

}