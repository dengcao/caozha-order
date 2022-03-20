<?php
/**
 * 源码名：caozha-order
 * Copyright © 邓草 （官网：http://caozha.com）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/cao-zha/caozha-order   or   Gitee：https://gitee.com/caozha/caozha-order
 */

namespace app\index\controller;

use think\captcha\facade\Captcha;
use think\facade\Config;
use think\facade\Request;
use think\facade\Db;
use think\facade\View;
use app\index\model\Product as ProductModel;
use app\index\model\Order as OrderModel;
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

        $pro_payment_arr=array();
        if($product->pro_payment){
            $order_payment=Config::get("app.order_payment");
            $pro_payment=explode(",",$product->pro_payment);
            foreach ($pro_payment as $key=>$value){
                $pro_payment_arr[$value]=$order_payment[$value];
            }
        }
        View::assign([
            'product' => $product,
            'pro_payment' => $pro_payment_arr,
        ]);
        // 模板输出
        return View::fetch('order/'.$product->templates);
    }

    public function save()
    {
        $update_data=Request::param('','','filter_sql');//过滤注入
        $update_data["payment"]=isset($update_data["payment"])?$update_data["payment"]:0;
        $update_data["quantity"]=isset($update_data["quantity"])?$update_data["quantity"]:1;
        $update_data["amount"]=isset($update_data["amount"])?$update_data["amount"]:0;
        $update_data["pro_url"]=isset($update_data["pro_url"])?urldecode($update_data["pro_url"]):"";
        $update_data["from_url"]=isset($update_data["from_url"])?urldecode($update_data["from_url"]):"";

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
        if ($product->isEmpty()) {//产品不存在
            echo_js("alert('产品标识符错误。');");
        }

        // 检测输入的验证码是否正确
        if ($product->is_captcha==1) {
            $captcha = $update_data["captcha"];
            if (!$captcha) {
                echo_js("alert('请输入验证码。');");
            } elseif (!captcha_check($captcha)) {
                // 验证失败
                echo_js("alert('验证码错误，请点击验证码刷新后再重新输入。');");
            }
        }

        // 防攻击：同一个IP或手机号某分钟内最多允许提交多少次
        $anti_time=$product->anti_time;
        if (is_numeric($anti_time) && $anti_time>0) {
            $anti_num=$product->anti_num;
            $anti_now_time=time();
            $anti_start_time=date("Y-m-d H:i:s",($anti_now_time-$anti_time*60));
            $anti_end_time=date("Y-m-d H:i:s",$anti_now_time);
            $order_total=OrderModel::where(function ($query) use ($update_data) {
                //解决生成的SQL语句不自动加括号的问题
                $query->whereOr([['ip','=',getip()],['tel','=',$update_data["tel"]]]);
            })->where([['addtime','>=',$anti_start_time],['addtime','<=',$anti_end_time]])->paginate(['list_rows'=> 10])->total();
//            echo OrderModel::getLastSql();exit();
            if($order_total>=$anti_num){
                echo_js("alert('同一个IP或手机".$anti_time."分钟内最多允许提交".$anti_num."次，您已提交了".$order_total."次，已超出限制，请过段时间再试。');");
            }
        }

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
            "addtime"=>date("Y-m-d H:i:s",time()),
        );

        //检测重复订单
        $web_config_data = get_web_config();//获取网站配置
        $order_repeat_check_field_arr = explode(",", $web_config_data["order_repeat_check_fields"]);//检测的字段
        $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀
        $where_sql = "";
        foreach ($order_repeat_check_field_arr as $field) {
            $where_sql .= " and " . $field . "='" . $insert_data[$field] . "'";
        }
        $order_query_total = Db::query("select count(id) as total from `" . $cz_prefix . "order`  where is_del=0 and is_repeat=0 ".$where_sql);
        $order_repeat_total = $order_query_total[0]["total"];
        if($order_repeat_total>0){//重复订单
            $insert_data["is_repeat"]=1;
        }

        $id = Db::name('order')->insertGetId($insert_data);

        if($id>0){

            $web_config_data=get_web_config();
            if(isset($web_config_data["tips_order_post_type"])){
                //发邮件给管理员
                if($web_config_data["tips_order_post_type"]==1){
                    $email_smtp_content=$web_config_data["email_smtp_content"];
                    foreach($insert_data as $order_key=>$order_val){
                        $email_smtp_content=str_replace("{@".$order_key."}",$order_val,$email_smtp_content);
                    }
                    @cz_sendMail($web_config_data["email_smtp_to"], $web_config_data["email_smtp_subject"], $email_smtp_content,false,$web_config_data["email_smtp_addcc"],$web_config_data["email_smtp_addbcc"]);
                }
            }

            if(!$product->tips_text){
                $product->tips_text="订单提交成功！我们会尽快给您发货，谢谢您的支持！";
            }
            $jump_url=$product->tips_url;//跳转到URL

            if($product->tips_type==1){//弹出成功对话框
                if($jump_url){
                    echo_js("alert('".$product->tips_text."');top.location.href='".$jump_url."';",false);
                }else{
                    $jump_url=url("/index/order/view/sign/".$product->pro_sign)."?from_url=".urlencode($update_data["from_url"]);
                    echo_js("alert('".$product->tips_text."');window.location.href='".$jump_url."';",false);
                }
            }elseif($product->tips_type==2){//跳转URL
                if($jump_url){
                    echo_js("top.location.href='".$jump_url."';",false);
                }else{
                    $jump_url=url("/index/order/view/sign/".$product->pro_sign)."?from_url=".urlencode($update_data["from_url"]);
                    echo_js("window.location.href='".$jump_url."';",false);
                }
            }elseif($product->tips_type==3){//跳转到成功提示页面
                if(!$jump_url){
                    $jump_url=$update_data["pro_url"];
                }
                $page_url=url('/index/order/show_success')."?backurl=".urlencode($jump_url)."&back_time=60&text=".urlencode($product->tips_text);
                echo_js("top.location.href='".$page_url."';",false);
            }

        }else{
            echo_js("alert('订单提交失败，请联系我们客服。');");
        }
    }

    public function captcha()//显示验证码
    {
        return Captcha::create("verify_comment");
    }

    public function show_success()//返回成功提示的页面
    {
        $action=Request::param('','','filter_sql');//过滤注入
        $action["text"]=isset($action["text"])?$action["text"]:"订单提交成功！我们会尽快给您发货，谢谢您的支持！";
        $action["backurl"]=isset($action["backurl"])?$action["backurl"]:"";
        $action["back_time"]=isset($action["back_time"])?$action["back_time"]:60;
        echo "<!DOCTYPE html><html><head>
        <meta charset=\"utf-8\">
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0\">
        <title>系统提示</title></head>
        <body style=\"font-size:18px;padding:5px 10px;text-align:center;line-height:185%;\"><div style='margin: 0 auto;text-align:left;max-width:450px;'>系统提示：<br><div style='font-weight: bold;color:#ff0000;margin:10px auto 15px auto;'>".$action["text"]."</div><div style='text-align: center'><span id=\"back_time\">" . $action["back_time"] . "</span>秒后将自动返回……<br />
        <a href=\"" . $action["backurl"] . "\"><u>您也可以点此马上返回</u></a></div></div>
        <script>
        function back(){
            var back_time = document.getElementById(\"back_time\");
            if(back_time.innerText == 0){
                window.location.href='" . $action["backurl"] . "';
                return false;
            }
            back_time.innerText = back_time.innerText - 1;
        }
        window.setInterval(\"back();\", 1000);
        </script>
        </body></html>";
    }

}