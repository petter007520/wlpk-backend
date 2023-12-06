var horzBlankHtml = '<div></div>';

Vue.component("horz-blank-set", {
    data: function () {
        return {};
    },
    created: function () {
        this.$parent.data.ignore = ['textColor', 'elementBgColor', 'elementAngle'];//加载忽略内容 -- 其他设置中的属性设置
        this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载
    },
    template: horzBlankHtml
});