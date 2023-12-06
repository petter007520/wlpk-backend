//author  周
// var map, marker, infoWindow,  geolocation, geocoder;//地图加载默认参数
mapClass = function(id, lnglat, map_success_back){
    /*******************************************************地图加载事件start**********************************************************/
    //加载地图，调用浏览器定位服务
    _this = this;

    this.init(id, lnglat, map_success_back);//初始化函数
};
mapClass.prototype = {
    init : async function(id, lnglat, map_success_back){
        _this.setAttr();

        _this.map_callback = map_success_back;

        var center;
        if(lnglat["lat"] != ''|| lnglat["lng"] != '' ){
            center = new TMap.LatLng(lnglat.lat,lnglat.lng);
        } else {
        	await _this.getCurrentPosition(function(res){
        		if (res.status == 0) {
        			var location = res.result.location;
    				center = new TMap.LatLng(location.lat, location.lng);
        		}
        	})
        }
        _this.map =  new TMap.Map(document.getElementById(id), {
            center: center,//设置地图中心点坐标
            zoom: 17   //设置地图缩放级别
        });

    	// 创建中心点标记
    	_this.marker = new TMap.MultiMarker({
			map: _this.map,
			geometries: [
				{
					id: 'center',
					position: _this.map.getCenter(),
				}
			]
		});

		_this.infoWindow = new TMap.InfoWindow({
		   content: "", //信息窗口内容
		   position: center,//显示信息窗口的坐标
		   map: _this.map,
		   offset: {x: 0, y: -50}  
		});
		_this.infoWindow.close(); // 初始化关闭窗体

        _this.map.on('click',function(e){
            if(_this.map_click){
                _this.map_change = false;
                _this.getAddress(e.latLng,function(result){
                    _this.markerMove(e.latLng, result.address, result.addressComponents);
                })
            }else{
                return false;
            }

        })

        if(map_success_back != undefined){
            map_success_back(_this);
        }
    },
   	getAddress(LatLng, callback){
   		var geocoder = new qq.maps.Geocoder({
		    complete:function(result){
		    	var detail = result.detail;
		    	callback(detail);
		    }
		});
		var coord=new qq.maps.LatLng(LatLng.lat,LatLng.lng);
		geocoder.getAddress(coord)
    },
    getCurrentPosition : async function (callback){
        var ipLocation = new TMap.service.IPLocation();
        await ipLocation.locate({}).then(function(res){
            callback(res)
        })
    },
    markerMove : function(position, address, data){
    	var center = new TMap.LatLng(position.lat, position.lng);
        var address_detail = address;
     	address_detail = address_detail.replace(data.country,'');
        address_detail = address_detail.replace(data.province,'');
        address_detail = address_detail.replace(data.city,'');
        address_detail = address_detail.replace(data.district,'');

    	_this.marker.updateGeometries([
    		{
				id: 'center',
				position: center
			}
		]);
		_this.infoWindow.open()
		_this.infoWindow.setPosition(center);
        _this.infoWindow.setContent("<p>当前位置：<span class='text-color'>" + address + "</span>");
       	_this.map.setCenter(position);

        _this.address.province_name = data.province;
        if(data.city == ""){
            _this.address.city_name = data.province;
        }else{
            _this.address.city_name = data.city;
        }
        _this.address.district_name = data.district;
        _this.address.township_name = data.street;
        _this.address.area = _this.address.province_name+","+_this.address.city_name+","+_this.address.district_name+","+_this.address.township_name;

        _this.address.longitude = position.lng;
        _this.address.latitude = position.lat;
        _this.address.address = address_detail;
        if(!_this.map_change){
            mapChangeCallBack();
        }else{
            selectCallBack();
        }
    },
    mapChange : function(address){
        if (_this.map_change) {
            var province_name = _this.address.province > 0 ? _this.address.province_name : '';
            var city_name = _this.address.province > 0 && _this.address.city > 0 ? _this.address.city_name : '';
            var districts_name = _this.address.province > 0 && _this.address.city > 0 && _this.address.district > 0 ? _this.address.district_name : '';
           	var address_detail = _this.address.province > 0 && _this.address.city > 0 && _this.address.district > 0 ? _this.address.detail_address : '';
            if(!address){
				address = province_name +','+ city_name +','+ districts_name +','+ address_detail;
            }
            _this.getLocation(address, function (result) {
            	_this.markerMove(result.location, result.address, result.addressComponents);
            });
        }

    },
    getLocation(address, callback){
    	var callbacks = {
		    complete:function(result){
		    	var detail = result.detail;
		    	callback(detail);
		    }
		}
		var geocoder = new qq.maps.Geocoder(callbacks);
        geocoder.getLocation(address);
    },
    setAttr : function(){
        _this.map = "";
        _this.marker = "";
        _this.infoWindow = "";
        _this.geolocation = "";
        _this.geocoder = "";
        _this.location = false;
        _this.map_change = true;
        _this.map_click = true;
        _this.zoom = 15;
        _this.address = {
            province : "",
            province_name : "",
            city : "",
            city_name : "",
            district : "",
            district_name : "",
            township : "",
            township_name : "",
            address : "",
            longitude : "",
            latitude : "",
            area : ""
        };
    },
    destroy : function(){
        _this.map.destroy( );//销毁地图
    },
    setMapCircle : function(radius_num, position){
        if(radius_num == undefined && radius_num <= 0){
            return;
        }
        _this.map.clearMap();
        // var circle_arr = map.getAllOverlays('circle');
        // if(circle_arr.length > 0){{
        //     map.remove(circle)
        // }

        if(position != undefined){
            var center_position = new AMap.LngLat(position['lon'], position['lat'])
        }else{
            var center_position = _this.marker.getPosition();
        }
        var circle = new AMap.Circle({
            center:  center_position,// 圆心位置
            radius: radius_num*1000, //半径
            strokeColor: "#F33", //线颜色
            strokeOpacity: 1, //线透明度
            strokeWeight: 3, //线粗细度
            fillColor: "#ee2200", //填充颜色
            fillOpacity: 0.35//填充透明度
        });
        _this.map.add(_this.marker);
        circle.setMap(_this.map);
    }

}


