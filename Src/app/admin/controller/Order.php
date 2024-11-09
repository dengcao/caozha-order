<?php
/**
 * 源码名：caozha-order
 * Copyright © 邓草 （官网：http://blog.5300.cn）
 * 基于木兰宽松许可证 2.0（Mulan PSL v2）免费开源，您可以自由复制、修改、分发或用于商业用途，但需保留作者版权等声明。详见开源协议：http://license.coscl.org.cn/MulanPSL2
 * caozha-order (Software Name) is licensed under Mulan PSL v2. Please refer to: http://license.coscl.org.cn/MulanPSL2
 * Github：https://github.com/dengcao/caozha-order   or   Gitee：https://gitee.com/dengzhenhua/caozha-order
 */

namespace app\admin\controller;

use app\admin\model\Order as OrderModel;
use app\admin\model\TemptableNew;
use app\admin\model\WebConfig as WebConfigModel;
use think\Exception;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;

//使用Spreadsheet类
use PhpOffice\PhpSpreadsheet\Spreadsheet;
//xls格式类
use PhpOffice\PhpSpreadsheet\Writer\Xls;
//可以生成多种格式类
use PhpOffice\PhpSpreadsheet\IOFactory;

class Order
{
    protected $order_payment, $order_status, $order_client;

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
            'order_paginate' => $web_config["order_paginate"],//采用分页模式，1=正常模式，2=简洁模式
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
            'order_paginate' => $web_config["order_paginate"],//采用分页模式，1=正常模式，2=简洁模式
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
        $id = Request::param("id", '', 'filter_sql');
        if (!is_numeric($id)) {
            caozha_error("参数错误", "", 1);
        }

        $is_del = Request::param("is_del");
        if (!is_numeric($is_del)) {
            $is_del = 0;
        } elseif ($is_del == 1) {
            cz_auth("order_recycle");//检测是否有权限
        }

        $order = OrderModel::where("id", "=", $id)->where("is_del", "=", $is_del)->withAttr('status', function ($value) {
            try {
                $order_status = $this->order_status[$value];
            } catch (Exception $e) {
                $order_status = "";
            }
            return $order_status;
        })->withAttr('addresss', function ($value) {
            return str_ireplace("/@/", " ", $value);
        })->withAttr('payment', function ($value) {
            try {
                $order_payment = $this->order_payment[$value];
            } catch (Exception $e) {
                $order_payment = "";
            }
            return $order_payment;
        });

        $pro_signs = trim(Session::get("pro_signs"));
        if ($pro_signs) {
            $order = $order->where(function ($query) use ($pro_signs) {
                //解决生成的SQL语句不自动加括号的问题
                $pro_signs_arr = explode(",", $pro_signs);
                foreach ($pro_signs_arr as $value) {
                    $query->whereOr("pro_sign", "=", $value);
                }

            });
        }

        $order = $order->findOrEmpty();

        //print_r(Db::getLastSql());

        if ($order->isEmpty()) {
            caozha_error("[ID:" . $id . "]订单不存在或无权限查看。", "", 1);
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
        $web_config = get_web_config();//获取网站配置

        $page = Request::param("page");
        if (!is_numeric($page)) {
            $page = 1;
        }
        $limit = Request::param("limit");
        if (!is_numeric($limit)) {
            $limit = $web_config["order_limit"];
            if (!is_numeric($limit)) {
                $limit = 15;//默认显示15条
            }
        }

        $list = Db::name('order');
        //$list = new OrderModel;
        $list = $list->withAttr('status', function ($value) {
            try {
                $order_status = $this->order_status[$value];
            } catch (Exception $e) {
                $order_status = "";
            }
            return $order_status;
        })->withAttr('payment', function ($value) {
            try {
                $order_payment = $this->order_payment[$value];
            } catch (Exception $e) {
                $order_payment = "";
            }
            return $order_payment;
        })->withAttr('addresss', function ($value) {
            return str_ireplace("/@/", "", $value);
        });

        $action = Request::param('', '', 'filter_sql');//过滤注入
        if (isset($action["keyword"])) {
            if ($action["keyword"] != "") {
                $list = $list->where("realname|tel|addresss|remarks|pro_name|pro_options|pro_sign|wechat|qq|email|postal_code|admin_remarks|pro_url|from_url", "like", "%" . $action["keyword"] . "%");
            }
        }

        $pro_signs = trim(Session::get("pro_signs"));
        if ($pro_signs) {
            $list = $list->where(function ($query) use ($pro_signs) {
                //解决生成的SQL语句不自动加括号的问题
                $pro_signs_arr = explode(",", $pro_signs);
                foreach ($pro_signs_arr as $value) {
                    $query->whereOr("pro_sign", "=", $value);
                }

            });
        }

        $action["starttime"] = isset($action["starttime"]) ? $action["starttime"] : "";
        $action["endtime"] = isset($action["endtime"]) ? $action["endtime"] : "";
        if ($action["starttime"]) {
            $list = $list->where('addtime', '>=', $action["starttime"]);
        }
        if ($action["endtime"]) {
            $list = $list->where('addtime', '<=', $action["endtime"]);
        }

        $action_arr = ['status', 'payment', 'is_repeat'];
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
        } elseif ($is_del == 1) {
            cz_auth("order_recycle");//检测是否有权限
        }

        $list = $list->where("is_del", "=", $is_del);

