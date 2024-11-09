<?php
/**
 * 源码名：caozha-order
 * Copyright © 邓草 （官网：http://blog.5300.cn）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/dengcao/caozha-order   or   Gitee：https://gitee.com/dengzhenhua/caozha-order
 */

// 应用公共文件
use PHPMailer\PHPMailer\PHPMailer;

//应用的名称及版本
$GLOBALS["caozha_common_config"] = [
    "name" => "caozha-order",
    "version" => "1.9.0",
    "gitee" => "dengzhenhua/caozha-order",
    "github" => "dengcao/caozha-order",
];

//caozha-admin 程序名称及版本，用于标识和升级，勿删改
$GLOBALS["caozha_admin_sys"] = array(
    "name" => "caozha-admin",
    "version" => "1.9.0",
    "url" => "https://gitee.com/dengzhenhua/caozha-admin",
);

/**
 * 获取应用入口之前的目录，格式如：/public/或/
 * @return string
 */
function get_cz_path(){
    //$cz_path=substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'], '/')+1);
    $cz_path=substr($_SERVER['PHP_SELF'],0,8);
    if($cz_path=="/public/"){return $cz_path;}else{return "/";}
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

/**
 * 判断是否URL网址
 * @param string $url 网址
 * @return boolean
 */
function is_url_cz($url){
    if(!$url){return false;}
    if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
        return true;
    }else{
        return false;
    }
}


/**
 *发送邮件
 * @param string $to 接收者（多个邮件之间用,分隔）,string $title 标题,string $content 邮件内容,array $attachment 附件内容（二维数组），string $to_addCC 抄送人，string $to_addBCC 密送人
 * @return bool true:发送成功 false:发送失败
 * @throws \PHPMailer\PHPMailer\Exception
 */
function cz_sendMail($to, $title, $content,$attachment_arr=false,$to_addCC='',$to_addBCC=''): bool
{
    //实例化PHPMailer核心类
    $mail = new PHPMailer();

    $web_config_data=get_web_config();
    if(isset($web_config_data["email_smtp_config"])){
        $smtp_config_arr=explode("||",$web_config_data["email_smtp_config"]);
        //格式：smtp服务器主机||smtp服务器端口号||登录鉴权加密方式（默认ssl）||邮件编码||发件人姓名||smtp登录账号||smtp登录密码||发件人邮箱
        $smtp_config=array(
            "Host"=>$smtp_config_arr[0],
            "Port"=>$smtp_config_arr[1],
            "SMTPSecure"=>$smtp_config_arr[2],
            "CharSet"=>$smtp_config_arr[3],
            "FromName"=>$smtp_config_arr[4],
            "Username"=>$smtp_config_arr[5],
            "Password"=>$smtp_config_arr[6],
            "From"=>$smtp_config_arr[7],
        );
    }else{
        return false;
    }

    //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
    $mail->SMTPDebug = 0;

    //使用smtp鉴权方式发送邮件
    $mail->isSMTP();

    //smtp需要鉴权 这个必须是true
    $mail->SMTPAuth = true;

    //邮箱的smtp服务器地址
    $mail->Host = $smtp_config["Host"];

    //设置使用ssl加密方式登录鉴权
    $mail->SMTPSecure = $smtp_config["SMTPSecure"];

    //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
    $mail->Port = $smtp_config["Port"];

    //设置smtp的helo消息头 这个可有可无 内容任意
    // $mail->Helo = 'Hello smtp.qq.com Server';

    //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用您的域名
    $mail->Hostname = '';

    //设置发送的邮件的编码 可选GB2312、UTF-8（utf8在某些客户端收信下会乱码）
    $mail->CharSet = $smtp_config["CharSet"];

    //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
    $mail->FromName = $smtp_config["FromName"];

    //smtp登录的账号
    $mail->Username = $smtp_config["Username"];

    //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
    $mail->Password = $smtp_config["Password"];

    //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
    $mail->From = $smtp_config["From"];

    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
    $mail->isHTML(true);

    //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
    //添加多个收件人 则多次调用方法即可
    // $mail->addAddress('xxx@163.com','');

    $to_arr=explode(",",$to);
    foreach($to_arr as $to_user){
        $mail->addAddress($to_user,'');//收件人
    }

    if($to_addCC){
        $to_addCC_arr=explode(",",$to_addCC);
        foreach($to_addCC_arr as $to_addCC_user){
            $mail->addCC($to_addCC_user,'');//抄送
        }
    }

    if($to_addBCC){
        $to_addBCC_arr=explode(",",$to_addBCC);
        foreach($to_addBCC_arr as $to_addBCC_user){
            $mail->addBCC($to_addBCC_user,'');//密送
        }
    }

    //抄送/密送方式
//    foreach($to_arr as $key=>$to_user){
//        if($key==0){
//            $mail->addAddress($to_user,'');
//        }else{
//            $mail->addCC($to_user,'');//抄送人
//            //$mail->addBCC($to_user,'');//秘密抄送人
//        }
//    }

    //添加该邮件的主题
    $mail->Subject = $title;

    //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
    $mail->Body = $content;

    //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
    // $mail->addAttachment('./d.jpg','mm.jpg');
    //同样该方法可以多次调用 上传多个附件
    // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
    if(is_array($attachment_arr) && false!=$attachment_arr){
        foreach($attachment_arr as $attachment){
            $mail->addAttachment($attachment["path"],$attachment["name"]);
        }
    }

    $status = $mail->send();

    //简单的判断与提示信息
    if ($status) {
        return true;
    } else {
        return false;
    }
}