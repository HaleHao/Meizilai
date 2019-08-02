<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/7 0007
 * Time: 15:35
 */
return [
    'use_type' => [
        0 => '次数',
        1 => '有效期'
    ],
    'comment_type' => [
        1 => '好评',
        2 => '一般',
        3 => '差评'
    ],
    'store_grade' => [
        1 => '满意',
        2 => '一般',
        3 => '不满意'
    ],
    'user_type' => [
        0 => '普通会员',
        1 => '办卡会员',
        2 => '共享合伙人',
        3 => '美容师',
        4 => '店主'
    ],

    'goods' => [
        'order_status' => [
            0 => '待付款',
            1 => '待发货',
            2 => '待收货',
            3 => '待取货',
            4 => '待评价',
            5 => '已完成',
            6 => '已取消',
            7 => '待接单',
            8 => '已拒绝',
        ],
        'pay_status' => [
            0 => '未支付',
            1 => '已支付',
            2 => '已退款'
        ],
        'delivery_method'=>[
            1 => '快递配送',
            2 => '门店自提'
        ]

    ],
    'card' => [
        'order_status' => [
            0 => '待支付',
            1 => '已购买'
        ],
        'pay_status' => [
            0 => '未支付',
            1 => '已支付'
        ],
        'card_level' => [
            0 => '体验卡',
            1 => '银卡',
            2 => '金卡',
            3 => '钻石卡'
        ]
    ],
    'serve' => [
        'order_status' => [
            0 => '待付款',
            1 => '待确认',
            2 => '待服务',
            3 => '已拒绝',
            4 => '已服务',
            5 => '待评价',
            6 => '已完成',
            7 => '已取消',
        ],
        'pay_status' => [
            0 => '未支付',
            1 => '已支付',
            2 => '已退款',
        ],
        'grade' => [
            1 => '满意',
            2 => '一般',
            3 => '不满意'
        ]
    ],
    'level' => [
        'order_status' => [
            0 => '待付款',
            1 => '待审核',
            2 => '审核通过',
            3 => '审核拒绝',
        ],
        'pay_status' => [
            0 => '未支付',
            1 => '已支付',
            2 => '已退款',
        ],
        'apply_status' => [
            0 => '待审核',
            1 => '审核通过',
            2 => '审核失败',
        ]
    ],

    'earnings' => [
        'goods' => '商品',
        'card' => '会员卡',
        'level' => '等级',
        'device' => '设备'
    ],

    'device' => [
        'device_control' => [
            'open' => 'EC 08 04 01 00 00 00 00 01 EF',
            'close' => 'EC 08 04 00 00 00 00 00 01 EF',
        ],
        'relay_control' => [
            'open' => '10 04 01',
            'close' => '10 04 00',
        ],
        'sound_control' => [
            'open' => '10 05 01 '.config('device_sound',20),
            'close' => '10 05 02 '.config('device_sound',20),
        ]

    ],

    'express' => [

        [
            'express_company' => '顺丰速运',
            'company_sn' => 'SF',
        ],
        [
            'express_company' => '百世快递',
            'company_sn' => 'HTKY',
        ],
        [
            'express_company' => '中通快递',
            'company_sn' => 'ZTO',
        ],
        [
            'express_company' => '申通快递',
            'company_sn' => 'STO',
        ],
        [
            'express_company' => '圆通速递',
            'company_sn' => 'YTO',
        ],
        [
            'express_company' => '韵达速递',
            'company_sn' => 'YD',
        ],
        [
            'express_company' => '邮政快递包裹',
            'company_sn' => 'YZPY',
        ],
        [
            'express_company' => 'EMS',
            'company_sn' => 'EMS',
        ],
        [
            'express_company' => '天天快递',
            'company_sn' => 'HHTT',
        ],
        [
            'express_company' => '京东快递',
            'company_sn' => 'JD',
        ],
        [
            'express_company' => '优速快递',
            'company_sn' => 'UC',
        ],
        [
            'express_company' => '德邦快递',
            'company_sn' => 'DBL',
        ],
        [
            'express_company' => '宅急送',
            'company_sn' => 'ZJS',
        ],

    ],

    'bank' => [
        [
            'bank_name' => '工商银行',
            'bank_code' => 1002
        ],
        [
            'bank_name' => '农业银行',
            'bank_code' => 1005
        ],
        [
            'bank_name' => '中国银行',
            'bank_code' => 1026
        ],
        [
            'bank_name' => '建设银行',
            'bank_code' => 1003
        ],
        [
            'bank_name' => '招商银行',
            'bank_code' => 1001
        ],
        [
            'bank_name' => '邮储银行',
            'bank_code' => 1066
        ],
        [
            'bank_name' => '交通银行',
            'bank_code' => 1020
        ],
        [
            'bank_name' => '浦发银行',
            'bank_code' => 1004
        ],
        [
            'bank_name' => '民生银行',
            'bank_code' => 1006
        ],
        [
            'bank_name' => '兴业银行',
            'bank_code' => 1009
        ],
        [
            'bank_name' => '平安银行',
            'bank_code' => 1010
        ],
        [
            'bank_name' => '中信银行',
            'bank_code' => 1021
        ],
        [
            'bank_name' => '华夏银行',
            'bank_code' => 1025
        ],
        [
            'bank_name' => '广发银行',
            'bank_code' => 1027
        ],
        [
            'bank_name' => '光大银行',
            'bank_code' => 1022
        ],
        [
            'bank_name' => '北京银行',
            'bank_code' => 1032
        ],
        [
            'bank_name' => '宁波银行',
            'bank_code' => 1056
        ],

    ]
];