<?php
return array(
	'tableName' => 'php35_goods',    // 表名
	'tableCnName' => '商品',  // 表的中文名
	'moduleName' => 'Home',  // 代码生成到的模块
	'withPrivilege' => FALSE,  // 是否生成相应权限的数据
	'topPriName' => '',        // 顶级权限的名称
	'digui' => 0,             // 是否无限级（递归）
	'diguiName' => '',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	// 添加时允许接收的表单中的字段
	'insertFields' => "array('goods_name','market_price','shop_price','is_on_sale','is_delete','goods_desc','sort_num')",
	// 修改时允许接收的表单中的字段
	'updateFields' => "array('id','goods_name','market_price','shop_price','is_on_sale','is_delete','goods_desc','sort_num')",
	'validate' => "
		array('goods_name', 'require', '商品名称不能为空！', 1, 'regex', 3),
		array('goods_name', '1,30', '商品名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('market_price', 'require', '市场价格不能为空！', 1, 'regex', 3),
		array('market_price', 'currency', '市场价格必须是货币格式！', 1, 'regex', 3),
		array('shop_price', 'require', '本店价格不能为空！', 1, 'regex', 3),
		array('shop_price', 'currency', '本店价格必须是货币格式！', 1, 'regex', 3),
		array('is_on_sale', 'number', '是否上架，1：上架 0：下架必须是一个整数！', 2, 'regex', 3),
		array('is_delete', 'number', '是否加入回收站，1：删除了 0：未删除必须是一个整数！', 2, 'regex', 3),
		array('sort_num', 'number', '排序的数字，越小越靠前必须是一个整数！', 2, 'regex', 3),
	",
	/********************** 表中每个字段信息的配置 ****************************/
	'fields' => array(
		'goods_name' => array(
			'text' => '商品名称',
			'type' => 'text',
			'default' => '',
		),
		'market_price' => array(
			'text' => '市场价格',
			'type' => 'text',
			'default' => '',
		),
		'shop_price' => array(
			'text' => '本店价格',
			'type' => 'text',
			'default' => '',
		),
		'logo' => array(
			'text' => '原图片路径',
			'type' => 'file',
			'thumbs' => array(
				array(350, 350, 2),
				array(150, 150, 2),
				array(50, 50, 2),
			),
			'save_fields' => array('logo', 'big_logo', 'mid_logo', 'sm_logo'),
			'default' => '',
		),
		'is_on_sale' => array(
			'text' => '是否上架，1：上架 0：下架',
			'type' => 'text',
			'default' => '1',
		),
		'is_delete' => array(
			'text' => '是否加入回收站，1：删除了 0：未删除',
			'type' => 'text',
			'default' => '0',
		),
		'goods_desc' => array(
			'text' => '商品描述',
			'type' => 'html',
			'default' => '',
		),
		'sort_num' => array(
			'text' => '排序的数字，越小越靠前',
			'type' => 'text',
			'default' => '100',
		),
	),
	/**************** 搜索字段的配置 **********************/
	'search' => array(
		array('goods_name', 'normal', '', 'like', '商品名称'),
		array('market_price', 'between', 'market_pricefrom,market_priceto', '', '市场价格'),
		array('shop_price', 'between', 'shop_pricefrom,shop_priceto', '', '本店价格'),
		array('is_on_sale', 'normal', '', 'eq', '是否上架，1：上架 0：下架'),
		array('is_delete', 'normal', '', 'eq', '是否加入回收站，1：删除了 0：未删除'),
		array('goods_desc', 'normal', '', 'eq', '商品描述'),
		array('sort_num', 'normal', '', 'eq', '排序的数字，越小越靠前'),
	),
);