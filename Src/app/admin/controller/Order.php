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
use app\admin\model\WebConfig as WebConfigModel;
use think\Exception;
use think\facade\Config;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;

//引入自动加载类
require_once "../vendor/autoload.php";
//使用Spreadsheet类
use PhpOffice\PhpSpreadsheet\Spreadsheet;
//xls格式类
use PhpOffice\PhpSpreadsheet\Writer\Xls;
//可以生成多种格式类
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        });

        $pro_signs=trim(Session::get("pro_signs"));
        if ($pro_signs) {
            $order = $order->where(function ($query) use ($pro_signs) {
                //解决生成的SQL语句不自动加括号的问题
                $pro_signs_arr=explode(",",$pro_signs);
                foreach ($pro_signs_arr as $value){
                    $query->whereOr("pro_sign","=",$value);
                }

            });
        }

        $order = $order->findOrEmpty();

        //print_r(Db::getLastSql());

        if ($order->isEmpty()) {
            caozha_error("[ID:" . $order_id . "]订单不存在或无权限查看。", "", 1);
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
            return str_ireplace("/@/","",$value);
        })->order('addtime', 'desc');

        $action = Request::param('', '', 'filter_sql');//过滤注入
        if (isset($action["keyword"])) {
            if ($action["keyword"] != "") {
                $list = $list->where("realname|tel|addresss|remarks|pro_name|pro_options|pro_sign|wechat|qq|email|postal_code|admin_remarks|pro_url|from_url", "like", "%" . $action["keyword"] . "%");
            }
        }

        $pro_signs=trim(Session::get("pro_signs"));
        if ($pro_signs) {
            $list = $list->where(function ($query) use ($pro_signs) {
                //解决生成的SQL语句不自动加括号的问题
                $pro_signs_arr=explode(",",$pro_signs);
                foreach ($pro_signs_arr as $value){
                    $query->whereOr("pro_sign","=",$value);
                }

            });
        }

        $action["starttime"]=isset($action["starttime"])?$action["starttime"]:"";
        $action["endtime"]=isset($action["endtime"])?$action["endtime"]:"";
        if ($action["starttime"]) {
            $list = $list->where('addtime','>=', $action["starttime"]);
        }
        if ($action["endtime"]) {
        $list = $list->where('addtime','<=', $action["endtime"]);
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
            return $this->export_to_excel($list->select()->toArray(),$format_arr_data,$export_type,true);
        }

        $list = $list->paginate([
            'list_rows' => $limit,//每页数量
            'page' => $page,//当前页
        ]);
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
    public function export_to_excel($source_arr_data,$format_arr_data,$export_type="csv",$is_syslog=false){
        if(!$source_arr_data || !$format_arr_data){return false;}
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        set_time_limit(0);
        $export_count=count($source_arr_data);
        if($export_type=="csv"){
            $csv_txt=implode(",",$format_arr_data);
            foreach ($source_arr_data as $k => $v) {
                $csv_txt_temp="";
                foreach ($format_arr_data as $k_temp => $v_temp) {
                    $csv_txt_temp.=',"'.$v[$k_temp].'"';
                }
                $csv_txt.="\r\n".mb_substr($csv_txt_temp, 1);
            }
            $filename="客户订单(共".$export_count."条)_".date('YmdHis').".csv";
            if($is_syslog){
                write_syslog(array("log_content" => "下载订单：".$filename."，" . $_SERVER['QUERY_STRING']));//记录系统日志
            }
            return download($csv_txt, $filename, true);
        }elseif ($export_type=="xls" || $export_type=="xlsx"){

            //总结规律 设置参数的时候如果用$sheet $sheet->setTitle('Hello');而用$spreadsheet $spreadsheet->getActiveSheet()->setTitle('Hello'); 所有参数应该都可用这里两种方法
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            //设置sheet的名字  两种方法
            //$sheet->setTitle('phpspreadsheet——demo');
            $sheet->setTitle('订单');
            //设置第一行小标题
            $k = 1;
            $list_i=0;
            foreach ($format_arr_data as $list_k => $list_v) {
                $list_i+=1;
                $sheet->setCellValue(getExcelValue($list_i).$k, $list_v);
                $sheet->getColumnDimension(getExcelValue($list_i))->setWidth(15);//设置列的宽度
                //$sheet->getColumnDimension(getExcelValue($list_i))->setAutoSize(true);//自动设置列宽
                $sheet->getStyle(getExcelValue($list_i).$k)->getFont()->setBold(true);// 一定范围内字体加粗
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

                $list_i=0;
                foreach ($format_arr_data as $list_k => $list_v) {
                    $list_i+=1;
                    $sheet->setCellValue(getExcelValue($list_i).$k, $value[$list_k]);
                }
                $k++;
            }

            $filename="客户订单(共".$export_count."条)_".date('YmdHis').".".$export_type;
            if($is_syslog){
                write_syslog(array("log_content" => "下载订单：".$filename."，" . $_SERVER['QUERY_STRING']));//记录系统日志
            }

            //第一种保存方式
            /*$writer = new Xls($spreadsheet);
            //保存的路径可自行设置
            $file_name = '../'.$file_name . ".xls";
            $writer->save($file_name);*/
            //第二种直接页面上显示下载
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            if($export_type=="xls"){
                $writer = IOFactory::createWriter($spreadsheet, 'Xls'); //注意createWriter($spreadsheet, 'Xls') 第二个参数首字母必须大写
            }else{
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); //注意createWriter($spreadsheet, 'Xlsx') 第二个参数首字母必须大写
            }

            $writer->save('php://output');
        }

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
        }elseif($act=="permanently_del"){//彻底删除订单
            if(!is_cz_auth("order_recycle")){//检测是否有权限
                $list = array("code" => 0, "msg"=>"删除失败！您没有彻底删除订单的权限。");
                $act="";
            }
        }elseif($act=="permanently_del_all"){//清空订单回收站
            if(!is_cz_auth("order_recycle")){//检测是否有权限
                $list = array("code" => 0, "msg"=>"清空订单回收站失败！您没有清空订单回收站的权限。");
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

        }elseif($act=="permanently_del_all"){

            //彻底清空订单回收站
            $del_num = OrderModel::where("is_del", "=", 1)->delete();

            if ($del_num > 0) {
                write_syslog(array("log_content" => "清空订单回收站，共彻底删除了".$del_num."条订单。"));//记录系统日志
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


    public function upload()//订单上传
    {
        cz_auth("order_upload");//检测是否有权限
        // 模板输出
        return View::fetch('order/upload');
    }

    public function upload_save()//订单上传处理
    {
        cz_auth("order_upload");//检测是否有权限
        // 获取表单上传文件
        $file = request()->file('order_file');
        try {
            validate(['image'=>'filesize:'.(1024*1024*20).'|fileExt:csv,xls,xlsx'])->check(['file'=>$file]);
            $savename =  \think\facade\Filesystem::putFile( 'excel', $file,function(){return "file_".Session::get("admin_id");});
            return json(array("code"=>1,"msg"=>"上传成功","filename"=>$savename));
        } catch (\think\exception\ValidateException $e) {
//            echo $e->getMessage();
            return json(array("code"=>0,"msg"=>"上传失败","filename"=>""));
        }
    }

    public function import()//订单导入处理
    {
        cz_auth("order_upload");//检测是否有权限
        if(!Request::isAjax()){
            // 如果不是AJAX
            return result_json(0,"error:not ajax.");
        }
        $action = Request::param("", '', 'filter_sql');
        $action["filename"]=isset($action["filename"])?$action["filename"]:"";
        if(!$action["filename"]){
            return result_json(0,"filename不能为空");
        }

        $fileExtendName = substr(strrchr($action["filename"], '.'), 1);//文件后缀

        // 有Xls、Xlsx、csv等格式
        if( $fileExtendName =='xlsx' ){
            $objReader = IOFactory::createReader('Xlsx');
        }else if( $fileExtendName =='csv' ){
            $objReader = IOFactory::createReader('Csv');
        }else{
            $objReader = IOFactory::createReader('Xls');
        }
        $objReader->setReadDataOnly(TRUE);
        $filename = runtime_path()."storage/".$action["filename"];
        $objPHPExcel = $objReader->load($filename);  //$filename可以是上传的表格，或者是指定的表格
        $sheet = $objPHPExcel->getSheet(0);   //excel中的第一张表sheet
        $highestRow = $sheet->getHighestRow();       // 取得总行数
        $realHighestRow =$highestRow -2;//取得去除前面两行外的真实行数
        $highestColumn = $sheet->getHighestColumn();   // 取得最大的列，如：G
        // 最大列数+1 实际上是往后错一列
        ++$highestColumn;
        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        if($realHighestRow <= 0){
            return result_json(0,"excel表格没有数据！");
        }

        //获取所有字段名
        $fields_arr=array();
        $cz_prefix=config('database.connections.mysql.prefix');//数据表前缀
        $row_field = Db::query("SHOW FULL COLUMNS FROM ".$cz_prefix."order");
        foreach ($row_field as $value){
            $fields_arr[]=$value["Field"];
        }

        //获取第二行的列，用来做字段名，判断
        $fields_order=[];
        for ($colIndex = 'A'; $colIndex != $highestColumn; $colIndex++) {
            //$val = trim($sheet->getCellByColumnAndRow($colIndex, 2)->getValue());
            // 组装单元格标识  A1  A2
            $addr = $colIndex . 2;
            // 获取单元格内容
            $val = $sheet->getCell($addr)->getValue();
            if(in_array($val,$fields_arr)){//判断是否合法
                $fields_order[$val]=$colIndex;
            }
        }


        //循环读取excel表格，整合成数组。如果是不指定key的二维，就用$data[i][j]表示。
        $insertData=[];
        for ($j = 3; $j <= $highestRow; $j++) {
            $insertData_temp=array();
            foreach ($fields_order as $key=>$value){
                $insertData_temp[$key]=trim($objPHPExcel->getActiveSheet()->getCell($value . $j)->getValue());
            }
            $insertData[]=$insertData_temp;
        }

        //批量插入数据建议使用Db::insertAll() 方法，只会进行一次数据库请求；saveAll 方法实际上是 循环数据数组，每一条数据进行一遍save方法
        // 分批写入 每次最多500条数据
        $res = Db::name('order')
            ->limit(500)
            ->insertAll($insertData);
        write_syslog(array("log_content" => "批量导入订单，共导入".$res."条订单数据。"));//记录系统日志
        return json(array("code"=>1,"msg"=>"导入订单成功，共导入".$res."条订单数据！"));
    }

    public function repeat_confirm()//确认是否检测重复订单
    {
        cz_auth("order_repeat");//检测是否有权限
        $alert='校检重复订单需要较长时间，请您点击“确认”按钮后，不要做任何操作，只需静待页面显示成功即可。<br>点击“确认”开始执行，点击“取消”则不执行！';
        $js_code='window.location.href="'.url("admin/order/repeat_check").'";';
        caozha_confirm($alert, $js_code, 1);
    }

    public function repeat_check()//批量检测重复订单，并设置重复状态
    {
        cz_auth("order_repeat");//检测是否有权限

        $order_repeat_check_field_arr=[];
        $web_config=WebConfigModel::where("id",">=",1)->limit(1)->findOrEmpty();
        if ($web_config->isEmpty()) {
            caozha_error("系统设置的数据表不存在。","",1);
        }else{
            $web_config_data=object_to_array($web_config->web_config);
            $order_repeat_check_field_arr=explode(",",$web_config_data["order_repeat_check_fields"]);
        }

        $list = Db::name('order')->where("is_check_repeat","=",0)->select()->toArray();
        foreach ($list as $order) {
            $where_data=array("is_check_repeat"=>1);
            foreach ($order_repeat_check_field_arr as $field) {
                $where_data[$field]=$order[$field];
            }
            //$list_check=OrderModel::where([["is_check_repeat","=",1],["tel","=",$order["tel"]]])->findOrEmpty();
            $list_check=OrderModel::where($where_data)->findOrEmpty();
            if ($list_check->isEmpty()) {//不重复
                Db::name('order')->where('order_id',"=", $order["order_id"])->update(['is_check_repeat' => 1]);
            }else{//重复
                Db::name('order')->where('order_id',"=", $order["order_id"])->update(['is_check_repeat' => 1,'is_repeat' => 1]);
            }
        }
        write_syslog(array("log_content" => "批量检测重复订单。"));//记录系统日志
        caozha_success("批量检测重复订单完成","",1);
    }

    public function repeat_del_confirm()//确认是否删除重复订单
    {
        cz_auth("order_repeat_del");//检测是否有权限
        $alert='是否确认删除所有标记为重复的订单？按确认继续删除，按取消退出。';
        $js_code='window.location.href="'.url("admin/order/repeat_del").'";';
        caozha_confirm($alert, $js_code, 1);
    }

    public function repeat_del()//删除重复订单
    {
        cz_auth("order_repeat_del");//检测是否有权限
        $res=Db::name('order')->where('is_repeat',"=",1)->update(['is_del' => 1]);
        write_syslog(array("log_content" => "批量删除".$res."条重复订单。"));//记录系统日志
        caozha_success("已成功批量删除".$res."条重复订单。","",1);
    }

}
