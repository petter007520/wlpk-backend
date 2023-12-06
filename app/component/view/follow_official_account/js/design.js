var followOfficialAccountHtml = '<div></div>';

Vue.component("follow-official-account-sources", {
    template: followOfficialAccountHtml,
    data: function () {
        return {
            data: this.$parent.data,
        };
    },
    created: function () {
        if (!this.$parent.data.verify) this.$parent.data.verify = [];
        this.$parent.data.verify.push(this.verify);//加载验证方法

        this.$parent.data.ignore = ['pageBgColor','componentBgColor','textColor','marginTop','marginBottom','marginBoth'];//加载忽略内容 -- 其他设置中的属性设置
        this.$parent.data.ignoreLoad = true; // 等待忽略数组赋值后加载

        // $(".draggable-element .follow-official-account-wrap").css({
        //     top: 86, // 55+20+11
        // });
        //
        // $(".draggable-element .follow-official-account-wrap .edit-attribute").css({
        //     position: 'fixed',
        //     right:15,
        //     top: 55 // layui-header-right 的高度
        // })

    },
    methods: {
        verify: function (index) {
            var res = {code: true, message: ""};
            if (vue.data[index].welcomeMsg.length === 0) {
                res.code = false;
                res.message = "请输入欢迎语";
            }
            return res;
        },
    }
});