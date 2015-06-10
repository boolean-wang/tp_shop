<?php
return array(
	'tableName' => 'php35_category',    // 表名
	'tableCnName' => '分类',  // 表的中文名
	'moduleName' => 'Admin',  // 代码生成到的模块
	'withPrivilege' => FALSE,  // 是否生成相应分类的数据
	'topPriName' => '',        // 顶级分类的名称
	'digui' => 1,             // 是否无限级（递归）
	'diguiName' => 'cat_name',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	// 添加时允许接收的表单中的字段
	'insertFields' => "array('cat_name','parent_id','sort_num','is_rec','seo_keyword','seo_description')",
	// 修改时允许接收的表单中的字段
	'updateFields' => "array('id','cat_name','parent_id','sort_num','is_rec','seo_keyword','seo_description')",
	'validate' => "
		array('cat_name', 'require', '分类名称不能为空！', 1, 'regex', 3),
		array('cat_name', '1,30', '分类名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('parent_id', 'number', '上级的ID，0:顶级必须是一个整数！', 2, 'regex', 3),
		array('sort_num', 'number', '排序的数字必须是一个整数！', 2, 'regex', 3),
		array('is_rec', 'number', '是否推荐到首页中间必须是一个整数！', 2, 'regex', 3),
		array('seo_keyword', '1,150', 'SEO关键字的值最长不能超过 150 个字符！', 2, 'length', 3),
		array('seo_description', '1,150', 'SEO描述的值最长不能超过 150 个字符！', 2, 'length', 3),
	",
	/********************** 表中每个字段信息的配置 ****************************/
	'fields' => array(
		'cat_name' => array(
			'text' => '分类名称',
			'type' => 'text',
			'default' => '',
		),
		'parent_id' => array(
			'text' => '上级分类',
			'type' => 'text',
			'default' => '0',
		),
		'sort_num' => array(
			'text' => '排序的数字',
			'type' => 'text',
			'default' => '100',
		),
		'is_rec' => array(
			'text' => '是否推荐到首页中间',
			'type' => 'radio',
			'values' => array(
				'0' => '不推荐',
				'1' => '推荐'
			),
			'default' => '0',
		),
		'seo_keyword' => array(
			'text' => 'SEO关键字',
			'type' => 'text',
			'default' => '',
		),
		'seo_description' => array(
			'text' => 'SEO描述',
			'type' => 'text',
			'default' => '',
		),
	),
);