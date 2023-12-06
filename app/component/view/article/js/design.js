var articleHtml = '<div></div>';

Vue.component("article-sources", {
    template: articleHtml,
    data: function () {
        return {
            data: this.$parent.data,
            goodsSources: {
                initial: {
                    text: "默认",
                    icon: "iconmofang"
                },
                diy: {
                    text: "手动选择",
                    icon: "iconshoudongxuanze"
                },
            },
            ornamentList: [
                {
                    type: 'default',
                    text: '默认',
                },
                {
                    type: 'shadow',
                    text: '投影',
                },
                {
                    type: 'stroke',
                    text: '描边',
                },
            ],
        };
    },
    created: function () {
        this.$parent.data.ignore = [];//加载忽略内容 -- 其他设置中的属性设置
        this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

        if(Object.keys(this.$parent.data.previewList).length == 0) {
            for (var i = 1; i < 3; i++) {
                this.$parent.data.previewList["brand_id_" + ns.gen_non_duplicate(i)] = {
                    article_title: "文章标题",
                    article_abstract: '这里是文章内容',
                    read_num: (i + 1) * 12,
                    category_name: '文章分类',
                    create_time: 1662202804
                };
            }
        }

        // 组件所需的临时数据
        this.$parent.data.tempData = {
            goodsSources: this.goodsSources,
            ornamentList: this.ornamentList,
            methods: {
                addArticle: this.addArticle,
                timeFormat: this.timeFormat
            },
        };
    },
    methods: {
        verify: function (index) {
            var res = {code: true, message: ""};
            if (vue.data[index].sources === 'diy' && vue.data[index].articleIds.length === 0) {
                res.code = false;
                res.message = "请选择文章";
            }
            return res;
        },
        addArticle: function () {
            var self = this;
            articleSelect(function (res) {
                self.$parent.data.articleIds = res.articleIds;
                self.$parent.data.previewList = res.list;
            }, {select_id: self.$parent.data.articleIds.toString()});
        },
        timeFormat(time, format){
            return ns.time_to_date(time, format)
        }
    }
});