<?php
/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * =========================================================
 */

namespace app\event;

use app\model\web\DiyView as DiyViewModel;


/**
 * 增加默认自定义数据：门店主页
 */
class AddStoreDiyView
{

    public function handle($param)
    {
        if (!empty($param[ 'site_id' ]) && addon_is_exit('store', $param[ 'site_id' ])) {

            $diy_view_model = new DiyViewModel();

            $img_url = "addon/store/shop/view/public/img/template";

            // 添加自定义主页装修
            $value = json_encode(
                [
                    "global" => [
                        "title" => "门店主页",
                        "pageBgColor" => "#F5F6FA",
                        "topNavColor" => "#04C561",
                        "topNavBg" => true,
                        "navBarSwitch" => true,
                        "navStyle" => "4",
                        "textNavColor" => "#FFFFFF",
                        "topNavImg" => "",
                        "moreLink" => [
                            "name" => ""
                        ],
                        "openBottomNav" => true,
                        "textImgPosLink" => "center",
                        "mpCollect" => false,
                        "popWindow" => [
                            "imageUrl" => "",
                            "count" => -1,
                            "show" => 0,
                            "link" => [
                                "name" => ""
                            ],
                            "imgWidth" => "",
                            "imgHeight" => ""
                        ],
                        "bgUrl" => $img_url . "/bg.png",
                        "imgWidth" => "375",
                        "imgHeight" => "247",
                        "template" => [
                            "pageBgColor" => "",
                            "textColor" => "#303133",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 0,
                            "bottomAroundRadius" => 0,
                            "elementBgColor" => "",
                            "elementAngle" => "round",
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 0,
                                "bottom" => 0,
                                "both" => 12
                            ]
                        ]
                    ],
                    "value" => [
                        [
                            "searchStyle" => 2,
                            "title" => "请输入搜索关键词",
                            "textAlign" => "left",
                            "borderType" => 2,
                            "iconType" => "img",
                            "icon" => "",
                            "style" => [
                                "fontSize" => "60",
                                "iconBgColor" => [],
                                "iconBgColorDeg" => 0,
                                "iconBgImg" => "",
                                "bgRadius" => 0,
                                "iconColor" => [
                                    "#000000"
                                ],
                                "iconColorDeg" => 0
                            ],
                            "imageUrl" => $img_url . "/search.png",
                            "id" => "4v0q7l4eyhs0",
                            "addonName" => "",
                            "componentName" => "Search",
                            "componentTitle" => "搜索框",
                            "searchLink" => [
                                "name" => ""
                            ],
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "textColor" => "#303133",
                            "componentBgColor" => "",
                            "topAroundRadius" => 0,
                            "bottomAroundRadius" => 0,
                            "elementBgColor" => "#FFFFFF",
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ],
                            "imgWidth" => "60",
                            "imgHeight" => "60",
                            "positionWay" => "fixed"
                        ],
                        [
                            "list" => [
                                [
                                    "title" => "新到货",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#000000"
                                        ],
                                        "iconColorDeg" => 0
                                    ],
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "icon" => "",
                                    "bgColorStart" => "#FA6400",
                                    "bgColorEnd" => "#FA6400",
                                    "textColor" => "#FFFFFF",
                                    "iconType" => "img",
                                    "imageUrl" => $img_url . "/new.png",
                                    "id" => "d4ywk2pbons0",
                                    "imgWidth" => "33",
                                    "imgHeight" => "32"
                                ],
                                [
                                    "title" => "新鲜猪肉",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#FA6400"
                                        ],
                                        "iconColorDeg" => 0
                                    ],
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "icon" => "",
                                    "bgColorStart" => "#FFFBDC",
                                    "bgColorEnd" => "#FFFBDC",
                                    "textColor" => "#FA6400",
                                    "iconType" => "icon",
                                    "imageUrl" => "",
                                    "id" => "1jip6f31rlog0"
                                ],
                                [
                                    "title" => "新鲜蔬菜",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#000000"
                                        ],
                                        "iconColorDeg" => 0
                                    ],
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "icon" => "",
                                    "bgColorStart" => "#F9FEE8",
                                    "bgColorEnd" => "#F9FEE8",
                                    "textColor" => "#2EB44B",
                                    "iconType" => "img",
                                    "imageUrl" => "",
                                    "id" => "1oai3grdteps0"
                                ],
                                [
                                    "title" => "米线面条",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#000000"
                                        ],
                                        "iconColorDeg" => 0
                                    ],
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "icon" => "",
                                    "bgColorStart" => "#FFEEF6",
                                    "bgColorEnd" => "#FFEEF6",
                                    "textColor" => "#A5421B",
                                    "iconType" => "img",
                                    "imageUrl" => "",
                                    "id" => "11ocpk5d93280"
                                ],
                                [
                                    "title" => "豆腐脑",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#000000"
                                        ],
                                        "iconColorDeg" => 0
                                    ],
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "icon" => "",
                                    "bgColorStart" => "#EAFFF7",
                                    "bgColorEnd" => "#EAFFF7",
                                    "textColor" => "#0DB0F0",
                                    "iconType" => "img",
                                    "imageUrl" => "",
                                    "id" => "1qaypb973iio0"
                                ]
                            ],
                            "ornament" => [
                                "type" => "default",
                                "color" => "#EDEDED"
                            ],
                            "id" => "zfps1tn2uds",
                            "addonName" => "",
                            "componentName" => "QuickNav",
                            "componentTitle" => "快捷导航",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 0,
                            "bottomAroundRadius" => 0,
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "indicatorColor" => "#ffffff",
                            "carouselStyle" => "circle",
                            "indicatorLocation" => "center",
                            "list" => [
                                [
                                    "imageUrl" => $img_url . "/gg_1.png",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "imgWidth" => "702",
                                    "imgHeight" => "260",
                                    "id" => "1vpshofnu4o00"
                                ]
                            ],
                            "id" => "7j58u684wmk0",
                            "addonName" => "",
                            "componentName" => "ImageAds",
                            "componentTitle" => "图片广告",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 5,
                            "bottomAroundRadius" => 5,
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "style" => [
                                "fontSize" => 80,
                                "iconBgColor" => [],
                                "iconBgColorDeg" => 0,
                                "iconBgImg" => "",
                                "bgRadius" => 0,
                                "iconColor" => [
                                    "#7D7D7D"
                                ],
                                "iconColorDeg" => 0
                            ],
                            "labelIds" => [],
                            "icon" => "icondiy icon-system-dvd-line",
                            "contentStyle" => "style-1",
                            "sources" => "initial",
                            "previewList" => [
                                "label_id_lbeug2w6" => [
                                    "label_name" => "同城配送到家",
                                ],
                                "label_id_luz15aj6v" => [
                                    "label_name" => "最快30分钟达",
                                ],
                                "label_id_13l0pqbwrr" => [
                                    "label_name" => "本地电商",
                                ]
                            ],
                            "id" => "33a3vqu1uq8",
                            "addonName" => "store",
                            "componentName" => "StoreLabel",
                            "componentTitle" => "门店标签",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "textColor" => "#7D7D7D",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 0,
                            "bottomAroundRadius" => 0,
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ],
                            "iconType" => "icon",
                            "fontSize" => 14,
                            "fontWeight" => 'normal',
                            "count" => 3
                        ],
                        [
                            "contentStyle" => "style-1",
                            "list" => [
                                [
                                    "title" => " 每天接近饭点时派送运力紧张，请您尽量提前下单！",
                                    "link" => [
                                        "name" => ""
                                    ]
                                ]
                            ],
                            "sources" => "diy",
                            "iconSources" => "diy",
                            "noticeIds" => [],
                            "iconType" => "img",
                            "icon" => "",
                            "style" => [
                                "fontSize" => "60",
                                "iconBgColor" => [],
                                "iconBgColorDeg" => 0,
                                "iconBgImg" => "",
                                "bgRadius" => 0,
                                "iconColor" => [
                                    "#000000"
                                ],
                                "iconColorDeg" => 0
                            ],
                            "imageUrl" => $img_url . "/notice.png",
                            "scrollWay" => "upDown",
                            "id" => "svi17ueui8w",
                            "addonName" => "",
                            "componentName" => "Notice",
                            "componentTitle" => "公告",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "textColor" => "#7D7D7D",
                            "componentBgColor" => "#FFFFFF",
                            "componentAngle" => "round",
                            "topAroundRadius" => 8,
                            "bottomAroundRadius" => 8,
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ],
                            "imgWidth" => "44",
                            "imgHeight" => "44",
                            "fontSize" => 14,
                            "fontWeight" => 'normal'
                        ],
                        [
                            "mode" => "row1-of4",
                            "imageGap" => 10,
                            "list" => [
                                [
                                    "imageUrl" => $img_url . "/rubik_cube_1.png",
                                    "imgWidth" => "246",
                                    "imgHeight" => "162",
                                    "previewWidth" => 77.75,
                                    "previewHeight" => "51.201219512195124px",
                                    "link" => [
                                        "name" => ""
                                    ]
                                ],
                                [
                                    "imageUrl" => $img_url . "/rubik_cube_2.png",
                                    "imgWidth" => "246",
                                    "imgHeight" => "162",
                                    "previewWidth" => 77.75,
                                    "previewHeight" => "51.201219512195124px",
                                    "link" => [
                                        "name" => ""
                                    ]
                                ],
                                [
                                    "imageUrl" => $img_url . "/rubik_cube_3.png",
                                    "imgWidth" => "246",
                                    "imgHeight" => "162",
                                    "previewWidth" => 77.75,
                                    "previewHeight" => "51.201219512195124px",
                                    "link" => [
                                        "name" => ""
                                    ]
                                ],
                                [
                                    "imageUrl" => $img_url . "/rubik_cube_4.png",
                                    "imgWidth" => "246",
                                    "imgHeight" => "162",
                                    "previewWidth" => 77.75,
                                    "previewHeight" => "51.201219512195124px",
                                    "link" => [
                                        "name" => ""
                                    ]
                                ]
                            ],
                            "id" => "2cyenbhbislc",
                            "addonName" => "",
                            "componentName" => "RubikCube",
                            "componentTitle" => "魔方",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 0,
                            "bottomAroundRadius" => 0,
                            "elementAngle" => "round",
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "indicatorIsShow" => true,
                            "indicatorColor" => "#ffffff",
                            "carouselStyle" => "circle",
                            "indicatorLocation" => "center",
                            "list" => [
                                [
                                    "imageUrl" => $img_url . "/point.png",
                                    "link" => [
                                        "name" => "INTEGRAL_STORE",
                                        "title" => "积分商城",
                                        "wap_url" => "/pages_promotion/point/list",
                                        "parent" => "MARKETING_LINK"
                                    ],
                                    "imgWidth" => "702",
                                    "imgHeight" => "180",
                                    "id" => "1z69zcd5vkio0"
                                ]
                            ],
                            "id" => "1vtq5yokj44g",
                            "addonName" => "",
                            "componentName" => "ImageAds",
                            "componentTitle" => "图片广告",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 12,
                            "bottomAroundRadius" => 12,
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "mode" => "graphic",
                            "type" => "img",
                            "showStyle" => "fixed",
                            "ornament" => [
                                "type" => "default",
                                "color" => "#EDEDED"
                            ],
                            "rowCount" => 5,
                            "pageCount" => 2,
                            "carousel" => [
                                "type" => "circle",
                                "color" => "#FFFFFF"
                            ],
                            "imageSize" => 50,
                            "aroundRadius" => 25,
                            "font" => [
                                "size" => 14,
                                "weight" => "normal",
                                "color" => "#303133"
                            ],
                            "list" => [
                                [
                                    "title" => "新鲜蔬菜",
                                    "imageUrl" => $img_url . "/nav_1.png",
                                    "iconType" => "img",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#000000"
                                        ],
                                        "iconColorDeg" => 0
                                    ],
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => true,
                                        "text" => "热卖",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#FE8681",
                                        "bgColorEnd" => "#FF5754"
                                    ],
                                    "icon" => "",
                                    "id" => "1v99h14wqhr40",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "鲜肉蛋禽",
                                    "imageUrl" => $img_url . "/nav_2.png",
                                    "iconType" => "img",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#000000"
                                        ],
                                        "iconColorDeg" => 0
                                    ],
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => false,
                                        "text" => "热门",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#F83287",
                                        "bgColorEnd" => "#FE3423"
                                    ],
                                    "icon" => "",
                                    "id" => "1s5oxgjxss5c0",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "优选水果",
                                    "imageUrl" => $img_url . "/nav_3.png",
                                    "iconType" => "img",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#000000"
                                        ],
                                        "iconColorDeg" => 0
                                    ],
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => true,
                                        "text" => "草莓",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#FE8681",
                                        "bgColorEnd" => "#FF5754"
                                    ],
                                    "icon" => "",
                                    "id" => "1z76ythx14sg0",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "海鲜水产",
                                    "imageUrl" => $img_url . "/nav_4.png",
                                    "iconType" => "img",
                                    "style" => "",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => false,
                                        "text" => "热门",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#F83287",
                                        "bgColorEnd" => "#FE3423"
                                    ],
                                    "iconfont" => [
                                        "value" => "",
                                        "color" => ""
                                    ],
                                    "id" => "77stelj6ykc0",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "蛋糕甜点",
                                    "imageUrl" => $img_url . "/nav_5.png",
                                    "iconType" => "img",
                                    "style" => "",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => true,
                                        "text" => "蛋糕",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#FE8681",
                                        "bgColorEnd" => "#FF5754"
                                    ],
                                    "iconfont" => [
                                        "value" => "",
                                        "color" => ""
                                    ],
                                    "id" => "1zmlt86jn8xs0",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "牛奶乳品",
                                    "imageUrl" => $img_url . "/nav_6.png",
                                    "iconType" => "img",
                                    "style" => "",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => false,
                                        "text" => "热门",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#F83287",
                                        "bgColorEnd" => "#FE3423"
                                    ],
                                    "iconfont" => [
                                        "value" => "",
                                        "color" => ""
                                    ],
                                    "id" => "ld0uf9zrh2o0",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "厨房调料",
                                    "imageUrl" => $img_url . "/nav_7.png",
                                    "iconType" => "img",
                                    "style" => "",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => false,
                                        "text" => "热门",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#F83287",
                                        "bgColorEnd" => "#FE3423"
                                    ],
                                    "iconfont" => [
                                        "value" => "",
                                        "color" => ""
                                    ],
                                    "id" => "fruwfv8bads",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "米面粮油",
                                    "imageUrl" => $img_url . "/nav_8.png",
                                    "iconType" => "img",
                                    "style" => "",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => false,
                                        "text" => "热门",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#F83287",
                                        "bgColorEnd" => "#FE3423"
                                    ],
                                    "iconfont" => [
                                        "value" => "",
                                        "color" => ""
                                    ],
                                    "id" => "70g58o07zrk0",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "卤味蔬食",
                                    "imageUrl" => $img_url . "/nav_9.png",
                                    "iconType" => "img",
                                    "style" => "",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => false,
                                        "text" => "热门",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#F83287",
                                        "bgColorEnd" => "#FE3423"
                                    ],
                                    "iconfont" => [
                                        "value" => "",
                                        "color" => ""
                                    ],
                                    "id" => "186cp6fj4vr40",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ],
                                [
                                    "title" => "饮料酒水",
                                    "imageUrl" => $img_url . "/nav_10.png",
                                    "iconType" => "img",
                                    "style" => "",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "label" => [
                                        "control" => false,
                                        "text" => "热门",
                                        "textColor" => "#FFFFFF",
                                        "bgColorStart" => "#F83287",
                                        "bgColorEnd" => "#FE3423"
                                    ],
                                    "iconfont" => [
                                        "value" => "",
                                        "color" => ""
                                    ],
                                    "id" => "1ivu0ox8nmxs0",
                                    "imgWidth" => "147",
                                    "imgHeight" => "147"
                                ]
                            ],
                            "id" => "49qvi2em5is0",
                            "addonName" => "",
                            "componentName" => "GraphicNav",
                            "componentTitle" => "图文导航",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "#FFFFFF",
                            "componentAngle" => "round",
                            "topAroundRadius" => 12,
                            "bottomAroundRadius" => 12,
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "indicatorColor" => "#ffffff",
                            "carouselStyle" => "circle",
                            "indicatorLocation" => "center",
                            "list" => [
                                [
                                    "imageUrl" => $img_url . "/baozhang.png",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "imgWidth" => "702",
                                    "imgHeight" => "66",
                                    "id" => "1glfoxntyc1s0"
                                ]
                            ],
                            "id" => "68xzp1ipgkw0",
                            "addonName" => "",
                            "componentName" => "ImageAds",
                            "componentTitle" => "图片广告",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 0,
                            "bottomAroundRadius" => 0,
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "ornament" => [
                                "type" => "default",
                                "color" => "#EDEDED"
                            ],
                            "style" => "style-3",
                            "count" => 6,
                            "nameLineMode" => "single",
                            "sortWay" => "default",
                            "imgAroundRadius" => 10,
                            "goodsNameStyle" => [
                                "color" => "#303133",
                                "control" => false,
                                "fontWeight" => false,
                                "support" => false
                            ],
                            "saleStyle" => [
                                "color" => "#999CA7",
                                "control" => false,
                                "support" => false
                            ],
                            "theme" => "default",
                            "priceStyle" => [
                                "mainColor" => "#FF1544",
                                "mainControl" => true,
                                "lineColor" => "#999CA7",
                                "lineControl" => false,
                                "lineSupport" => false
                            ],
                            "sources" => "initial",
                            "goodsId" => [],
                            "categoryId" => 0,
                            "categoryName" => "请选择",
                            "topStyle" => [
                                "title" => "今日推荐",
                                "subTitle" => "大家都在买",
                                "icon" => [
                                    "value" => "icondiy icon-system-tuijian",
                                    "color" => "#FF3D3D",
                                    "bgColor" => ""
                                ],
                                "color" => "#303133",
                                "subColor" => "#999CA7",
                                "support" => false
                            ],
                            "bgUrl" => "app/component/view/goods_recommend/img/style3_bg.png",
                            "styleName" => "风格3",
                            "labelStyle" => [
                                "support" => true,
                                "bgColor" => "#FF504D",
                                "title" => "新人专享",
                                "color" => "#FFFFFF"
                            ],
                            "id" => "16uuk7vmsq5c",
                            "addonName" => "",
                            "componentName" => "GoodsRecommend",
                            "componentTitle" => "商品推荐",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 8,
                            "bottomAroundRadius" => 8,
                            "elementBgColor" => "",
                            "elementAngle" => "round",
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "mode" => "row1-lt-of2-rt",
                            "imageGap" => 10,
                            "list" => [
                                [
                                    "imageUrl" => $img_url . "/rubik_cube_5.png",
                                    "imgWidth" => "342",
                                    "imgHeight" => "350",
                                    "previewWidth" => 163.5,
                                    "previewHeight" => "177.32px",
                                    "link" => [
                                        "name" => ""
                                    ]
                                ],
                                [
                                    "imageUrl" => $img_url . "/rubik_cube_6.png",
                                    "imgWidth" => "342",
                                    "imgHeight" => "166",
                                    "previewWidth" => 163.5,
                                    "previewHeight" => "83.66px",
                                    "link" => [
                                        "name" => ""
                                    ]
                                ],
                                [
                                    "imageUrl" => $img_url . "/rubik_cube_7.png",
                                    "imgWidth" => "342",
                                    "imgHeight" => "166",
                                    "previewWidth" => 163.5,
                                    "previewHeight" => "83.66px",
                                    "link" => [
                                        "name" => ""
                                    ]
                                ]
                            ],
                            "id" => "47v1783p3oo0",
                            "addonName" => "",
                            "componentName" => "RubikCube",
                            "componentTitle" => "魔方",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 0,
                            "bottomAroundRadius" => 0,
                            "elementAngle" => "round",
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "indicatorColor" => "#ffffff",
                            "carouselStyle" => "circle",
                            "indicatorLocation" => "center",
                            "list" => [
                                [
                                    "imageUrl" => $img_url . "/gg_2.png",
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "imgWidth" => "702",
                                    "imgHeight" => "132",
                                    "id" => "1qmhc284vj7k0"
                                ]
                            ],
                            "id" => "jkx4ncjwfrk",
                            "addonName" => "",
                            "componentName" => "ImageAds",
                            "componentTitle" => "图片广告",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 8,
                            "bottomAroundRadius" => 8,
                            "topElementAroundRadius" => 0,
                            "bottomElementAroundRadius" => 0,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 6,
                                "both" => 12
                            ]
                        ],
                        [
                            "ornament" => [
                                "type" => "default",
                                "color" => "#EDEDED"
                            ],
                            "template" => "row1-of2",
                            "goodsMarginType" => "default",
                            "goodsMarginNum" => 10,
                            "style" => "style-2",
                            "sources" => "initial",
                            "count" => 6,
                            "goodsId" => [],
                            "categoryId" => 0,
                            "categoryName" => "请选择",
                            "sortWay" => "default",
                            "nameLineMode" => "single",
                            "imgAroundRadius" => 0,
                            "slideMode" => "scroll",
                            "theme" => "default",
                            "btnStyle" => [
                                "fontWeight" => false,
                                "padding" => 0,
                                "cartEvent" => "cart",
                                "text" => "购买",
                                "textColor" => "#FFFFFF",
                                "theme" => "default",
                                "aroundRadius" => 25,
                                "control" => true,
                                "support" => true,
                                "bgColor" => "#FF6A00",
                                "style" => "button",
                                "iconDiy" => [
                                    "iconType" => "icon",
                                    "icon" => "",
                                    "style" => [
                                        "fontSize" => "60",
                                        "iconBgColor" => [],
                                        "iconBgColorDeg" => 0,
                                        "iconBgImg" => "",
                                        "bgRadius" => 0,
                                        "iconColor" => [
                                            "#000000"
                                        ],
                                        "iconColorDeg" => 0
                                    ]
                                ]
                            ],
                            "tag" => [
                                "text" => "隐藏",
                                "value" => "hidden"
                            ],
                            "goodsNameStyle" => [
                                "color" => "#303133",
                                "control" => true,
                                "fontWeight" => false
                            ],
                            "saleStyle" => [
                                "color" => "#999CA7",
                                "control" => true,
                                "support" => true
                            ],
                            "priceStyle" => [
                                "mainColor" => "#FF6A00",
                                "mainControl" => true,
                                "lineColor" => "#999CA7",
                                "lineControl" => true,
                                "lineSupport" => true
                            ],
                            "id" => "4dd123y6pco",
                            "addonName" => "",
                            "componentName" => "GoodsList",
                            "componentTitle" => "商品列表",
                            "isDelete" => 0,
                            "pageBgColor" => "",
                            "componentBgColor" => "",
                            "componentAngle" => "round",
                            "topAroundRadius" => 8,
                            "bottomAroundRadius" => 8,
                            "elementBgColor" => "#FFFFFF",
                            "elementAngle" => "round",
                            "topElementAroundRadius" => 8,
                            "bottomElementAroundRadius" => 8,
                            "margin" => [
                                "top" => 6,
                                "bottom" => 0,
                                "both" => 12
                            ]
                        ]
                    ]
                ]
            );

            // 门店主页
            $data = [
                'site_id' => $param[ 'site_id' ],
                'title' => '门店主页',
                'name' => 'DIY_STORE',
                'is_default' => 1,
                'addon_name' => 'store',
                'type' => 'DIY_STORE',
                'type_name' => '门店主页',
                'value' => $value
            ];
            $res = $diy_view_model->addSiteDiyView($data);
            return $res;
        }

    }

}