(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-goods-list"],{"0d7d":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,"[data-v-3bf1312c] .uni-tag--primary.uni-tag--inverted{background-color:#f5f5f5!important}",""]),t.exports=e},"134b":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,".uni-tag[data-v-43de7aec]{box-sizing:border-box;padding:0 %?32?%;height:%?60?%;line-height:calc(%?60?% - 2px);font-size:%?28?%;display:inline-flex;align-items:center;color:#333;border-radius:%?6?%;background-color:#f8f8f8;border:1px solid #f8f8f8}.uni-tag--circle[data-v-43de7aec]{border-radius:%?30?%}.uni-tag--mark[data-v-43de7aec]{border-radius:0 %?30?% %?30?% 0}.uni-tag--disabled[data-v-43de7aec]{opacity:.5}.uni-tag--small[data-v-43de7aec]{height:%?40?%;padding:0 %?16?%;line-height:calc(%?40?% - 2px);font-size:%?24?%}.uni-tag--primary[data-v-43de7aec]{color:#fff;background-color:#007aff;border:1px solid #007aff}.uni-tag--primary.uni-tag--inverted[data-v-43de7aec]{color:#007aff;background-color:#fff;border:1px solid #007aff}.uni-tag--success[data-v-43de7aec]{color:#fff;background-color:#4cd964;border:1px solid #4cd964}.uni-tag--success.uni-tag--inverted[data-v-43de7aec]{color:#4cd964;background-color:#fff;border:1px solid #4cd964}.uni-tag--warning[data-v-43de7aec]{color:#fff;background-color:#f0ad4e;border:1px solid #f0ad4e}.uni-tag--warning.uni-tag--inverted[data-v-43de7aec]{color:#f0ad4e;background-color:#fff;border:1px solid #f0ad4e}.uni-tag--error[data-v-43de7aec]{color:#fff;background-color:#dd524d;border:1px solid #dd524d}.uni-tag--error.uni-tag--inverted[data-v-43de7aec]{color:#dd524d;background-color:#fff;border:1px solid #dd524d}.uni-tag--inverted[data-v-43de7aec]{color:#333;background-color:#fff;border:1px solid #f8f8f8}",""]),t.exports=e},"16dd":function(t,e,i){"use strict";var a=i("87e61"),o=i.n(a);o.a},"1a00":function(t,e,i){"use strict";i("7a82");var a=i("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var o=a(i("2909"));i("d3b7"),i("159b"),i("c975"),i("4e82"),i("99af"),i("a9e3"),i("acd8"),i("ac1f"),i("c740");var n={data:function(){return{listStyle:"",loadingType:"loading",orderType:"",priceOrder:"desc",categoryList:[],goodsList:[],order:"",sort:"desc",showScreen:!1,keyword:"",categoryId:0,minPrice:"",maxPrice:"",isFreeShipping:!1,isIphoneX:!1,coupon:0,emptyShow:!1,isList:!0,memberId:0,share_title:"",count:0,category_title:"",coupon_name:"",listHeight:[],listPosition:[],debounce:null,brandId:0,brandList:[],config:{add_cart_switch:0}}},onLoad:function(t){var e=this;if(this.categoryId=t.category_id||0,this.keyword=t.keyword||"",this.coupon=t.coupon||0,this.goods_id_arr=t.goods_id_arr||0,this.brandId=t.brand_id||0,this.loadCategoryList(this.categoryId),this.getBrandList(),this.isIphoneX=this.$util.uniappIsIPhoneX(),this.$util.getMemberId().then((function(t){e.memberId=t})),t.source_member&&uni.setStorageSync("source_member",t.source_member),t.scene){var i=decodeURIComponent(t.scene);i=i.split("&"),i.length&&i.forEach((function(t){-1!=t.indexOf("sku_id")&&(e.skuId=t.split("-")[1]),-1!=t.indexOf("m")&&uni.setStorageSync("source_member",t.split("-")[1]),-1!=t.indexOf("is_test")&&uni.setStorageSync("is_test",1)}))}uni.onWindowResize((function(t){e.debounce&&clearTimeout(e.debounce),e.waterfallflow(0)}))},onShow:function(){uni.getStorageSync("token")&&uni.getStorageSync("source_member")&&this.$util.onSourceMember(uni.getStorageSync("source_member")),setTimeout((function(){}),2e3)},onShareAppMessage:function(t){var e="搜索到"+this.count+"件“"+this.keyword+this.category_title+this.coupon_name+"”相关的优质商品",i=this.$util.getCurrentShareRoute(this.memberId),a=i.path;return{title:e,path:a,success:function(t){},fail:function(t){}}},onShareTimeline:function(){var t="搜索到"+this.count+"件“"+this.keyword+this.category_title+this.coupon_name+"”相关的优质商品",e=this.$util.getCurrentShareRoute(this.memberId),i=e.query;return{title:t,query:i,imageUrl:""}},methods:{couponInfo:function(t){var e=this;return new Promise((function(i){e.$api.sendRequest({url:"/coupon/api/coupon/typeinfo",data:{coupon_type_id:t},success:function(t){t.code>=0&&i(t.data.coupon_name)}})}))},share_select:function(t,e){return new Promise((function(i){t.forEach((function(t){t.category_id==e&&i(t.category_name),t.child_list&&t.child_list.length>0&&t.child_list.forEach((function(t){t.category_id==e&&i(t.category_name),t.child_list&&t.child_list.length>0&&t.forEach((function(t){t.category_id==e&&i(t.category_name)}))}))}))}))},loadCategoryList:function(t,e){var i=this;this.$api.sendRequest({url:"/api/goodscategory/tree",data:{},success:function(t){null!=t.data&&(i.categoryList=t.data)}})},getGoodsList:function(t){var e=this;this.$api.sendRequest({url:"/api/goodssku/page",data:{page:t.num,page_size:t.size,keyword:this.keyword,category_id:this.categoryId,brand_id:this.brandId,min_price:this.minPrice,max_price:this.maxPrice,is_free_shipping:this.isFreeShipping?1:0,order:this.order,sort:this.sort,coupon:this.coupon,goods_id_arr:this.goods_id_arr},success:function(i){var a=[],o=i.message;0==i.code&&i.data?(e.count=i.data.count,0==i.data.page_count&&(e.emptyShow=!0),a=i.data.list):e.$util.showToast({title:o}),e.category_title="",e.coupon_name="",e.config=i.data.config,e.categoryId&&e.share_select(e.categoryList,e.categoryId).then((function(t){e.category_title=t})),e.coupon&&e.couponInfo(e.coupon).then((function(t){e.coupon_name=t})),t.endSuccess(a.length),1==t.num&&(e.goodsList=[]),e.goodsList=e.goodsList.concat(a),e.$refs.loadingCover&&e.$refs.loadingCover.hide(),e.waterfallflow(10*(t.num-1))},fail:function(i){t.endErr(),e.$refs.loadingCover&&e.$refs.loadingCover.hide()}})},changeListStyle:function(){this.isList=!this.isList,this.waterfallflow(0)},sortTabClick:function(t){if("sale_num"==t)this.order="sale_num",this.sort="desc";else if("discount_price"==t)this.order="discount_price",this.sort="desc";else{if("screen"==t)return void(this.showScreen=!0);this.order="",this.sort=""}this.orderType===t&&"discount_price"!==t||(this.orderType=t,"discount_price"===t?(this.priceOrder="asc"===this.priceOrder?"desc":"asc",this.sort=this.priceOrder):this.priceOrder="",this.emptyShow=!1,this.goodsList=[],this.$refs.mescroll.refresh())},toDetail:function(t){this.$util.redirectTo("/pages/goods/detail",{goods_id:t.goods_id})},search:function(){this.emptyShow=!1,this.goodsList=[],this.$refs.mescroll.refresh()},selectedCategory:function(t){this.categoryId=t},screenData:function(){if(""!=this.minPrice||""!=this.maxPrice){if(!Number(this.maxPrice)&&this.maxPrice)return void this.$util.showToast({title:"请输入最高价"});if(Number(this.minPrice)<0||Number(this.maxPrice)<0)return void this.$util.showToast({title:"筛选价格不能小于0"});if(""!=this.minPrice&&Number(this.minPrice)>Number(this.maxPrice)&&this.maxPrice)return void this.$util.showToast({title:"最低价不能大于最高价"});if(""!=this.maxPrice&&Number(this.maxPrice)<Number(this.minPrice))return void this.$util.showToast({title:"最高价不能小于最低价"})}this.emptyShow=!1,this.goodsList=[],this.$refs.mescroll.refresh(),this.showScreen=!1},resetData:function(){this.categoryId=0,this.minPrice="",this.maxPrice="",this.isFreeShipping=!1},goodsImg:function(t){var e=t.split(",");return e[0]?this.$util.img(e[0],{size:"mid"}):this.$util.getDefaultImage().goods},imgError:function(t){this.goodsList[t].goods_image=this.$util.getDefaultImage().goods},showPrice:function(t){var e=t.discount_price;return t.member_price&&parseFloat(t.member_price)<parseFloat(e)&&(e=t.member_price),e},showMarketPrice:function(t){if(t.market_price_show){var e=this.showPrice(t);if(t.market_price>0)return t.market_price;if(parseFloat(t.price)>parseFloat(e))return t.price}return""},goodsTag:function(t){return t.label_name||""},waterfallflow:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0;this.isList||this.$nextTick((function(){setTimeout((function(){var i=[],a=[];0!=e&&(i=t.listHeight,a=t.listPosition);var n=uni.createSelectorQuery().in(t);n.selectAll(".double-column .goods-item").boundingClientRect((function(n){for(var s=e;s<n.length;s++)if(s<2){var r={};r.top=uni.upx2px(20)+"px",r.left=s%2==0?n[s].width*s+"px":n[s].width*s+s%2*uni.upx2px(30)+"px",a[s]=r,i[s]=n[s].height+uni.upx2px(20)}else(function(){var t=Math.min.apply(Math,(0,o.default)(i)),e=i.findIndex((function(e){return e===t})),r={};r.top=t+uni.upx2px(20)+"px",r.left=a[e].left,a[s]=r,i[e]+=n[s].height+uni.upx2px(20)})();t.listHeight=i,t.listPosition=a})).exec()}),50)}))},getBrandList:function(){var t=this;this.$api.sendRequest({url:"/api/goodsbrand/page",data:{page:1,page_size:0},success:function(e){if(0==e.code&&e.data){var i=e.data;t.brandList=i.list}}})},cartListChange:function(t){},addCart:function(t){}}};e.default=n},"1ab9":function(t,e,i){"use strict";var a=i("8ed3"),o=i.n(a);o.a},"1ff9":function(t,e,i){var a=i("a0a8");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=i("4f06").default;o("44beba48",a,!0,{sourceMap:!1,shadowMode:!1})},2909:function(t,e,i){"use strict";i("7a82"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=function(t){return(0,a.default)(t)||(0,o.default)(t)||(0,n.default)(t)||(0,s.default)()};var a=r(i("6005")),o=r(i("db90")),n=r(i("06c5")),s=r(i("3427"));function r(t){return t&&t.__esModule?t:{default:t}}},3427:function(t,e,i){"use strict";i("7a82"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")},i("d9e2"),i("d401")},"4a04":function(t,e,i){"use strict";i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.text?i("v-uni-view",{staticClass:"uni-tag",class:[!0===t.disabled||"true"===t.disabled?"uni-tag--disabled":"",!0===t.inverted||"true"===t.inverted?"uni-tag--inverted":"",!0===t.circle||"true"===t.circle?"uni-tag--circle":"",!0===t.mark||"true"===t.mark?"uni-tag--mark":"","uni-tag--"+t.size,"uni-tag--"+t.type],on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.onClick()}}},[t._v(t._s(t.text))]):t._e()},o=[]},5402:function(t,e,i){"use strict";var a=i("1ff9"),o=i.n(a);o.a},"54eb":function(t,e,i){"use strict";i.r(e);var a=i("f749"),o=i.n(a);for(var n in a)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(n);e["default"]=o.a},"5e2f":function(t,e,i){"use strict";i.r(e);var a=i("6503"),o=i("54eb");for(var n in o)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return o[t]}))}(n);i("5402"),i("16dd");var s=i("f0c5"),r=Object(s["a"])(o["default"],a["b"],a["c"],!1,null,"3bf1312c",null,!1,a["a"],void 0);e["default"]=r.exports},"5fb1":function(t,e,i){"use strict";var a=i("6010"),o=i.n(a);o.a},6005:function(t,e,i){"use strict";i("7a82"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=function(t){if(Array.isArray(t))return(0,a.default)(t)};var a=function(t){return t&&t.__esModule?t:{default:t}}(i("6b75"))},6010:function(t,e,i){var a=i("134b");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=i("4f06").default;o("f58d02c6",a,!0,{sourceMap:!1,shadowMode:!1})},6503:function(t,e,i){"use strict";i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return n})),i.d(e,"a",(function(){return a}));var a={pageMeta:i("6d42").default,nsEmpty:i("7441").default,uniDrawer:i("8bd1").default,uniTag:i("ced7").default,loadingCover:i("cfb1").default},o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",[i("page-meta",{attrs:{"page-style":t.themeColor}}),i("v-uni-view",{staticClass:"content"},[i("v-uni-view",{staticClass:"head-wrap"},[i("v-uni-view",{staticClass:"search-wrap uni-flex uni-row"},[i("v-uni-view",{staticClass:"flex-item input-wrap"},[i("v-uni-input",{staticClass:"uni-input",attrs:{maxlength:"50",placeholder:"请输入您要搜索的商品"},on:{confirm:function(e){arguments[0]=e=t.$handleEvent(e),t.search()}},model:{value:t.keyword,callback:function(e){t.keyword=e},expression:"keyword"}}),i("v-uni-text",{staticClass:"iconfont icon-sousuo3",on:{click:function(e){e.stopPropagation(),arguments[0]=e=t.$handleEvent(e),t.search()}}})],1),i("v-uni-view",{staticClass:"iconfont",class:{"icon-apps":t.isList,"icon-list":!t.isList},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.changeListStyle()}}})],1),i("v-uni-view",{staticClass:"sort-wrap"},[i("v-uni-view",{staticClass:"comprehensive-wrap",class:{"color-base-text":""===t.orderType},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.sortTabClick("")}}},[i("v-uni-text",{class:{"color-base-text":""===t.orderType}},[t._v("综合")])],1),i("v-uni-view",{class:{"color-base-text":"sale_num"===t.orderType},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.sortTabClick("sale_num")}}},[t._v("销量")]),i("v-uni-view",{staticClass:"price-wrap",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.sortTabClick("discount_price")}}},[i("v-uni-text",{class:{"color-base-text":"discount_price"===t.orderType}},[t._v("价格")]),i("v-uni-view",{staticClass:"iconfont-wrap"},[i("v-uni-view",{staticClass:"iconfont icon-iconangledown-copy asc",class:{"color-base-text":"asc"===t.priceOrder&&"discount_price"===t.orderType}}),i("v-uni-view",{staticClass:"iconfont icon-iconangledown desc",class:{"color-base-text":"desc"===t.priceOrder&&"discount_price"===t.orderType}})],1)],1),i("v-uni-view",{staticClass:"screen-wrap",class:{"color-base-text":"screen"===t.orderType}},[i("v-uni-text",{on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.sortTabClick("screen")}}},[t._v("筛选")]),i("v-uni-view",{staticClass:"iconfont-wrap",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.sortTabClick("screen")}}},[i("v-uni-view",{staticClass:"iconfont icon-shaixuan color-tip"})],1)],1)],1)],1),i("mescroll-uni",{ref:"mescroll",attrs:{top:"180"},on:{getData:function(e){arguments[0]=e=t.$handleEvent(e),t.getGoodsList.apply(void 0,arguments)}}},[i("template",{attrs:{slot:"list"},slot:"list"},[i("v-uni-view",{staticClass:"goods-list single-column",class:{show:t.isList}},t._l(t.goodsList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"goods-item margin-bottom",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toDetail(e)}}},[i("v-uni-view",{staticClass:"goods-img"},[i("v-uni-image",{attrs:{src:t.goodsImg(e.goods_image),mode:"widthFix"},on:{error:function(e){arguments[0]=e=t.$handleEvent(e),t.imgError(a)}}}),""!=t.goodsTag(e)?i("v-uni-view",{staticClass:"color-base-bg goods-tag"},[t._v(t._s(t.goodsTag(e)))]):t._e()],1),i("v-uni-view",{staticClass:"info-wrap"},[i("v-uni-view",{staticClass:"name-wrap"},[i("v-uni-view",{staticClass:"goods-name"},[t._v(t._s(e.goods_name))])],1),i("v-uni-view",{staticClass:"lineheight-clear"},[i("v-uni-view",{staticClass:"discount-price"},[i("v-uni-text",{staticClass:"unit price-style small"},[t._v(t._s(t.$lang("common.currencySymbol")))]),i("v-uni-text",{staticClass:"price price-style large"},[t._v(t._s(parseFloat(t.showPrice(e)).toFixed(2).split(".")[0]))]),i("v-uni-text",{staticClass:"unit price-style small"},[t._v("."+t._s(parseFloat(t.showPrice(e)).toFixed(2).split(".")[1]))])],1),e.member_price&&e.member_price==t.showPrice(e)?i("v-uni-view",{staticClass:"member-price-tag"},[i("v-uni-image",{attrs:{src:t.$util.img("public/uniapp/index/VIP.png"),mode:"widthFix"}})],1):1==e.promotion_type?i("v-uni-view",{staticClass:"member-price-tag"},[i("v-uni-image",{attrs:{src:t.$util.img("public/uniapp/index/discount.png"),mode:"widthFix"}})],1):t._e()],1),i("v-uni-view",{staticClass:"pro-info"},[t.showMarketPrice(e)?i("v-uni-view",{staticClass:"delete-price color-tip price-font"},[i("v-uni-text",{staticClass:"unit"},[t._v(t._s(t.$lang("common.currencySymbol")))]),i("v-uni-text",[t._v(t._s(t.showMarketPrice(e)))])],1):t._e(),e.sale_show?i("v-uni-view",{staticClass:"sale color-tip"},[t._v("已售"+t._s(e.sale_num)+t._s(e.unit?e.unit:"件"))]):t._e(),1==t.config.add_cart_switch?i("v-uni-view",{staticClass:"cart-buy-btn",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.$refs.goodsSkuIndex.addCart("cart",e,i)}}},[t._v("购买")]):t._e()],1)],1)],1)})),1),i("v-uni-view",{staticClass:"goods-list double-column",class:{show:!t.isList}},t._l(t.goodsList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"goods-item margin-bottom",style:{left:t.listPosition[a]?t.listPosition[a].left:"",top:t.listPosition[a]?t.listPosition[a].top:""},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.toDetail(e)}}},[i("v-uni-view",{staticClass:"goods-img"},[i("v-uni-image",{attrs:{src:t.goodsImg(e.goods_image),mode:"widthFix"},on:{error:function(e){arguments[0]=e=t.$handleEvent(e),t.imgError(a)}}}),""!=t.goodsTag(e)?i("v-uni-view",{staticClass:"color-base-bg goods-tag"},[t._v(t._s(t.goodsTag(e)))]):t._e()],1),i("v-uni-view",{staticClass:"info-wrap"},[i("v-uni-view",{staticClass:"name-wrap"},[i("v-uni-view",{staticClass:"goods-name"},[t._v(t._s(e.goods_name))])],1),i("v-uni-view",{staticClass:"lineheight-clear"},[i("v-uni-view",{staticClass:"discount-price"},[i("v-uni-text",{staticClass:"unit price-style small"},[t._v(t._s(t.$lang("common.currencySymbol")))]),i("v-uni-text",{staticClass:"price price-style large"},[t._v(t._s(parseFloat(t.showPrice(e)).toFixed(2).split(".")[0]))]),i("v-uni-text",{staticClass:"unit price-style small"},[t._v("."+t._s(parseFloat(t.showPrice(e)).toFixed(2).split(".")[1]))])],1),e.member_price&&e.member_price==t.showPrice(e)?i("v-uni-view",{staticClass:"member-price-tag"},[i("v-uni-image",{attrs:{src:t.$util.img("public/uniapp/index/VIP.png"),mode:"widthFix"}})],1):1==e.promotion_type?i("v-uni-view",{staticClass:"member-price-tag"},[i("v-uni-image",{attrs:{src:t.$util.img("public/uniapp/index/discount.png"),mode:"widthFix"}})],1):t._e(),t.showMarketPrice(e)?i("v-uni-view",{staticClass:"delete-price color-tip price-font"},[i("v-uni-text",{staticClass:"unit"},[t._v(t._s(t.$lang("common.currencySymbol")))]),i("v-uni-text",[t._v(t._s(t.showMarketPrice(e)))])],1):t._e()],1),i("v-uni-view",{staticClass:"pro-info"},[e.sale_show?i("v-uni-view",{staticClass:"sale color-tip"},[t._v("已售"+t._s(e.sale_num)+t._s(e.unit?e.unit:"件"))]):t._e(),1==t.config.add_cart_switch?i("v-uni-view",{staticClass:"cart-buy-btn",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.$refs.goodsSkuIndex.addCart("cart",e,i)}}},[t._v("购买")]):t._e()],1)],1)],1)})),1),0==t.goodsList.length&&t.emptyShow?i("v-uni-view",[i("ns-empty",{attrs:{text:"暂无商品"}})],1):t._e()],1)],2),i("ns-goods-sku-index",{ref:"goodsSkuIndex",on:{cartListChange:function(e){arguments[0]=e=t.$handleEvent(e),t.cartListChange.apply(void 0,arguments)},addCart:function(e){arguments[0]=e=t.$handleEvent(e),t.addCart.apply(void 0,arguments)}}}),i("uni-drawer",{staticClass:"screen-wrap",attrs:{visible:t.showScreen,mode:"right"},on:{close:function(e){arguments[0]=e=t.$handleEvent(e),t.showScreen=!1}}},[i("v-uni-view",{staticClass:"title color-tip"},[t._v("筛选")]),i("v-uni-scroll-view",{attrs:{"scroll-y":!0}},[i("v-uni-view",{staticClass:"item-wrap"},[i("v-uni-view",{staticClass:"label"},[i("v-uni-text",[t._v("是否包邮")])],1),i("v-uni-view",{staticClass:"list"},[i("uni-tag",{attrs:{inverted:!0,text:"包邮",type:t.isFreeShipping?"primary":"default"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.isFreeShipping=!t.isFreeShipping}}})],1)],1),i("v-uni-view",{staticClass:"item-wrap"},[i("v-uni-view",{staticClass:"label"},[i("v-uni-text",[t._v("价格区间(元)")])],1),i("v-uni-view",{staticClass:"price-wrap"},[i("v-uni-input",{staticClass:"uni-input",attrs:{type:"digit",placeholder:"最低价"},model:{value:t.minPrice,callback:function(e){t.minPrice=e},expression:"minPrice"}}),i("v-uni-view",{staticClass:"h-line"}),i("v-uni-input",{staticClass:"uni-input",attrs:{type:"digit",placeholder:"最高价"},model:{value:t.maxPrice,callback:function(e){t.maxPrice=e},expression:"maxPrice"}})],1)],1),t.brandList.length>0?i("v-uni-view",{staticClass:"item-wrap"},[i("v-uni-view",{staticClass:"label"},[i("v-uni-text",[t._v("品牌")])],1),i("v-uni-view",{staticClass:"list"},t._l(t.brandList,(function(e,a){return i("v-uni-view",{key:a},[i("uni-tag",{attrs:{inverted:!0,text:e.brand_name,type:e.brand_id==t.brandId?"primary":"default"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.brandId==e.brand_id?t.brandId=0:t.brandId=e.brand_id}}})],1)})),1)],1):t._e(),i("v-uni-view",{staticClass:"category-list-wrap"},[i("v-uni-text",{staticClass:"first"},[t._v("全部分类")]),i("v-uni-view",{staticClass:"class-box"},[i("v-uni-view",{staticClass:"list-wrap",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.selectedCategory("")}}},[i("v-uni-text",{class:{selected:!t.categoryId,"color-base-text":!t.categoryId}},[t._v("全部")])],1),t._l(t.categoryList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"list-wrap",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.selectedCategory(e.category_id)}}},[i("v-uni-text",{class:{selected:e.category_id==t.categoryId,"color-base-text":e.category_id==t.categoryId}},[t._v(t._s(e.category_name))])],1)}))],2)],1)],1),i("v-uni-view",{staticClass:"footer",class:{"safe-area":t.isIphoneX}},[i("v-uni-button",{staticClass:"footer-box",attrs:{type:"default"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.resetData.apply(void 0,arguments)}}},[t._v("重置")]),i("v-uni-button",{staticClass:"footer-box1",attrs:{type:"primary"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.screenData.apply(void 0,arguments)}}},[t._v("确定")])],1)],1),i("loading-cover",{ref:"loadingCover"})],1)],1)},n=[]},"87e61":function(t,e,i){var a=i("0d7d");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=i("4f06").default;o("5a823420",a,!0,{sourceMap:!1,shadowMode:!1})},"8bd1":function(t,e,i){"use strict";i.r(e);var a=i("a039e"),o=i("95234");for(var n in o)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return o[t]}))}(n);i("1ab9");var s=i("f0c5"),r=Object(s["a"])(o["default"],a["b"],a["c"],!1,null,"dcc4de3c",null,!1,a["a"],void 0);e["default"]=r.exports},"8ed3":function(t,e,i){var a=i("fed2");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=i("4f06").default;o("47d6f166",a,!0,{sourceMap:!1,shadowMode:!1})},95234:function(t,e,i){"use strict";i.r(e);var a=i("e007"),o=i.n(a);for(var n in a)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(n);e["default"]=o.a},a039e:function(t,e,i){"use strict";i.d(e,"b",(function(){return a})),i.d(e,"c",(function(){return o})),i.d(e,"a",(function(){}));var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.visibleSync?i("v-uni-view",{staticClass:"uni-drawer",class:{"uni-drawer--visible":t.showDrawer,"uni-drawer--right":t.rightMode},on:{touchmove:function(e){e.stopPropagation(),e.preventDefault(),arguments[0]=e=t.$handleEvent(e),t.moveHandle.apply(void 0,arguments)}}},[i("v-uni-view",{staticClass:"uni-drawer__mask",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.close.apply(void 0,arguments)}}}),i("v-uni-view",{staticClass:"uni-drawer__content",class:{"safe-area":t.isIphoneX}},[t._t("default")],2)],1):t._e()},o=[]},a0a8:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n * 建议使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n */.head-wrap[data-v-3bf1312c]{background:#fff;position:fixed;width:100%;left:0;z-index:1}.head-wrap .search-wrap[data-v-3bf1312c]{flex:0.5;padding:%?30?% %?30?% 0;font-size:%?24?%;display:flex;align-items:center}.head-wrap .search-wrap .iconfont[data-v-3bf1312c]{margin-left:%?16?%;font-size:%?36?%}.head-wrap .search-wrap .input-wrap[data-v-3bf1312c]{flex:1;display:flex;justify-content:space-between;align-items:center;background:#f8f8f8;height:%?64?%;padding-left:%?10?%;border-radius:%?70?%}.head-wrap .search-wrap .input-wrap uni-input[data-v-3bf1312c]{width:90%;background:#f8f8f8;font-size:%?24?%;height:100%;padding:0 %?25?% 0 %?40?%;line-height:%?50?%;border-radius:%?40?%}.head-wrap .search-wrap .input-wrap uni-text[data-v-3bf1312c]{font-size:%?32?%;color:#909399;width:%?80?%;text-align:center;margin-right:%?20?%}.head-wrap .search-wrap .category-wrap[data-v-3bf1312c],\r\n.head-wrap .search-wrap .list-style[data-v-3bf1312c]{display:flex;justify-content:center;align-items:center}.head-wrap .search-wrap .category-wrap .iconfont[data-v-3bf1312c],\r\n.head-wrap .search-wrap .list-style .iconfont[data-v-3bf1312c]{font-size:%?50?%;color:#909399}.head-wrap .search-wrap .category-wrap uni-text[data-v-3bf1312c],\r\n.head-wrap .search-wrap .list-style uni-text[data-v-3bf1312c]{display:block;margin-top:%?60?%}.head-wrap .sort-wrap[data-v-3bf1312c]{display:flex;padding:%?10?% %?20?% %?10?% 0}.head-wrap .sort-wrap > uni-view[data-v-3bf1312c]{flex:1;text-align:center;font-size:%?28?%;height:%?60?%;line-height:%?60?%;font-weight:700}.head-wrap .sort-wrap .comprehensive-wrap[data-v-3bf1312c]{display:flex;justify-content:center;align-items:center}.head-wrap .sort-wrap .comprehensive-wrap .iconfont-wrap[data-v-3bf1312c]{display:inline-block;margin-left:%?10?%;width:%?40?%}.head-wrap .sort-wrap .comprehensive-wrap .iconfont-wrap .iconfont[data-v-3bf1312c]{font-size:%?32?%;line-height:1;margin-bottom:%?5?%}.head-wrap .sort-wrap .price-wrap[data-v-3bf1312c]{display:flex;justify-content:center;align-items:center}.head-wrap .sort-wrap .price-wrap .iconfont-wrap[data-v-3bf1312c]{display:flex;justify-content:center;align-items:center;flex-direction:column;width:%?40?%}.head-wrap .sort-wrap .price-wrap .iconfont-wrap .iconfont[data-v-3bf1312c]{position:relative;float:left;font-size:%?32?%;line-height:1;height:%?20?%;color:#909399}.head-wrap .sort-wrap .price-wrap .iconfont-wrap .iconfont.asc[data-v-3bf1312c]{top:%?-2?%}.head-wrap .sort-wrap .price-wrap .iconfont-wrap .iconfont.desc[data-v-3bf1312c]{top:%?-6?%}.head-wrap .sort-wrap .screen-wrap[data-v-3bf1312c]{display:flex;justify-content:center;align-items:center}.head-wrap .sort-wrap .screen-wrap .iconfont-wrap[data-v-3bf1312c]{display:inline-block;margin-left:%?10?%;width:%?40?%}.head-wrap .sort-wrap .screen-wrap .iconfont-wrap .iconfont[data-v-3bf1312c]{font-size:%?32?%;line-height:1}.category-list-wrap[data-v-3bf1312c]{height:100%}.category-list-wrap .class-box[data-v-3bf1312c]{display:flex;flex-wrap:wrap;padding:0 %?20?%}.category-list-wrap .class-box uni-view[data-v-3bf1312c]{width:calc((100% - %?60?%) / 3);font-size:%?22?%;margin-right:%?20?%;height:%?60?%;line-height:%?60?%;text-align:center;margin-bottom:%?12?%;flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;background:#f5f5f5;border-radius:%?5?%}.category-list-wrap .class-box uni-view[data-v-3bf1312c]:nth-of-type(3n){margin-right:0}.category-list-wrap .first[data-v-3bf1312c]{font-size:%?24?%;display:block;padding:%?20?%}.category-list-wrap .second[data-v-3bf1312c]{border-bottom:%?2?% solid #eee;padding:%?20?%;display:block;font-size:%?24?%}.category-list-wrap .third[data-v-3bf1312c]{padding:0 %?20?% %?20?%;overflow:hidden;font-size:%?24?%}.category-list-wrap .third > uni-view[data-v-3bf1312c]{display:inline-block;margin-right:%?20?%;margin-top:%?20?%}.category-list-wrap .third .uni-tag[data-v-3bf1312c]{padding:0 %?20?%}.screen-wrap .title[data-v-3bf1312c]{font-size:%?24?%;padding:%?20?%;background:#f6f4f5}.screen-wrap uni-scroll-view[data-v-3bf1312c]{height:85%}.screen-wrap uni-scroll-view .item-wrap[data-v-3bf1312c]{border-bottom:1px solid #f0f0f0}.screen-wrap uni-scroll-view .item-wrap .label[data-v-3bf1312c]{font-size:%?24?%;padding:%?20?%}.screen-wrap uni-scroll-view .item-wrap .label uni-view[data-v-3bf1312c]{display:inline-block;font-size:%?60?%;height:%?40?%;vertical-align:middle;line-height:%?40?%}.screen-wrap uni-scroll-view .item-wrap .list[data-v-3bf1312c]{margin:%?20?% %?30?%;overflow:hidden}.screen-wrap uni-scroll-view .item-wrap .list > uni-view[data-v-3bf1312c]{display:inline-block;margin-right:%?25?%;margin-bottom:%?25?%}.screen-wrap uni-scroll-view .item-wrap .list .uni-tag[data-v-3bf1312c]{padding:0 %?20?%;font-size:%?22?%;background:#f5f5f5;height:%?52?%;line-height:%?52?%;border:0}.screen-wrap uni-scroll-view .item-wrap .price-wrap[data-v-3bf1312c]{display:flex;justify-content:center;align-items:center;padding:%?20?%}.screen-wrap uni-scroll-view .item-wrap .price-wrap uni-input[data-v-3bf1312c]{flex:1;background:#f5f5f5;height:%?52?%;width:%?182?%;line-height:%?50?%;font-size:%?22?%;border-radius:%?50?%;text-align:center}.screen-wrap uni-scroll-view .item-wrap .price-wrap uni-input[data-v-3bf1312c]:first-child{margin-right:%?10?%}.screen-wrap uni-scroll-view .item-wrap .price-wrap uni-input[data-v-3bf1312c]:last-child{margin-left:%?10?%}.screen-wrap .footer[data-v-3bf1312c]{height:%?90?%;display:flex;justify-content:center;align-items:flex-start;display:flex;bottom:0;width:100%}.screen-wrap .footer .footer-box[data-v-3bf1312c]{border-top-right-radius:0;border-bottom-right-radius:0;margin:0;width:40%}.screen-wrap .footer .footer-box1[data-v-3bf1312c]{border-top-left-radius:0;border-bottom-left-radius:0;margin:0;width:40%}.safe-area[data-v-3bf1312c]{bottom:%?68?%!important}.empty[data-v-3bf1312c]{margin-top:%?100?%}.buy-num[data-v-3bf1312c]{font-size:%?20?%}.icon[data-v-3bf1312c]{width:%?34?%;height:%?30?%}.list-style-new[data-v-3bf1312c]{display:flex;align-items:center}.list-style-new .line[data-v-3bf1312c]{width:%?4?%;height:%?28?%;background-color:#e3e3e3;margin-right:%?60?%}.h-line[data-v-3bf1312c]{width:%?37?%;height:%?2?%;background-color:#909399}.goods-list.single-column[data-v-3bf1312c]{display:none}.goods-list.single-column.show[data-v-3bf1312c]{display:block}.goods-list.single-column .goods-item[data-v-3bf1312c]{padding:%?26?%;background:#fff;margin:%?20?% %?30?%;border-radius:%?10?%;display:flex;position:relative}.goods-list.single-column .goods-item .goods-img[data-v-3bf1312c]{width:%?200?%;height:%?200?%;border-radius:%?10?%;margin-right:%?20?%;overflow:hidden}.goods-list.single-column .goods-item .goods-img uni-image[data-v-3bf1312c]{width:%?200?%;height:%?200?%}.goods-list.single-column .goods-item .goods-tag[data-v-3bf1312c]{color:#fff;line-height:1;padding:%?8?% %?12?%;position:absolute;border-top-left-radius:%?10?%;border-bottom-right-radius:%?10?%;top:%?26?%;left:%?26?%;font-size:%?22?%}.goods-list.single-column .goods-item .goods-tag-img[data-v-3bf1312c]{position:absolute;border-top-left-radius:%?10?%;width:%?80?%;height:%?80?%;top:%?26?%;left:%?26?%;z-index:5;overflow:hidden}.goods-list.single-column .goods-item .goods-tag-img uni-image[data-v-3bf1312c]{width:100%;height:100%}.goods-list.single-column .goods-item .info-wrap[data-v-3bf1312c]{flex:1;display:flex;flex-direction:column}.goods-list.single-column .goods-item .name-wrap[data-v-3bf1312c]{flex:1}.goods-list.single-column .goods-item .goods-name[data-v-3bf1312c]{font-size:%?28?%;line-height:1.3;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}.goods-list.single-column .goods-item .introduction[data-v-3bf1312c]{line-height:1;margin-top:%?10?%}.goods-list.single-column .goods-item .discount-price[data-v-3bf1312c]{display:inline-block;font-weight:700;line-height:1;margin-top:%?16?%}.goods-list.single-column .goods-item .discount-price .unit[data-v-3bf1312c]{margin-right:%?6?%;color:var(--price-color)}.goods-list.single-column .goods-item .discount-price .price[data-v-3bf1312c]{color:var(--price-color)}.goods-list.single-column .goods-item .pro-info[data-v-3bf1312c]{display:flex;margin-top:auto;align-items:center}.goods-list.single-column .goods-item .pro-info .delete-price[data-v-3bf1312c]{text-decoration:line-through;flex:1}.goods-list.single-column .goods-item .pro-info .delete-price .unit[data-v-3bf1312c]{margin-right:%?0?%}.goods-list.single-column .goods-item .pro-info .sale[data-v-3bf1312c]{flex:1}.goods-list.single-column .goods-item .pro-info > uni-view[data-v-3bf1312c]{line-height:1;font-size:%?24?%!important}.goods-list.single-column .goods-item .pro-info > uni-view[data-v-3bf1312c]:nth-child(2){text-align:right;margin-right:%?20?%}.goods-list.single-column .goods-item .pro-info .cart-buy-btn[data-v-3bf1312c]{display:inline-block;text-align:center;box-sizing:border-box;color:#fff;background-color:var(--base-color);border-radius:%?50?%;font-size:%?24?%;padding:%?12?% %?30?%;line-height:1}.goods-list.single-column .goods-item .member-price-tag[data-v-3bf1312c]{display:inline-block;width:%?60?%;line-height:1;margin-left:%?6?%}.goods-list.single-column .goods-item .member-price-tag uni-image[data-v-3bf1312c]{width:100%;display:flex;max-height:%?30?%}.goods-list.double-column[data-v-3bf1312c]{display:none;margin:0 %?30?%;padding-top:%?20?%;position:relative;flex-wrap:wrap;justify-content:space-between}.goods-list.double-column.show[data-v-3bf1312c]{display:flex}.goods-list.double-column .goods-item[data-v-3bf1312c]{background-color:#fff;width:calc(50% - %?10?%);border-radius:%?10?%;overflow:hidden}.goods-list.double-column .goods-item[data-v-3bf1312c]:nth-child(2n + 2){margin-right:0}.goods-list.double-column .goods-item .goods-img[data-v-3bf1312c]{position:relative;overflow:hidden;padding-top:100%;border-top-left-radius:%?10?%;border-top-right-radius:%?10?%}.goods-list.double-column .goods-item .goods-img uni-image[data-v-3bf1312c]{width:100%;position:absolute!important;top:50%;left:0;-webkit-transform:translateY(-50%);transform:translateY(-50%)}.goods-list.double-column .goods-item .goods-tag[data-v-3bf1312c]{color:#fff;line-height:1;padding:%?8?% %?16?%;position:absolute;border-bottom-right-radius:%?10?%;top:0;left:0;font-size:%?22?%}.goods-list.double-column .goods-item .goods-tag-img[data-v-3bf1312c]{position:absolute;border-top-left-radius:%?10?%;width:%?80?%;height:%?80?%;top:0;left:0;z-index:5;overflow:hidden}.goods-list.double-column .goods-item .goods-tag-img uni-image[data-v-3bf1312c]{width:100%;height:100%}.goods-list.double-column .goods-item .info-wrap[data-v-3bf1312c]{padding:0 %?26?% %?26?% %?26?%}.goods-list.double-column .goods-item .goods-name[data-v-3bf1312c]{font-size:%?28?%;line-height:1.3;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-top:%?20?%}.goods-list.double-column .goods-item .lineheight-clear[data-v-3bf1312c]{margin-top:%?16?%}.goods-list.double-column .goods-item .discount-price[data-v-3bf1312c]{display:inline-block;font-weight:700;line-height:1}.goods-list.double-column .goods-item .discount-price .unit[data-v-3bf1312c]{margin-right:%?6?%;color:var(--price-color)}.goods-list.double-column .goods-item .discount-price .price[data-v-3bf1312c]{color:var(--price-color)}.goods-list.double-column .goods-item .pro-info[data-v-3bf1312c]{display:flex;margin-top:auto;align-items:center}.goods-list.double-column .goods-item .pro-info .sale[data-v-3bf1312c]{flex:1}.goods-list.double-column .goods-item .pro-info > uni-view[data-v-3bf1312c]{line-height:1;font-size:%?24?%!important}.goods-list.double-column .goods-item .pro-info > uni-view[data-v-3bf1312c]:nth-child(2){text-align:right}.goods-list.double-column .goods-item .pro-info .sale[data-v-3bf1312c]{margin-right:%?20?%}.goods-list.double-column .goods-item .pro-info .cart-buy-btn[data-v-3bf1312c]{display:inline-block;text-align:center;box-sizing:border-box;color:#fff;background-color:var(--base-color);border-radius:%?50?%;font-size:%?24?%;padding:%?12?% %?30?%;line-height:1}.goods-list.double-column .goods-item .delete-price .unit[data-v-3bf1312c]{margin-right:%?6?%}.goods-list.double-column .goods-item .delete-price uni-text[data-v-3bf1312c]{line-height:1;font-size:%?24?%!important}.goods-list.double-column .goods-item .member-price-tag[data-v-3bf1312c]{display:inline-block;width:%?60?%;line-height:1;margin-left:%?6?%}.goods-list.double-column .goods-item .member-price-tag uni-image[data-v-3bf1312c]{width:100%}',""]),t.exports=e},ae8a:function(t,e,i){"use strict";i("7a82"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"UniTag",props:{type:{type:String,default:"default"},size:{type:String,default:"normal"},text:{type:String,default:""},disabled:{type:[String,Boolean],defalut:!1},inverted:{type:[String,Boolean],defalut:!1},circle:{type:[String,Boolean],defalut:!1},mark:{type:[String,Boolean],defalut:!1}},methods:{onClick:function(){!0!==this.disabled&&"true"!==this.disabled&&this.$emit("click")}}};e.default=a},ced7:function(t,e,i){"use strict";i.r(e);var a=i("4a04"),o=i("d82d");for(var n in o)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return o[t]}))}(n);i("5fb1");var s=i("f0c5"),r=Object(s["a"])(o["default"],a["b"],a["c"],!1,null,"43de7aec",null,!1,a["a"],void 0);e["default"]=r.exports},d82d:function(t,e,i){"use strict";i.r(e);var a=i("ae8a"),o=i.n(a);for(var n in a)["default"].indexOf(n)<0&&function(t){i.d(e,t,(function(){return a[t]}))}(n);e["default"]=o.a},db90:function(t,e,i){"use strict";i("7a82"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=function(t){if("undefined"!==typeof Symbol&&null!=t[Symbol.iterator]||null!=t["@@iterator"])return Array.from(t)},i("a4d3"),i("e01a"),i("d3b7"),i("d28b"),i("3ca3"),i("ddb0"),i("a630")},e007:function(t,e,i){"use strict";i("7a82"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={name:"UniDrawer",props:{visible:{type:Boolean,default:!1},mode:{type:String,default:""},mask:{type:Boolean,default:!0}},data:function(){return{visibleSync:!1,showDrawer:!1,rightMode:!1,closeTimer:null,watchTimer:null,isIphoneX:!1}},watch:{visible:function(t){var e=this;clearTimeout(this.watchTimer),setTimeout((function(){e.showDrawer=t}),100),this.visibleSync&&clearTimeout(this.closeTimer),t?this.visibleSync=t:this.watchTimer=setTimeout((function(){e.visibleSync=t}),300)}},created:function(){var t=this;this.isIphoneX=this.$util.uniappIsIPhoneX(),this.visibleSync=this.visible,setTimeout((function(){t.showDrawer=t.visible}),100),this.rightMode="right"===this.mode},methods:{close:function(){var t=this;this.showDrawer=!1,this.closeTimer=setTimeout((function(){t.visibleSync=!1,t.$emit("close")}),200)},moveHandle:function(){}}};e.default=a},f749:function(t,e,i){"use strict";i("7a82");var a=i("4ea4").default;Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var o=a(i("8bd1")),n=a(i("ced7")),s=a(i("5f10")),r=a(i("1a00")),c={components:{uniDrawer:o.default,uniTag:n.default,nsGoodsSkuIndex:s.default},data:function(){return{}},mixins:[r.default]};e.default=c},fed2:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,".uni-drawer[data-v-dcc4de3c]{display:block;position:fixed;top:0;left:0;right:0;bottom:0;overflow:hidden;visibility:hidden;z-index:999;height:100%}.uni-drawer.uni-drawer--right .uni-drawer__content[data-v-dcc4de3c]{left:auto;right:0;-webkit-transform:translatex(100%);transform:translatex(100%)}.uni-drawer.uni-drawer--visible[data-v-dcc4de3c]{visibility:visible}.uni-drawer.uni-drawer--visible .uni-drawer__content[data-v-dcc4de3c]{-webkit-transform:translatex(0);transform:translatex(0)}.uni-drawer.uni-drawer--visible .uni-drawer__mask[data-v-dcc4de3c]{display:block;opacity:1}.uni-drawer__mask[data-v-dcc4de3c]{display:block;opacity:0;position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.4);transition:opacity .3s}.uni-drawer__content[data-v-dcc4de3c]{display:block;position:absolute;top:0;left:0;width:61.8%;height:100%;background:#fff;transition:all .3s ease-out;-webkit-transform:translatex(-100%);transform:translatex(-100%)}.safe-area[data-v-dcc4de3c]{padding-bottom:%?68?%;padding-top:%?44?%;box-sizing:border-box}",""]),t.exports=e}}]);