<?php

return [
    //主菜单
    'main_menu'=> [
        'projects'=>'项目管理', 'shop'=>'商城管理', 'service_life'=>'生活服务', 
        'user_center'=>'用户中心','awards'=>'奖励管理', 'information'=>'信息管理',
        'sys_management'=>'系统管理'
    ],
    //项目管理
    'projects' => [ '项目列表'=>'/list_projects', '项目分类'=>'/categories_project', 
                '已投项目'=>'/invested_project', '返息明细'=>'/interest'],
    //商城管理
    'shop'=>['商品管理'=>'/productions', '订单管理'=>'/orders_productions'],
    //生活服务
    'service_life'=>['充值缴费'=>'/payment_charge','订单管理'=>'/orders_service'],
    //用户中心
    'user_center'=>['会员列表'=>'/list_members','会员等级管理'=>'/levels_members','用户钱包列表'=>'/wallets',
    '团队等级管理'=>'/levels_team','钱包绑定统计'=>'/bind_wallet','流水记录'=>'/financial_records',
    '充值记录'=>'/charge_records','提现记录'=>'/withdrawal_records'],
    //奖励管理
    'awards'=>['系统奖励'=>'/awards', '签到奖励'=>'/sign'],
    //信息管理
    'information'=>['文章列表'=>'/article', '文章分类'=>'/category', '站内信列表'=>'/indexbox'],
    //系统管理
    'sys_management'=>[
        '网站信息'=>'/website','首页弹窗设置'=>'/windowhomepage', '支付设置'=>'/payment', 
        '系统图片设置'=>'/slide', 'APP版本设置'=>'/appversion', '合同设置'=>'/agreement',
        '管理员操作日志'=>'/log_manager', '程序参数设置'=>'/parameters_process', '系统日志管理'=>'/log_system', 
        '访问权限管理'=>'/role', '系统用户管理'=>'/sysusers'
    ],

    //支付类型  1 加密货币 2 支付宝 3 微信 4 银行卡
    'payment_ways' => [
        1=>'加密货币', 4=>'银行卡'
    ],

];