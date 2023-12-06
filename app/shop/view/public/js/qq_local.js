var map;
/**
 * 创建地图
 * @param latlng
 */

async function createMap(id, lnglat) {
    var center;
    if(lnglat["lat"] != ''|| lnglat["lng"] != '' ){
        center = new TMap.LatLng(lnglat.lat,lnglat.lng);
    } else {
        await getCurrentPosition(function(res){
            if (res.status == 0) {
                var location = res.result.location;
                center = new TMap.LatLng(location.lat, location.lng);
            }
        })
    }

    map = new TMap.Map(document.getElementById(id), {
        center: center,//设置地图中心点坐标
        zoom: 17   //设置地图缩放级别
    });
}

var editor_array = {};
/**
 * 创建一个圆, 并且给与编辑权限
 */
function createCircle(key, color, border_color, path_param){
    if(path_param == undefined){
        var center = map.getCenter(); //获取当前地图中心位置
        var radius = 1000;
    }else{
        var center = new TMap.LatLng(path_param.center.latitude, path_param.center.longitude)
        var radius = path_param.radius;
    }

    var multiCircle = new TMap.MultiCircle({
        map: map,
        styles: { // 设置圆形样式
            'circle': new TMap.CircleStyle({
                'color': set16ToRgba(color, 0.4),
                'showBorder': true,
                'borderColor': border_color,
                'borderWidth': 2
            }),
        },
        geometries: [
            {
                styleId: 'circle',
                center: center,
                radius: radius,
                id: key
            }
        ]
    });

    var circle = new TMap.tools.GeometryEditor({
        map: map,
        overlayList: [
            {
                overlay: multiCircle,
                id: key,
            }
        ],
        actionMode: TMap.tools.constants.EDITOR_ACTION.INTERACT,
        activeOverlayId: key, // 激活图层
        selectable: true, // 开启点选功能
    })
    // 缩放地图
    map.easeTo({zoom: 14 })
    circle.select([key]);
    editor_array[key] = circle;

    // 初始化数据
    editor_array[key].data = {
        center: center,
        radius: radius
    }

    circle.on('adjust_complete', function (data) {
        editor_array[key].data = data;
    })
}

/**
 * 创建多边形
 * @param key
 * @param color
 * @param border_color
 * @param path_param
 */
function createPolygon(key, color, border_color, path_param){
    var center = map.getCenter();
    var lat = center.lat;
    var lng = center.lng;

    if(path_param == undefined){
        var path = [
            new TMap.LatLng(lat+0.01, lng+0.01),
            new TMap.LatLng(lat-0.01, lng+0.01),
            new TMap.LatLng(lat-0.01, lng-0.01),
            new TMap.LatLng(lat+0.01, lng-0.01),
        ]
    }else{
        var path = []
        $.each(path_param, function(i, item){
            path.push(new TMap.LatLng(item.latitude, item.longitude));
        });
    }

    var multiPolygon = new TMap.MultiPolygon({
        map: map,
        styles: {
            polygon: new TMap.PolygonStyle({
                'color': set16ToRgba(color, 0.4),
                'showBorder': true,
                'borderColor': border_color,
                'borderWidth': 2
            })
        },
        geometries: [
            {
                id: key, // 多边形图形数据的标志信息
                styleId: 'polygon', // 样式id
                paths: path, // 多边形的位置信息
                properties: {
                    // 多边形的属性数据
                    title: 'polygon',
                },
            }
        ]
    });

    var polygon = new TMap.tools.GeometryEditor({
        map: map,
        overlayList: [
            {
                overlay: multiPolygon,
                id: key,
            }
        ],
        actionMode: TMap.tools.constants.EDITOR_ACTION.INTERACT,
        activeOverlayId: key, // 激活图层
        selectable: true, // 开启点选功能
    })
    // 缩放地图
    map.easeTo({zoom: 14 })
    polygon.select([key]);
    editor_array[key] = polygon;

    // 初始化数据
    editor_array[key].data = {
        paths: path
    };

    polygon.on('adjust_complete', function (data) {
        editor_array[key].data = data;
    })
}

/**
 * 移除覆盖物
 * @param overlayers
 */
function removeOverlayers(key){
    var editor = editor_array[key];
    editor.delete();//删除覆盖物
}

/**
 * 给与覆盖物焦点
 */
function foursOverlayers(key){
}

/**
 * 获取覆盖物实例
 * @param key
 * @returns {*}
 */
function getOverlayersPath(key, type){
    var editor = editor_array[key];
    switch(type){
        //多边形
        case 'polygon':{
            var return_json = [];
            editor.data.paths.forEach(function (item) {
                var item_json = {longitude:item.lng,latitude:item.lat};
                return_json.push(item_json);
            })
            return return_json;
            break;
        }
        //圆
        case 'circle':{
            var return_json = {};
            var center = editor.data.center;
            return_json['center'] = {longitude:center.lng,latitude:center.lat};
            return_json['radius'] = editor.data.radius;
            return return_json;
            break;
        }
    }


}

/**
 * 获取定位
 * @param callback
 */
async function getCurrentPosition(callback){
    var ipLocation = new TMap.service.IPLocation();
    await ipLocation.locate({}).then(function(res){
        callback(res)
    })
}

/**
 * 16进制转rgba
 * @param str
 * @returns {string}
 */
function set16ToRgba(str, opacity){
    var reg = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/
    if(!reg.test(str)){return;}
    let newStr = (str.toLowerCase()).replace(/\#/g,'')
    let len = newStr.length;
    if(len == 3){
        let t = ''
        for(var i=0;i<len;i++){
            t += newStr.slice(i,i+1).concat(newStr.slice(i,i+1))
        }
        newStr = t
    }
    let arr = []; //将字符串分隔，两个两个的分隔
    for(var i =0;i<6;i=i+2){
        let s = newStr.slice(i,i+2)
        arr.push(parseInt("0x" + s))
    }
    return 'rgba(' + arr.join(",")  + ', '+ opacity +')';
}