<?php
// 事件定义文件
return [
	'bind' => [
	
	],
	
	'listen' => [
		//展示活动
        'ShowPromotion' => [
            'addon\postertemplate\event\ShowPromotion',
        ],
        'PosterTemplate' => [
            'addon\postertemplate\event\PosterTemplate',
        ]
	],
	
	'subscribe' => [
	],
];