        $is_export = Request::param("is_export");
        if ($is_export == 1) {//导出excel
            cz_auth("order_export");//检测是否有权限
            $export_type = Request::param("export_type");

            set_time_limit(0);
            ini_set('memory_limit', $web_config["order_upload_memory_limit"] . 'M');//允许PHP使用最大内存，当处理的Excel文件太大的时候，建议适当修改大小，不过容易导致内存溢出错误。

            //获取导出的字段
            $format_arr_data = array();

            $order_export_fields_arr = explode(",", $web_config["order_export_fields"]);
            //获取所有字段名
            $fields_arr = array();
            $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀
            $row_field = Db::query("SHOW FULL COLUMNS FROM " . $cz_prefix . "order");
            //print_r($row_field);exit();
            foreach ($row_field as $value) {
                $fields_arr[$value["Field"]] = $value["Comment"];
            }
            foreach ($order_export_fields_arr as $val) {
                $format_arr_data[$val] = $fields_arr[$val];
            }

            return $this->export_to_excel($list->field($web_config["order_export_fields"])->select()->toArray(), $format_arr_data, $export_type, true);
        }

        if($web_config["order_paginate"]==2){
            //采用TP6的大数据分页，就是个大坑，BUG一大堆，很不稳定。当搜索结果为空的时候，就会显示：不支持的分页索引字段类型
            // 已经修改了TP框架暂时解决了这个问题，等TP官方自己修复
            $cz_listRows=array(
                "list_rows"=>$limit,//每页数量
                'page' => $page,//当前页
            );
            $list = $list->paginateX($cz_listRows, 'id', 'desc')->toArray();


            //如不用paginateX，自己写，也可以直接用paginate(10, true)简洁分页
//            $list = $list->limit(($limit*($page-1)),$limit)->select()->toArray();
//            //echo OrderModel::getLastSql();
//            $list_out=array(
//                "per_page"=>$limit,
//                "current_page"=>$page,
//                "data"=>$list
//            );
//            $list=$list_out;

            if(count($list["data"])>0){
                $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀
                $order_all_query = Db::query("select count(*) as total from `" . $cz_prefix . "order`");
                $order_all_total = $order_all_query[0]["total"];
            }else{
                $order_all_total=0;
            }

            $list["total"]=$order_all_total;
            $list["last_page"]=ceil($order_all_total/$limit);

        }else{
            $list = $list->order('addtime', 'desc')->paginate([
                'list_rows' => $limit,//每页数量
                'page' => $page,//当前页
            ]);
        }

