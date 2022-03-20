<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型使用Think
    'type'          => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'     => 1,
    // 模板目录名
    'view_dir_name' => 'view',
    // 模板后缀
    'view_suffix'   => 'html',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'     => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'       => '}',
    // 标签库标签开始标记
    'taglib_begin'  => '{',
    // 标签库标签结束标记
    'taglib_end'    => '}',
    //设置默认的过滤方法，默认：htmlentities，可替换：htmlspecialchars等。为防止XSS安全问题，建议用默认。
    'default_filter' => 'htmlentities',
    // 替换标签
    'tpl_replace_string'  =>  [
        '__CAOZHA-SYS-NAME__'=>$GLOBALS["caozha_common_config"]["name"],
        '__CAOZHA-SYS-VERSION__'=>$GLOBALS["caozha_common_config"]["version"],
        '__CAOZHA-SYS-GITEE__'=>$GLOBALS["caozha_common_config"]["gitee"],
        '__CAOZHA-SYS-GITHUB__'=>$GLOBALS["caozha_common_config"]["github"],
        '__CAOZHA-ADMIN-SYS-NAME__'=>$GLOBALS["caozha_admin_sys"]["name"],
        '__CAOZHA-ADMIN-SYS-VERSION__'=>$GLOBALS["caozha_admin_sys"]["version"],
        '__CAOZHA-ADMIN-SYS-URL__'=>$GLOBALS["caozha_admin_sys"]["url"],
        '__CAOZHA-STATIC__'=>get_cz_path().'static/admin/caozha',
        '__CAOZHA-LAYUIMINI__'=>get_cz_path().'static/admin/layuimini',
        ]
];
