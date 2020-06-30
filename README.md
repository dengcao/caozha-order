# caozha-order 竞价页订单管理系统 1.3.0

caozha-order是一个通用的竞价页订单管理系统，基于开源的caozha-admin开发，支持订单管理、订单回收站、产品管理、订单批量导出（支持导出格式：.xls，.xlsx，.csv）、竞价页的下单表单调用等功能。系统特点：易上手，零门槛，界面清爽极简，极便于二次开发。

### 系统功能

1、系统设置

2、管理员管理

3、权限组管理

4、系统日志

5、后台功能地图

6、订单管理

7、产品管理

8、订单回收站

9、内置3套不同风格的下单表单页面，支持调用或内嵌在竞价页上

10、批量导出订单，支持导出格式：.xls，.xlsx，.csv等

11、防护设置：支持下单页是否开启验证码，是否防恶意提交（可设置同个IP或手机号X分钟内只能提交N次订单）


### 安装使用

**快速安装**

1、PHP版本必须7.1及以上。

2、上传目录/Src/内所有源码到服务器，并设置网站的根目录指向目录/Src/public/。（ThinkPHP6.0要求）

3、将/Database/目录里的.sql文件导入到MYSQL数据库。

4、修改文件/Src/config/database.php，配置您的数据库信息（如果是本地测试，还需要修改文件/Src/.env，本地测试会优先使用此配置文件）。

5、后台访问地址：http://您的域名/admin/index/login   (账号：caozha   密码：123456)


**开发手册**

本系统基于caozha-admin开发，二次开发可参考此手册。

码云Wiki：[https://gitee.com/caozha/caozha-admin/wikis](https://gitee.com/caozha/caozha-admin/wikis)

GitHub Wiki：[https://github.com/cao-zha/caozha-admin/wiki](https://github.com/cao-zha/caozha-admin/wiki)


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

### 特别鸣谢

caozha-order使用了以下开源代码：

caozha-admin1.6.0、ThinkPHP6.0.2、layui2.5.4、layuimini v2、font-awesome-4.7.0

特别致谢！

### 赞助支持：

支持本程序，请到Gitee和GitHub给我们点Star！

Gitee：https://gitee.com/caozha/caozha-order

GitHub：https://github.com/cao-zha/caozha-order

### 关于开发者

开发：草札 www.caozha.com

鸣谢：品络 www.pinluo.com  &ensp;  穷店 www.qiongdian.com


### 界面预览


**竞价页的下单表单页面：**

风格一：

![输入图片说明](https://images.gitee.com/uploads/images/2020/0630/153646_fd08bd8e_7397417.png "7.png")

风格二：

![输入图片说明](https://images.gitee.com/uploads/images/2020/0630/153654_93d9e77b_7397417.png "8.png")

风格三：

![输入图片说明](https://images.gitee.com/uploads/images/2020/0630/153703_1c25fca2_7397417.png "9.png")


**后台管理功能页面：**


![输入图片说明](https://images.gitee.com/uploads/images/2020/0624/222924_45b3913d_7397417.png "1.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0624/222933_565c78dc_7397417.png "2.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0628/144907_7aca6b17_7397417.png "3.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0628/144916_43a44d8f_7397417.png "4.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0624/222954_6c54cc78_7397417.png "5.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0630/153714_a11eb487_7397417.png "6.png")

![输入图片说明](https://images.gitee.com/uploads/images/2020/0630/154425_00892cdf_7397417.png "10.png")