        //print_r(Db::getLastSql());
        return json($list);
    }


    /**
     * 导出到EXCEL
     * @param array $source_arr_data 导出的源内容，数组
     * @param array $format_arr_data 导出的列格式，数组
     * @param string $export_type 导出的数据类型，如csv,xls,xlsx等
     * @param boolean $is_syslog 是否记录系统日志
     * @return boolean or none
     */
    public function export_to_excel($source_arr_data, $format_arr_data, $export_type = "csv", $is_syslog = false)
    {
        if (!$source_arr_data || !$format_arr_data) {
            return false;
        }

        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        //error_reporting(0);// 关闭所有错误报告

        $export_count = count($source_arr_data);
        if ($export_type == "csv") {
            $csv_txt = implode(",", $format_arr_data);
            foreach ($source_arr_data as $k => $v) {
                $csv_txt_temp = "";
                foreach ($format_arr_data as $k_temp => $v_temp) {
                    $csv_txt_temp .= ',"' . $v[$k_temp] . '"';
                }
                $csv_txt .= "\r\n" . mb_substr($csv_txt_temp, 1);
            }
            $filename = "客户订单(共" . $export_count . "条)_" . date('YmdHis') . ".csv";
            if ($is_syslog) {
                write_syslog(array("log_content" => "下载订单：" . $filename . "，" . $_SERVER['QUERY_STRING']));//记录系统日志
            }
            return download($csv_txt, $filename, true);
        } elseif ($export_type == "xls" || $export_type == "xlsx") {

            //总结规律 设置参数的时候如果用$sheet $sheet->setTitle('Hello');而用$spreadsheet $spreadsheet->getActiveSheet()->setTitle('Hello'); 所有参数应该都可用这里两种方法
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            //设置sheet的名字  两种方法
            //$sheet->setTitle('phpspreadsheet——demo');
            $sheet->setTitle('订单');
            //设置第一行小标题
            $k = 1;
            $list_i = 0;
            foreach ($format_arr_data as $list_k => $list_v) {
                $list_i += 1;
                $sheet->setCellValue(getExcelValue($list_i) . $k, $list_v);
                $sheet->getColumnDimension(getExcelValue($list_i))->setWidth(15);//设置列的宽度
                //$sheet->getColumnDimension(getExcelValue($list_i))->setAutoSize(true);//自动设置列宽
                $sheet->getStyle(getExcelValue($list_i) . $k)->getFont()->setBold(true);// 一定范围内字体加粗
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

                $list_i = 0;
                foreach ($format_arr_data as $list_k => $list_v) {
                    $list_i += 1;
                    $sheet->setCellValue(getExcelValue($list_i) . $k, $value[$list_k]);
                }
                $k++;
            }

            $filename = "客户订单(共" . $export_count . "条)_" . date('YmdHis') . "." . $export_type;
            if ($is_syslog) {
                write_syslog(array("log_content" => "下载订单：" . $filename . "，" . $_SERVER['QUERY_STRING']));//记录系统日志
            }

            //第一种保存方式
            /*$writer = new Xls($spreadsheet);
            //保存的路径可自行设置
            $file_name = '../'.$file_name . ".xls";
            $writer->save($file_name);*/
            //第二种直接页面上显示下载
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            if ($export_type == "xls") {
                $writer = IOFactory::createWriter($spreadsheet, 'Xls'); //注意createWriter($spreadsheet, 'Xls') 第二个参数首字母必须大写
            } else {
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); //注意createWriter($spreadsheet, 'Xlsx') 第二个参数首字母必须大写
            }

            $writer->save('php://output');
        }

    }

    public function todo()//操作数据
    {
        $id = Request::param("id", '', 'filter_sql');
        $act = Request::param("act", '', 'filter_sql');

        if ($act == "del") {
            if (!is_cz_auth("order_del")) {//检测是否有权限
                $list = array("code" => 0, "msg" => "删除失败！您没有删除订单的权限。");
                $act = "";
            }
        } elseif ($act == "permanently_del") {//彻底删除订单
            if (!is_cz_auth("order_recycle")) {//检测是否有权限
                $list = array("code" => 0, "msg" => "删除失败！您没有彻底删除订单的权限。");
                $act = "";
            }
        } elseif ($act == "permanently_del_all") {//清空订单回收站
            if (!is_cz_auth("order_recycle")) {//检测是否有权限
                $list = array("code" => 0, "msg" => "清空订单回收站失败！您没有清空订单回收站的权限。");
                $act = "";
            }
        } elseif ($act == "recover") {
            if (!is_cz_auth("order_recycle")) {//检测是否有权限
                $list = array("code" => 0, "msg" => "恢复失败！您没有恢复订单的权限。");
                $act = "";
            }
        } else {
            if (!is_cz_auth("order_todo")) {//检测是否有权限
                $list = array("code" => 0, "msg" => "操作失败！您没有权限。");
                $act = "";
            }
        }

        if ($act == "del") {
            //删除
            $del_num = 0;
            if ($id) {
                //$del_num = OrderModel::where("id", "in", $id)->delete();
                $del_num = Db::name('order')->where('id', "in", $id)->update(['is_del' => 1]);//软删除
            }
            if ($del_num > 0) {
                write_syslog(array("log_content" => "软删除订单(ID)：" . $id));//记录系统日志
                $list = array("code" => 1, "msg" => "删除成功，共删除了 <font color='red'>" . $del_num . "</font> 条订单！");
            } else {
                $list = array("code" => 0, "msg" => "删除失败！");
            }
        } elseif ($act == "status_1") {
            $todo_name = "设订单为未处理";
            $do_num = 0;
            if ($id) {
                $do_num = Db::name('order')->where('id', "in", $id)->update(['status' => 1]);
            }
            if ($do_num > 0) {
                write_syslog(array("log_content" => $todo_name . "(ID)：" . $id));//记录系统日志
            }
            $list = array("code" => 1, "msg" => $todo_name . "完成，共更新了 <font color='red'>" . $do_num . "</font> 条订单！");
        } elseif ($act == "status_2") {
            $todo_name = "设订单为已处理";
            $do_num = 0;
            if ($id) {
                $do_num = Db::name('order')->where('id', "in", $id)->update(['status' => 2]);
            }
            if ($do_num > 0) {
                write_syslog(array("log_content" => $todo_name . "(ID)：" . $id));//记录系统日志
            }
            $list = array("code" => 1, "msg" => $todo_name . "完成，共更新了 <font color='red'>" . $do_num . "</font> 条订单！");
        } elseif ($act == "status_3") {
            $todo_name = "设订单为无效";
            $do_num = 0;
            if ($id) {
                $do_num = Db::name('order')->where('id', "in", $id)->update(['status' => 3]);
            }
            if ($do_num > 0) {
                write_syslog(array("log_content" => $todo_name . "(ID)：" . $id));//记录系统日志
            }
            $list = array("code" => 1, "msg" => $todo_name . "完成，共更新了 <font color='red'>" . $do_num . "</font> 条订单！");
        } elseif ($act == "recover") {
            $todo_name = "恢复订单";
            $do_num = 0;
            if ($id) {
                $do_num = Db::name('order')->where('id', "in", $id)->update(['is_del' => 0]);
            }
            if ($do_num > 0) {
                write_syslog(array("log_content" => $todo_name . "(ID)：" . $id));//记录系统日志
            }
            $list = array("code" => 1, "msg" => $todo_name . "完成，共更新了 <font color='red'>" . $do_num . "</font> 条订单！");
        } elseif ($act == "permanently_del") {

            //彻底删除
            $del_num = 0;
            if ($id) {
                $del_num = OrderModel::where("id", "in", $id)->delete();
            }
            if ($del_num > 0) {
                write_syslog(array("log_content" => "彻底删除订单(ID)：" . $id));//记录系统日志
                $list = array("code" => 1, "msg" => "删除成功，共删除了 <font color='red'>" . $del_num . "</font> 条订单！");
            } else {
                $list = array("code" => 0, "msg" => "删除失败！");
            }

        } elseif ($act == "permanently_del_all") {

            //彻底清空订单回收站
            $del_num = OrderModel::where("is_del", "=", 1)->delete();

            if ($del_num > 0) {
                write_syslog(array("log_content" => "清空订单回收站，共彻底删除了" . $del_num . "条订单。"));//记录系统日志
                $list = array("code" => 1, "msg" => "删除成功，共删除了 <font color='red'>" . $del_num . "</font> 条订单！");
            } else {
                $list = array("code" => 0, "msg" => "删除失败！");
            }

        } else {
            if (!$list) {
                $list = array("code" => 0, "msg" => "未定义操作！");
            }
        }


        return json($list);
    }


    public function upload()//订单上传
    {
        cz_auth("order_upload");//检测是否有权限

        $order_upload_limit = 20;
        $web_config = WebConfigModel::where("id", ">=", 1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。", "", 1);
        } else {
            $web_config_data = object_to_array($web_config->web_config);
            $order_upload_limit = $web_config_data["order_upload_limit"];
        }
        View::assign([
            'order_upload_limit' => $order_upload_limit,
        ]);

        // 模板输出
        return View::fetch('order/upload');
    }

    public function upload_save()//订单上传处理
    {
        cz_auth("order_upload");//检测是否有权限

        $order_upload_limit = 20;
        $web_config = WebConfigModel::where("id", ">=", 1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。", "", 1);
        } else {
            $web_config_data = object_to_array($web_config->web_config);
            $order_upload_limit = $web_config_data["order_upload_limit"];
        }

        // 获取表单上传文件
        $file = request()->file('order_file');
        try {
            validate(['file' => 'filesize:' . (1024 * 1024 * $order_upload_limit) . '|fileExt:xls,xlsx'])->check(['file' => $file]);
            $savename = \think\facade\Filesystem::putFile('excel', $file, function () {
                return "file_" . Session::get("admin_id");
            });
            return json(array("code" => 1, "msg" => "上传成功", "filename" => $savename));
        } catch (\think\exception\ValidateException $e) {
//            echo $e->getMessage();
            return json(array("code" => 0, "msg" => "上传失败：".$e->getMessage(), "filename" => ""));
        }
    }

    public function import()//订单导入处理
    {
        cz_auth("order_upload");//检测是否有权限

        $order_upload_memory_limit = 1000;
        $web_config = WebConfigModel::where("id", ">=", 1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。", "", 1);
        } else {
            $web_config_data = object_to_array($web_config->web_config);
            $order_upload_memory_limit = $web_config_data["order_upload_memory_limit"];
        }

        ini_set('memory_limit', $order_upload_memory_limit . 'M');//允许PHP使用最大内存，当处理的Excel文件太大的时候，建议适当修改大小，不过容易导致内存溢出错误。
        set_time_limit(0);//永不超时
        ignore_user_abort(true);//即使关闭浏览器也不中断程序执行

        if (!Request::isAjax()) {
            // 如果不是AJAX
            return result_json(0, "error:not ajax.");
        }
        $action = Request::param("", '', 'filter_sql');
        $action["filename"] = isset($action["filename"]) ? $action["filename"] : "";
        if (!$action["filename"]) {
            return result_json(0, "filename不能为空");
        }

        $fileExtendName = substr(strrchr($action["filename"], '.'), 1);//文件后缀

        // 有Xls、Xlsx、csv等格式
        if ($fileExtendName == 'xlsx') {
            $objReader = IOFactory::createReader('Xlsx');
        } else if ($fileExtendName == 'csv') {//CSV格式容易导致编码的问题，所以暂时取消支持
            $objReader = IOFactory::createReader('Csv');
            $objReader->setInputEncoding('CP936'); //默认情况下假定加载的CSV文件是UTF-8编码的。如果要读取在Microsoft Office Excel中创建的CSV文件，则正确的输入编码可能是Windows-1252（CP1252）。始终确保正确设置了输入编码。
        } else {
            $objReader = IOFactory::createReader('Xls');
        }
        $objReader->setReadDataOnly(TRUE);
        $filename = runtime_path() . "storage/" . $action["filename"];
        $objPHPExcel = $objReader->load($filename);  //$filename可以是上传的表格，或者是指定的表格
        $sheet = $objPHPExcel->getSheet(0);   //excel中的第一张表sheet
        $highestRow = $sheet->getHighestRow();       // 取得总行数
        $realHighestRow = $highestRow - 2;//取得去除前面两行外的真实行数
        $highestColumn = $sheet->getHighestColumn();   // 取得最大的列，如：G
        // 最大列数+1 实际上是往后错一列
        ++$highestColumn;
        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        if ($realHighestRow <= 0) {
            return result_json(0, "excel表格没有数据！");
        }

        //获取所有字段名
        $fields_arr = array();
        $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀
        $row_field = Db::query("SHOW FULL COLUMNS FROM " . $cz_prefix . "order");
        foreach ($row_field as $value) {
            $fields_arr[] = $value["Field"];
        }

        //获取第二行的列，用来做字段名，判断
        $fields_order = [];
        for ($colIndex = 'A'; $colIndex != $highestColumn; $colIndex++) {
            //$val = trim($sheet->getCellByColumnAndRow($colIndex, 2)->getValue());
            // 组装单元格标识  A1  A2
            $addr = $colIndex . 2;
            // 获取单元格内容
            $val = $sheet->getCell($addr)->getValue();
            if (in_array($val, $fields_arr)) {//判断是否合法
                $fields_order[$val] = $colIndex;
            }
        }

        if(count($fields_order)<1){
            return json(array("code" => 0, "msg" => "导入订单失败，表格第二行必须至少设置一个字段名！"));
        }

        //循环读取excel表格，整合成数组。如果是不指定key的二维，就用$data[i][j]表示。
        $insertData = [];
        for ($j = 3; $j <= $highestRow; $j++) {
            $insertData_temp_arr = array();
            $is_insertData_empty=true;//插入的行是否全部为空。先假定为空。
            foreach ($fields_order as $key => $value) {
                $excel_value_curr=trim($objPHPExcel->getActiveSheet()->getCell($value . $j)->getValue());//获取单元格的内容
                if($key=="id"){
                    continue;//直接忽略
                }
                if($key=="addtime"){
                    if(!$excel_value_curr){
                        $excel_value_curr = NULL;
                    }else{
                        try {
                            //$excel_value_curr = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excel_value_curr);
                            $toTimestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($excel_value_curr,"PRC");
                        }catch (Exception $e){
                            $toTimestamp = strtotime($excel_value_curr);
                        }
                        $excel_value_curr = date("Y-m-d H:i:s", $toTimestamp );
                    }
                }
                if(!is_numeric($excel_value_curr)){
                    switch ($key)
                    {
                        case "payment":
                            $excel_value_curr=1;
                            break;
                        case "quantity":
                            $excel_value_curr=1;
                            break;
                        case "amount":
                            $excel_value_curr=0;
                            break;
                        case "listorder":
                            $excel_value_curr=0;
                            break;
                        case "status":
                            $excel_value_curr=1;
                            break;
                        case "is_show":
                            $excel_value_curr=0;
                            break;
                        case "is_del":
                            $excel_value_curr=0;
                            break;
                        case "is_repeat":
                            $excel_value_curr=0;
                            break;
                    }
                }
                $insertData_temp_arr[$key] = $excel_value_curr;
                if($insertData_temp_arr[$key] || is_numeric($insertData_temp_arr[$key])){
                    $is_insertData_empty=false;//只要任何一个字段有值，就不为空
                }
            }
            if(!$is_insertData_empty){
                $insertData[] = $insertData_temp_arr;
            }
        }

        //批量插入数据建议使用Db::insertAll() 方法，只会进行一次数据库请求；saveAll 方法实际上是 循环数据数组，每一条数据进行一遍save方法
        // 分批写入 每次最多500条数据
        $res = Db::name('order')
            ->limit(500)
            ->insertAll($insertData);
        write_syslog(array("log_content" => "批量导入订单，共导入" . $res . "条订单数据。"));//记录系统日志
        return json(array("code" => 1, "msg" => "导入订单成功，共导入" . $res . "条订单数据！"));
    }

    public function repeat_confirm()//确认是否检测重复订单
    {
        cz_auth("order_repeat");//检测是否有权限
        $alert = '校检重复订单需要较长时间，请您点击“确认”按钮后，不要做任何操作，只需静待页面显示成功即可。<br>点击“确认”开始执行，点击“取消”则不执行！';
        $js_code = 'window.location.href="' . url("admin/order/repeat_check") . '";';
        caozha_confirm($alert, $js_code, 1);
    }

    public function repeat_check()//批量检测重复订单，并设置重复状态
    {
        cz_auth("order_repeat");//检测是否有权限

        set_time_limit(0);//永不超时
        //ignore_user_abort(true);//即使关闭浏览器也不中断程序执行

        $order_repeat_step = Request::param('step', '', 'filter_sql');//执行到第几步
        if(!$order_repeat_step || !is_numeric($order_repeat_step)){
            $order_repeat_step=1;
        }

        $order_repeat_check_config = Cache::get('order_repeat_check_config');
        if (is_array($order_repeat_check_config) && $order_repeat_step>1) {
            $order_repeat_check_fields = $order_repeat_check_config["order_repeat_check_fields"];
            $order_repeat_check_field_arr = $order_repeat_check_config["order_repeat_check_field_arr"];
            $order_repeat_check_limit = $order_repeat_check_config["order_repeat_check_limit"];
            $order_upload_memory_limit = $order_repeat_check_config["order_upload_memory_limit"];
            $cz_prefix = $order_repeat_check_config["cz_prefix"];
        }else{
            $order_repeat_check_field_arr = [];
            $order_repeat_check_limit = 500;
            $order_upload_memory_limit = 1000;
            $web_config_data = get_web_config();//获取网站配置
                $order_repeat_check_fields = $web_config_data["order_repeat_check_fields"];
                $order_repeat_check_field_arr = explode(",", $web_config_data["order_repeat_check_fields"]);
                $order_repeat_check_limit = $web_config_data["order_repeat_check_limit"];
                $order_upload_memory_limit = $web_config_data["order_upload_memory_limit"];
                $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀

            $order_repeat_check_config = array(
                "order_repeat_check_fields" => $web_config_data["order_repeat_check_fields"],
                "order_repeat_check_field_arr" => $order_repeat_check_field_arr,
                "order_repeat_check_limit" => $order_repeat_check_limit,
                "order_upload_memory_limit" => $order_upload_memory_limit,
                "cz_prefix" => $cz_prefix,
            );
            Cache::set('order_repeat_check_config', $order_repeat_check_config);// 缓存
        }

        ini_set('memory_limit', $order_upload_memory_limit . 'M');//允许PHP使用最大内存，当处理的数据太大的时候，建议适当修改大小，不过容易导致内存溢出错误。

        if ($order_repeat_step==1) {
            //检测临时表是否存在，存在则删除
            $is_table_exist=Db::query('SHOW TABLES LIKE "'.$cz_prefix.'temptable"');
            if(!$is_table_exist){
                //将id值和查重条件添加到临时表
                Db::execute("create table `" . $cz_prefix . "temptable` as (select MAX(id) id," . $order_repeat_check_fields . ",count(*) as count from `" . $cz_prefix . "order` where is_del=0 and is_repeat=0 group by " . $order_repeat_check_fields . " having count(*) > 1);");//创建临时表
            }
            caozha_alert_msg("订单查重：<br><font color='red'>第".$order_repeat_step."步</font>，查重数据和条件收集，已经完成。<br>请不要关闭页面，系统正在处理下一步，请耐心等待……", url("admin/order/repeat_check")."?step=2", 3600, 1);
        }

        //第一步执行完毕之后，才可以进行下面的操作
        $order_repeat_total = Request::param('order_repeat_total', '', 'filter_sql');//订单重复数量
        if ($order_repeat_step == 2) {
            if (!is_numeric($order_repeat_total)) {
                $order_query = Db::query("select count(1) as total from `" . $cz_prefix . "temptable`");
                $order_repeat_total = $order_query[0]["total"];
            }
            caozha_alert_msg("订单查重：<br><font color='red'>第".$order_repeat_step."步</font>，获取查重数据总量，已经完成。<br>请不要关闭页面，系统正在处理下一步，请耐心等待……", url("admin/order/repeat_check")."?step=3&order_repeat_total=".$order_repeat_total, 3600, 1);
        }

        $order_repeat_current_num = Request::param('order_repeat_current_num', '', 'filter_sql');//处理到的当前进度
        if (!is_numeric($order_repeat_current_num)) {
            $order_repeat_current_num = 0;
        }

        $list_data=Db::query("select * from `" . $cz_prefix . "temptable` order by count asc limit ".$order_repeat_current_num.",".$order_repeat_check_limit.";");

        //容易造成阻塞
//        foreach ($list_data as $order) {
//            $where_sql = "";
//            foreach ($order_repeat_check_field_arr as $field) {
//                $where_sql .= " and " . $field . "='" . $order[$field] . "'";
//            }
//            Db::execute("update `" . $cz_prefix . "order` set is_repeat=1 where id!=".$order["id"].$where_sql);
//        }

        $update_list=array();
        $is_run_again=Request::param('is_run_again', '', 'filter_sql');//是否继续循环执行（递归）一次查重

        foreach ($list_data as $order) {
            if($order["count"]>2){
                $is_run_again=1;
            }

            $update_list[]=array(
                "id"=>$order["id"],
                "is_repeat"=>1,
            );

        }
        //print_r($update_list);exit();
        $order_update=new OrderModel();
        $order_update->saveAll($update_list);

        if(count($list_data)>0){
            $order_repeat_next_num =$order_repeat_current_num+$order_repeat_check_limit;
            $jump_url=url("admin/order/repeat_check")."?step=3&order_repeat_total=".$order_repeat_total."&order_repeat_current_num=".$order_repeat_next_num."&is_run_again=".$is_run_again;
            caozha_alert_msg("订单查重：<br><font color='red'>第".$order_repeat_step."步</font>，设置重复数据。<br>当前进度：".$order_repeat_current_num."/".$order_repeat_total."<br>请不要关闭页面，系统正在处理下一步，请耐心等待……<br><a href='".$jump_url."' style='color:#999;'>如页面长时间无响应，请点击这里继续</a>", $jump_url, 3600, 1);
        }elseif($is_run_again==1){//再次循环的时候，清除的是重复3个以上的订单
            write_syslog(array("log_content" => "批量检测重复订单：本次共处理" . $order_repeat_total . "条重复订单"));//记录系统日志
            //检测临时表temptable2是否存在，存在则删除
            $is_table_exist=Db::query('SHOW TABLES LIKE "'.$cz_prefix.'temptable_new"');
            if($is_table_exist){
                Db::execute("Drop table `" . $cz_prefix . "temptable_new`");
            }
            //创建新的临时表
            Db::execute("create table `" . $cz_prefix . "temptable_new` as (select * from `" . $cz_prefix . "temptable` where `count`>2 );");

            $order_query_new = Db::query("select count(1) as total from `" . $cz_prefix . "temptable_new`");
            $order_repeat_total_new = $order_query_new[0]["total"];

            Db::execute("Drop table `" . $cz_prefix . "temptable`");//删除旧的临时表
            Cache::delete('order_repeat_check_config');//删除缓存

            if($order_repeat_total_new>200){//再次重新执行查重任务
                Db::execute("Drop table `" . $cz_prefix . "temptable_new`");//删除新的临时表
                $jump_url=url("admin/order/repeat_check");
                caozha_alert_msg("订单查重：<br>将处理重复3次以上的数据。<br>请不要关闭页面，系统正在处理下一步，请耐心等待……<br><a href='".$jump_url."' style='color:#999;'>如页面长时间无响应，请点击这里继续</a>", $jump_url, 3600, 1);
            }else{//从临时表中执行查重任务
                $jump_url=url("admin/order/repeat_check_temptable")."?order_repeat_total=".$order_repeat_total_new;
                caozha_alert_msg("订单查重：<br>将处理重复3次以上的数据。<br>请不要关闭页面，系统正在处理下一步，请耐心等待……<br><a href='".$jump_url."' style='color:#999;'>如页面长时间无响应，请点击这里继续</a>", $jump_url, 3600, 1);
            }

        }else{
            //删除临时表
            Db::execute("Drop table `" . $cz_prefix . "temptable`");
            Cache::delete('order_repeat_check_config');//删除缓存
            write_syslog(array("log_content" => "批量检测重复订单：本次共处理" . $order_repeat_total . "条重复订单"));//记录系统日志
            caozha_success("批量检测重复订单完成。<br>如需查看重复订单，您可到订单列表搜索“重复订单”，也可以一键删除重复订单。", "", 1);
        }

    }

    public function repeat_check_temptable()//从临时表中执行查重任务
    {
        $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀
        $order_repeat_total=Request::param('order_repeat_total', '', 'filter_sql');//待处理的总量
        $repeat_current_num=Request::param('repeat_current_num', '', 'filter_sql');//当前处理进度
        if(!is_numeric($repeat_current_num)){
            $repeat_current_num=0;
        }

        $order = TemptableNew::where("id",">",0)->findOrEmpty();
        if (!$order->isEmpty()) {
//            $order_arr=array(
//                "id"=>$order->id,
//                "count"=>$order->count,
//            );
            $web_config_data = get_web_config();//获取网站配置
            $order_repeat_check_field_arr = explode(",", $web_config_data["order_repeat_check_fields"]);
            $where_sql = "";
            foreach ($order_repeat_check_field_arr as $field) {
                if($order[$field]==NULL){
                    $where_sql .= " and " . $field . " is null";
                }else{
                    $where_sql .= " and " . $field . "='" . $order->$field . "'";
                }
//                $order_arr[$field]=$order->$field;
            }

            //获取最小的ID
            $order_query_min_id = Db::query("select id from `" . $cz_prefix . "order` where is_del=0 and is_repeat=0 ".$where_sql." order by id asc limit 0,1");
            $order_min_id = $order_query_min_id[0]["id"];

            Db::execute("update `" . $cz_prefix . "order` set is_repeat=1 where id!=".$order_min_id.$where_sql);

            $order->id = 0;
            $order->save();

            $repeat_current_num+=1;
            $jump_url=url("admin/order/repeat_check_temptable")."?order_repeat_total=".$order_repeat_total."&repeat_current_num=".$repeat_current_num;
            caozha_alert_msg("订单查重：<br>处理重复3次以上的数据。<br>当前处理进度：".$repeat_current_num."/".$order_repeat_total."<br>请不要关闭页面，系统正在处理下一步，请耐心等待……<br><a href='".$jump_url."' style='color:#999;'>如页面长时间无响应，请点击这里继续</a>", $jump_url, 3600, 1);
        } else {
            Db::execute("Drop table `" . $cz_prefix . "temptable_new`");//删除新的临时表
            write_syslog(array("log_content" => "批量检测重复订单：本次共处理" . $order_repeat_total . "条重复订单"));//记录系统日志
            caozha_success("批量检测重复订单完成。<br>如需查看重复订单，您可到订单列表搜索“重复订单”，也可以一键删除重复订单。", "", 1);
        }

    }

    public function repeat_check_old2()//批量检测重复订单，并设置重复状态，旧的方法，性能差
    {
        cz_auth("order_repeat");//检测是否有权限

        set_time_limit(0);//永不超时
        //ignore_user_abort(true);//即使关闭浏览器也不中断程序执行

        $order_repeat_check_field_arr = [];
        $order_upload_memory_limit = 1000;
        $web_config = WebConfigModel::where("id", ">=", 1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。", "", 1);
        } else {
            $web_config_data = object_to_array($web_config->web_config);
            $order_repeat_check_field_arr = explode(",", $web_config_data["order_repeat_check_fields"]);
            $order_upload_memory_limit = $web_config_data["order_upload_memory_limit"];
        }

        ini_set('memory_limit', $order_upload_memory_limit . 'M');//允许PHP使用最大内存，当处理的数据太大的时候，建议适当修改大小，不过容易导致内存溢出错误。

        //查重处理
        $order_repeat_step = Request::param('step', '', 'filter_sql');//执行到第几步
        if(!$order_repeat_step || !is_numeric($order_repeat_step)){
            $order_repeat_step=1;
        }
        $cz_prefix = config('database.connections.mysql.prefix');//数据表前缀

        if ($order_repeat_step==1) {
            //检测临时表是否存在，存在则删除
            $is_table_exist=Db::query('SHOW TABLES LIKE "'.$cz_prefix.'temptable"');
            if($is_table_exist){
                Db::execute("Drop table `" . $cz_prefix . "temptable`");//删除临时表
            }
            //将id值和查重条件添加到临时表
            Db::execute("create table `" . $cz_prefix . "temptable` as (select MAX(id) id," . $web_config_data["order_repeat_check_fields"] . ",count(*) from `" . $cz_prefix . "order` where is_del=0 group by " . $web_config_data["order_repeat_check_fields"] . " having count(*) > 1);");//创建临时表
            caozha_alert_msg("订单查重：<br><font color='red'>第".$order_repeat_step."步</font>，查重数据和条件收集，已经完成。<br>请不要关闭页面，系统正在处理下一步，请耐心等待……", url("admin/order/repeat_check")."?step=2", 3600, 1);
        }

        if ($order_repeat_step == 2) {
            //检测临时表2是否存在，存在则删除
            $is_table_exist=Db::query('SHOW TABLES LIKE "'.$cz_prefix.'temptable2"');
            if($is_table_exist){
                Db::execute("Drop table `" . $cz_prefix . "temptable2`");//删除临时表
            }
            //根据查重条件查询出重复数据中id值不为最大id的所有记录的id，此处一定要注意使用去重条件去查询，不然会将其他不重复的数据查询出来
            $where_sql = "";
            foreach ($order_repeat_check_field_arr as $field) {
                $where_sql .= " and a." . $field . "=`" . $field . "`";
            }
//            print_r("Create table `" . $cz_prefix . "temptable2` SELECT id FROM `" . $cz_prefix . "order` a WHERE EXISTS ( SELECT id FROM `" . $cz_prefix . "temptable` WHERE a.is_del=0 and a.id != id " . $where_sql . ")");
//                exit;
            Db::execute("Create table `" . $cz_prefix . "temptable2` SELECT id FROM `" . $cz_prefix . "order` a WHERE EXISTS ( SELECT id FROM `" . $cz_prefix . "temptable` WHERE a.is_del=0 and a.id != id " . $where_sql . ")");
            caozha_alert_msg("订单查重：<br><font color='red'>第".$order_repeat_step."步</font>，建立临时表保存重复的订单数据，已经完成。<br>请不要关闭页面，系统正在处理下一步，请耐心等待……", url("admin/order/repeat_check")."?step=3", 3600, 1);
        }

        if ($order_repeat_step == 3) {
            //根据临时表temptable2中的id值设置为重复数据
            Db::execute("UPDATE `" . $cz_prefix . "order` SET `is_repeat`=1 where id in (select id from `" . $cz_prefix . "temptable2`)");
            caozha_alert_msg("订单查重：<br><font color='red'>第".$order_repeat_step."步</font>，给重复的订单设置标记，已经完成。<br>请不要关闭页面，系统正在处理下一步，请耐心等待……", url("admin/order/repeat_check")."?step=4", 3600, 1);
        }

        if ($order_repeat_step == 4) {
            //获取重复订单总数
            $order_query = Db::query("select count(*) as total from `" . $cz_prefix . "temptable2`");
            $order_repeat_check_total = $order_query[0]["total"];
            //删除临时表
            Db::execute("Drop table `" . $cz_prefix . "temptable`");
            Db::execute("Drop table `" . $cz_prefix . "temptable2`");
            write_syslog(array("log_content" => "批量检测重复订单：本次共处理" . $order_repeat_check_total . "条重复订单"));//记录系统日志
            caozha_success("批量检测重复订单完成。<br>本次共标记了 <font color='red'>" . $order_repeat_check_total . "</font> 条重复订单。<br>可到订单列表搜索“重复订单”，也可以一键删除重复订单。", "", 1);
        }
    }

    public function repeat_check_old1()//批量检测重复订单，并设置重复状态，旧的方法，性能差
    {
        cz_auth("order_repeat");//检测是否有权限

        set_time_limit(0);//永不超时
        //ignore_user_abort(true);//即使关闭浏览器也不中断程序执行

        $order_repeat_check_field_arr = [];
        $order_repeat_check_limit = 500;
        $order_upload_memory_limit = 1000;
        $web_config = WebConfigModel::where("id", ">=", 1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。", "", 1);
        } else {
            $web_config_data = object_to_array($web_config->web_config);
            $order_repeat_check_field_arr = explode(",", $web_config_data["order_repeat_check_fields"]);
            $order_repeat_check_limit = $web_config_data["order_repeat_check_limit"];
            $order_upload_memory_limit = $web_config_data["order_upload_memory_limit"];
        }

        ini_set('memory_limit', $order_upload_memory_limit . 'M');//允许PHP使用最大内存，当处理的数据太大的时候，建议适当修改大小，不过容易导致内存溢出错误。

        $list = Db::name('order')->where("is_check_repeat", "=", 0)->paginate([
            'list_rows' => $order_repeat_check_limit,//每次处理数量
            'page' => 1,
        ])->toArray();

        $res_total = $list["total"] - $list["per_page"];//剩余处理数据
        $res_per = $list["per_page"];//本次处理数据
        $res_data = $list["data"];//本次待处理的订单数据
        //print_r($list);exit();

        foreach ($res_data as $order) {
            $where_data = array("is_check_repeat" => 1);
            foreach ($order_repeat_check_field_arr as $field) {
                $where_data[$field] = $order[$field];
            }
            //$list_check=OrderModel::where([["is_check_repeat","=",1],["tel","=",$order["tel"]]])->findOrEmpty();
            $list_check = OrderModel::where($where_data)->findOrEmpty();
            if ($list_check->isEmpty()) {//不重复
                Db::name('order')->where('id', "=", $order["id"])->update(['is_check_repeat' => 1]);
            } else {//重复
                Db::name('order')->where('id', "=", $order["id"])->update(['is_check_repeat' => 1, 'is_repeat' => 1]);
            }
        }
        if ($res_total > 0) {
            write_syslog(array("log_content" => "批量检测重复订单：剩余处理" . $res_total . "条，本次处理" . $res_per . "条"));//记录系统日志
            caozha_alert_msg("本次批量检测完成 <font color='red'>" . $res_per . "</font> 条订单数据，<br>剩余 <font color='red'>" . $res_total . "</font> 条将在下一批处理。<br>请不要关闭页面，系统正在处理下一批数据，请耐心等待……", url("admin/order/repeat_check"), 3600, 1);
        } else {
            caozha_success("批量检测重复订单完成", "", 1);
        }

    }

    public function repeat_del_confirm()//确认是否删除重复订单
    {
        cz_auth("order_repeat_del");//检测是否有权限
        $alert = '是否确认删除所有标记为重复的订单？按确认继续删除，按取消退出。';
        $js_code = 'window.location.href="' . url("admin/order/repeat_del") . '";';
        caozha_confirm($alert, $js_code, 1);
    }

    public function repeat_del()//删除重复订单
    {
        cz_auth("order_repeat_del");//检测是否有权限
        $res = Db::name('order')->where('is_repeat', "=", 1)->update(['is_del' => 1]);
        write_syslog(array("log_content" => "批量删除" . $res . "条重复订单。"));//记录系统日志
        caozha_success("已成功批量删除" . $res . "条重复订单。", "", 1);
    }

    public function del_order_send()//从订单总表中删除另外一个表中已经存在的订单记录，主要用于删除已发过短信广告的订单。
    {
        $all_num=688648;//订单总数，参数需要自己对照数据表的总记录来改。
        $page_num=5000;//每页处理数
        $page_all=ceil($all_num/$page_num);//总页数
        $page = Request::param("page", '', 'filter_sql');
        if(!is_numeric($page)){
            $page = 1;
        }
        $order_curr=$page_num*($page-1);//当前处理到
        //cz_totalorder=总表，cz_order=已经发送过短信的表
        $cz_sql="DELETE FROM `cz_totalorder` WHERE `tel` in (select t.tel from (SELECT tel FROM `cz_order` order by id asc limit ".$order_curr.",".$page_num.")as t)";
        $res_num=Db::execute($cz_sql);
        write_syslog(array("log_content" => "批量删除已发送过的订单：当前第".$page."页，共删除" . $res_num."个，SQL：".$cz_sql));//记录系统日志
        $page+=1;
        if($page>$page_all){
            caozha_alert_msg("删除已发送过的订单：<br>完成。", "", 3600, 1);
        }
        $jump_url=url("admin/order/del_order_send")."?page=".$page;
        caozha_alert_msg("删除已发送过的订单：<br><br>当前进度：".($page-1)."/".$page_all."<br>请不要关闭页面，系统正在处理下一步，请耐心等待……<br><a href='".$jump_url."' style='color:#999;'>如页面长时间无响应，请点击这里继续</a>", $jump_url, 3600, 1);
    }

}
