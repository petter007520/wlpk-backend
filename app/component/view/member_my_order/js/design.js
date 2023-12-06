var memberMyOrderHtml = '<div></div>';

Vue.component("diy-member-my-order-sources", {
    template: memberMyOrderHtml,
    data: function () {
        return {
            data: this.$parent.data,
            styleList: [
                {text: '样式一', value: 1},
                {text: '样式二', value: 2},
                {text: '样式三', value: 3},
                {text: '样式四', value: 4}
            ],
            iconList: [
                {
                    title: "风格一",
                    data: {
                        "waitPay": {
                            "title": "待付款",
                            "icon": "icondiy icon-system-daifukuan2",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#FF7B1D", "#FF1544"],
                                "iconColorDeg": 0
                            }
                        },
                        "waitSend": {
                            "title": "待发货",
                            "icon": "icondiy icon-system-daifahuo2",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#FF7B1D", "#FF1544"],
                                "iconColorDeg": 0
                            }
                        },
                        "waitConfirm": {
                            "title": "待收货",
                            "icon": "icondiy icon-system-daishouhuo2",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#FF7B1D", "#FF1544"],
                                "iconColorDeg": 0
                            }
                        },
                        "waitUse": {
                            "title": "待使用",
                            "icon": "icondiy icon-system-daishiyong2",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#FF7B1D", "#FF1544"],
                                "iconColorDeg": 0
                            }
                        },
                        "refunding": {
                            "title": "售后",
                            "icon": "icondiy icon-system-shuhou2",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#FF7B1D", "#FF1544"],
                                "iconColorDeg": 0
                            }
                        }
                    }
                },
                {
                    title: "风格二",
                    data: {
                        "waitPay": {
                            "title": "待付款",
                            "icon": "icondiy icon-system-daifukuan",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": [],
                                "iconColorDeg": 0
                            }
                        },
                        "waitSend": {
                            "title": "待发货",
                            "icon": "icondiy icon-system-daifahuo",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": [],
                                "iconColorDeg": 0
                            }
                        },
                        "waitConfirm": {
                            "title": "待收货",
                            "icon": "icondiy icon-system-daishouhuo",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": [],
                                "iconColorDeg": 0
                            }
                        },
                        "waitUse": {
                            "title": "待使用",
                            "icon": "icondiy icon-system-daishiyong",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": [],
                                "iconColorDeg": 0
                            }
                        },
                        "refunding": {
                            "title": "售后",
                            "icon": "icondiy icon-system-shuhou",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": [],
                                "iconColorDeg": 0
                            }
                        }
                    }
                },
                {
                    title: "风格三",
                    data: {
                        "waitPay": {"title": "待付款"},
                        "waitSend": {"title": "待发货"},
                        "waitConfirm": {"title": "待收货"},
                        "waitUse": {"title": "待使用"},
                        "refunding": {"title": "售后"}
                    }
                },
                {
                    title: "风格四",
                    data: {
                        "waitPay": {
                            "title": "待支付",
                            "icon": "icondiy icon-system-daizhifu",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 90,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#20DA86", "#03B352"],
                                "iconColorDeg": 0
                            }
                        },
                        "waitSend": {
                            "title": "备货中",
                            "icon": "icondiy icon-system-beihuozhong",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 90,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#20DA86", "#03B352"],
                                "iconColorDeg": 0
                            }
                        },
                        "waitConfirm": {
                            "title": "配送中",
                            "icon": "icondiy icon-system-paisongzhong",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 90,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#20DA86", "#03B352"],
                                "iconColorDeg": 0
                            }
                        },
                        "waitUse": {
                            "title": "待使用",
                            "icon": "icondiy icon-system-daishiyong2",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 75,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#20DA86", "#03B352"],
                                "iconColorDeg": 0
                            }
                        },
                        "refunding": {
                            "title": "退换货",
                            "icon": "icondiy icon-system-tuihuoguanli",
                            "style": {
                                "bgRadius": 0,
                                "fontSize": 90,
                                "iconBgColor": [],
                                "iconBgColorDeg": 0,
                                "iconBgImg": "",
                                "iconColor": ["#20DA86", "#03B352"],
                                "iconColorDeg": 0
                            }
                        }
                    }
                }
            ]
        };
    },
    created: function () {
        this.$parent.data.ignore = [];//加载忽略内容 -- 其他设置中的属性设置
        this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

        // 组件所需的临时数据
        this.$parent.data.tempData = {
            styleList: this.styleList,
            systemColor: diyMemberMyOrderSystemColor,
            methods: {
                switchIconStyle: this.switchIconStyle,
                handleIconStyle: this.handleIconStyle
            },
        };
    },
    methods: {
        verify: function (index) {
            var res = {code: true, message: ""};
            return res;
        },
        handleIconStyle(data) {
            if (this.data.style == 1 || this.data.style == 4) {
                data.iconColor = [colourBlend(diyMemberMyOrderSystemColor.main_color, '#ffffff', 0.5), diyMemberMyOrderSystemColor.main_color];
            }
            return data;
        },
        switchIconStyle(index) {
            this.$parent.data.style = index + 1;
            this.$parent.data.icon = {};
            var self = this;
            setTimeout(function () {
                self.$parent.data.icon = self.iconList[index].data;
            })
        }
    }
});