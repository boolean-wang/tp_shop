<?php
return array(
	'HTML_CACHE_ON'     =>    FALSE, // 开启静态缓存
	'HTML_CACHE_TIME'   =>    60,   // 全局静态缓存有效期（秒）
	'HTML_FILE_SUFFIX'  =>    '.shtml', // 设置静态缓存文件后缀
	// 哪些页面生成缓存
	'HTML_CACHE_RULES'  =>     array(
		//'控制器名：方法名' => array('静态名', '过期时间')
		'Index:index'   => array('index', 3600),
		'Index:goods'   => array('goods/{id|todir}/goods-{id}', 3600),
	)
);

function todir($id)
{
	return ceil($id / 1000);
}