# caozha-order 广告竞价页订单管理系统 1.8.1

caozha-order是一个通用的广告（推广）竞价页订单管理系统，基于开源的caozha-admin开发，支持订单管理、订单回收站、产品管理、批量上传订单、批量导出订单（支持导出格式：.xls，.xlsx，.csv）、检测订单重复、竞价页的下单表单调用、客户下单时给管理员发邮件（短信）提醒等功能，内置灵活的查看订单权限设置机制。系统特点：易上手，零门槛，拿来即用，界面清爽极简，极便于二次开发。

### 系统功能

1、系统设置

2、管理员管理

3、权限组管理

4、系统日志

5、后台功能地图

6、产品管理

7、订单管理

8、订单回收站：恢复订单、彻底删除订单、清空订单回收站

9、订单查重：检查重复订单、删除重复订单

10、批量上传订单，支持上传的格式：.xls，.xlsx等

11、批量导出订单，可以设置订单导出的任何字段，支持导出格式：.xls，.xlsx，.csv等。单次导出的数量建议20万以下，可以通过按日期筛选导出。

12、内置3套不同风格的下单表单页面，支持调用或内嵌在竞价页上。（如需要不同的风格，可以自己新增或修改）

13、防护设置：支持下单页是否开启验证码，是否防恶意提交（可设置同个IP或手机号X分钟内只能提交N次订单）

14、按产品标识符设置查看订单的权限：可以单独对某个账号设置只能查看某些产品标识符的订单。说明：①此功能可以很方便的开账户给下属或合作商查看订单。②此功能和权限组设置是并列的，可相互搭配使用。

15、支持百万级别以上的订单数据量。当数据量很大，出现明显卡顿的时候，可以到系统设置—》订单列表分页模式-》简洁模式，即可解决问题。

16、客户下单时，可自动给管理员发送提醒邮件（tips：可设置收到QQ邮件通过微信、QQ提醒），也可以设置直接将订单内容通过邮件发送给管理员，让您第一时间收到客户订单。


### 安装使用

**开发环境**

