var diyMemberInfoHtml = '<div></div>';

Vue.component("diy-member-info-sources", {
    template: diyMemberInfoHtml,
    data: function () {
        return {
            data: this.$parent.data,
            styleList: [
                {text: '样式一', value: 1},
                {text: '样式二', value: 2},
                {text: '样式三', value: 3},
                {text: '样式四', value: 4}
            ]
        };
    },
    created: function () {
        this.$parent.data.ignore = ['componentAngle'];//加载忽略内容 -- 其他设置中的属性设置
        this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

        // 组件所需的临时数据
        this.$parent.data.tempData = {
            styleList: this.styleList,
            baseColor: diyMemberInfoSystemColor.main_color,
            methods: {
                getBgStyle: this.getBgStyle
            },
        };
    },
    watch:{
        "data.style": function(val){
            this.data.theme = "default";
            if(val == 3){
                this.data.theme = "diy";
                this.data.bgColorStart = "#f9e6df";
                this.data.bgColorEnd = "#f6f8f9";
            }
        }
    },
    methods: {
        verify: function (index) {
            var res = {code: true, message: ""};
            return res;
        },
        getBgStyle() {
            let style = {},
                img = "",
                backSize = "contain";
            if (this.data.style == 4) {
                img = ns.img(memberInfoRelativePath + '/img/style_4_bg.png');
                backSize = "cover";
            } else if(this.data.style != 3) {
                img = ns.img('public/static/img/diy_view/member_info_bg.png')
            }
            if (this.data.theme == 'default') {
                style.background = `url('${img}') no-repeat bottom / ${backSize}, linear-gradient(${this.data.gradientAngle}deg, ${diyMemberInfoSystemColor.main_color} 0%, ${diyMemberInfoSystemColor.main_color} 100%)`;
            } else {
                style.background = `url('${img}') no-repeat bottom / ${backSize},linear-gradient(${this.data.gradientAngle}deg, ${this.data.bgColorStart} 0%, ${this.data.bgColorEnd} 100%)`;
            }
            return style;
        }
    }
});