本人开发此系统使用的本地环境是：[phpstudy8.1集成工具（已集成php8.0.14nts/php8.1.1nts，有需要点击下载）](https://gitee.com/caozha/php8.0-8.1-for-phpstudy)，phpMyAdmin 4.8.5，Apache2.4.39（或Nginx1.15.11），PHP8.0.14，MySQL5.7.26。

事实上，您不需要使用跟以上完全一致的环境也可以正常运行本系统，理论上只要PHP>=8.0即可。如有不兼容，建议模拟本环境测试，并欢迎您提建议和反馈BUG。


**快速安装**

1、PHP版本：必须PHP8.0以上。

2、上传目录/Src/内所有源码到服务器，并设置网站的根目录指向运行目录/public/。（此为ThinkPHP6.0的要求）

3、将/Database/目录里的.sql文件导入到MYSQL数据库。

4、修改文件/config/database.php，配置您的数据库信息（如果测试时启用了/.env，还需要修改文件/.env，系统会优先使用此配置文件）。

5、后台访问地址：http://您的域名/admin/index/login   (账号：caozha   密码：123456)


**伪静态设置**

1、ThinkPHP框架必须在运行目录下设置伪静态才能正常访问，否则会显示404错误。

2、如果您使用的是Apache，伪静态设置为（.htaccess）：

<IfModule mod_rewrite.c>

  Options +FollowSymlinks -Multiviews
  
  RewriteEngine On
  
  RewriteCond %{REQUEST_FILENAME} !-d
  
  RewriteCond %{REQUEST_FILENAME} !-f
  
  RewriteRule ^(.*)$ index.php?s=$1 [QSA,PT,L]
  
</IfModule>


3、如果您使用的是Nginx，以wdCP和宝塔Linux面板为例，伪静态设置为：

    index index.php;
    
    if (!-e $request_filename) {
    
       rewrite  ^(.*)$  /index.php?s=/$1  last;
       
       break;
       
    }
    


4、在网站运行目录（/public/）下，有两个文件：.htaccess和nginx.htaccess，分别是Apache和Nginx的伪静态文件，您可以直接拿来使用。


**开发手册**

本系统基于caozha-admin开发，二次开发可参考此手册。

码云Wiki：[https://gitee.com/caozha/caozha-admin/wikis](https://gitee.com/caozha/caozha-admin/wikis)

GitHub Wiki：[https://github.com/cao-zha/caozha-admin/wiki](https://github.com/cao-zha/caozha-admin/wiki)


### 更新方法

**1.7.1以上升级到1.8.0的方法：**

1、版本1.8.0改动较大，目前仅支持运行在PHP8.0.x环境，如服务器不支持该PHP版本，勿升级。

2、升级此版本，无需更新数据库。

3、将1.8.0版/SRC/目录的源文件覆盖旧版本，注意修改数据库配置。

4、升级后，登陆系统后台，打开“系统设置”，将“客户下单成功后”选项“给管理员发邮件提醒”选中，设置好“发邮件SMTP设置”、“收件人邮箱”等。

5、清空系统缓存。


**1.6升级到1.7.1的方法：**


1、更新cz_web_config表，执行SQL语句：


UPDATE `cz_web_config` SET `web_config` = '{\"share_url\":\"http:\\/\\/www.caozha.com(\\u6b64URL\\u53ef\\u5728\\u7cfb\\u7edf\\u8bbe\\u7f6e\\u4e2d\\u4fee\\u6539)\",\"order_paginate\":\"1\",\"admin_limit\":\"15\",\"roles_limit\":\"15\",\"syslog_limit\":\"15\",\"order_limit\":\"10\",\"product_limit\":\"10\",\"order_upload_limit\":\"20\",\"order_upload_memory_limit\":\"1000\",\"order_repeat_check_limit\":\"1000\",\"layTableCheckbox\":\"on\",\"order_export_fields\":\"id,realname,tel,addresss,payment,quantity,amount,remarks,pro_name,pro_options,pro_sign,addtime,client,pro_url,from_url\",\"order_repeat_check_fields\":\"tel\"}' WHERE `cz_web_config`.`id` = 1;


2、修改cz_order表结构：①将原字段order_id修改成id。②删除is_check_repeat字段。


3、将1.7.1版/SRC/目录的源文件覆盖旧版本，注意修改数据库配置，还有清空缓存。


**1.7.1升级到1.7.2+的方法：**

1、执行下面MYSQL命令：

ALTER TABLE `cz_order` CHANGE `addtime` `addtime` DATETIME NULL DEFAULT NULL COMMENT '下单时间';

ALTER TABLE `cz_product` CHANGE `updatetime` `updatetime` DATETIME NULL DEFAULT NULL COMMENT '添加时间';


2、将1.7.2+版/SRC/目录的源文件覆盖旧版本，注意修改数据库配置，还有清空缓存。


### 更新说明

**版本1.0.0，主要更新：**

1、新增：订单管理、订单回收站、产品管理、系统设置、管理员、权限组、系统日志、后台功能地图等功能。

2、竞价页的下单表单调用。


**版本1.1.0，主要更新：**

新增：按下单日期时间搜索和导出订单。


**版本1.2.0，主要更新：**

新增：订单回收站。

**版本1.3.0，主要更新：**

1、新增了3套竞价页下单表单模板。

2、新增了防护设置：支持下单页是否开启验证码，是否防恶意提交（可设置同个IP或手机号X分钟内只能提交N次订单）

3、修复了若干处BUG。

**版本1.4.0，主要更新：**

新增了批量上传订单的功能。

**版本1.5.0，主要更新：**

新增了订单查重功能：检查重复订单、删除重复订单。

**版本1.6.0，主要更新：**

1、新增了按产品标识符设置订单查看权限的功能。

2、新增了查重字段设置。


**版本1.6.2，主要更新：**

1、可以设置：上传订单大小限制、导入订单时最大内存、订单查重时每次处理量等。

2、修复了上传订单时，大数据量的excel表格容易失败的问题，本地实测一次性导入6万订单依然成功。

3、优化了订单查重时容易崩溃的问题。


**版本1.7.0，主要更新：**

1、可以在系统设置订单导出的字段，可以是数据库里的任意字段。

2、性能有很大提升，1.6版在订单量100万以上的时候，订单列表页和后台首页有点卡顿，新版解决了这个问题。

3、改动了cz_order和cz_web_config的表结构。升级时，请注意查看上面文档的：1.6升级到1.7的方法。

4、重新设计了查重的功能，极大地加快了查重速度和准确率。

5、优化了订单导出功能，在选择导出格式.csv情况下，可以轻松导出几十万条订单。


**版本1.7.1，主要更新：**

修复了上传订单时没有检测空数据行或空字段的BUG。


**版本1.7.2，主要更新：**

兼容了MySQL5.6及以下数据库，在MySQL5.5/5.6上测试，可以正常导入和使用。但为了获取更高的性能，依然建议您使用更高版本的MySQL数据库。


**版本1.7.3，主要更新：**

修复了检查重复订单的一个BUG。


**版本1.7.5，主要更新：**

1、修复了上传订单的一个BUG，支持导入excel的时间格式；因兼容性原因，删除了上传.csv格式文件的支持，仅支持.xls和.xlsx两种格式。

2、订单列表增加下单时间显示。

3、优化下单后跳转URL的功能。


**版本1.7.6，主要更新：**

1、新增：客户提交订单的时候检测重复订单，如重复，做标记。

2、优化登陆超时的弹窗。

3、修复其他一些小问题。


**版本1.7.7，主要更新：**

优化了下单后的跳转功能。下单成功后，分三种跳转模式：弹出提示框后跳转，转到成功页后跳转，直接跳转；均可后台设置详细参数。


**版本1.7.8，主要更新：**

优化了订单上传（导入）功能。上传订单时，如遇到错误的字段数据将不再报错，而是自动忽略错误的字段值并改用默认字段值覆盖，让导入订单更流畅。


**版本1.8.0，主要更新：**

1、修复了一些历史BUG。

2、升级caozha-admin到最新版本1.8.2，支持PHP8。

3、新增“客户下单后给管理员发邮件提醒”功能。


**版本1.8.1，主要更新：**

1、支持php8.1，修复了php8.1时验证码错误等BUG。

2、更新了ThinkPHP框架到最新版。



### 特别鸣谢

caozha-order使用了以下开源代码：

caozha-admin、ThinkPHP、layui、layuimini、font-awesome、phpoffice、phpmailer等

特别致谢！

### 赞助支持：

支持本程序，请到Gitee和GitHub给我们点Star！

Gitee：https://gitee.com/dengzhenhua/caozha-order

GitHub：https://github.com/dengcao/caozha-order

### 关于

开发：[邓草博客 blog.5300.cn](http://blog.5300.cn)

赞助：[品络 www.pinluo.com](http://blog.5300.cn)  &ensp;  [AI工具箱 5300.cn](https://5300.cn)  &ensp;  [汉语言文学网 hyywx.com](https://hyywx.com)  &ensp;  [雄马 xiongma.cn](https://xiongma.cn) &ensp;  [优惠券 tm.gs](http://tm.gs)


### 界面预览


**竞价页的下单表单页面：**

风格一：

![输入图片说明](https://images.gitee.com/uploads/images/2020/0630/153646_fd08bd8e_7397417.png "7.png")

风格二：

![输入图片说明](https://images.gitee.com/uploads/images/2020/0630/153654_93d9e77b_7397417.png "8.png")

风格三：

![输入图片说明](https://images.gitee.com/uploads/images/2020/0630/153703_1c25fca2_7397417.png "9.png")


**后台管理功能页面：**


![输入图片说明](https://images.gitee.com/uploads/images/2020/0701/210221_62e6b554_7397417.png "1.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0701/210229_51d0bae9_7397417.png "2.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0701/210236_e330436f_7397417.png "3.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0701/210243_bce659bc_7397417.png "4.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0701/210250_fab99560_7397417.png "5.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0701/210257_9b7b776e_7397417.png "6.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0701/210307_acbaea3b_7397417.png "10.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0701/210317_ef12a3df_7397417.png "11.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0702/111805_1689bae8_7397417.png "12.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0702/111816_9fffec5e_7397417.png "13.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0703/204201_2fed4469_7397417.png "14.png")

![输入图片说明](https://images.gitee.com/uploads/images/2022/0321/004407_fb40ca98_7397417.png "15.png")

![输入图片说明](https://images.gitee.com/uploads/images/2022/0321/004450_cfff22de_7397417.png "16.png")

