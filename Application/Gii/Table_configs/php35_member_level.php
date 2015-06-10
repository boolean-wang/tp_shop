<?php
return array(
	'tableName' => 'php35_member_level',    // 表名
	'tableCnName' => '会员级别',  // 表的中文名
	'moduleName' => 'Admin',  // 代码生成到的模块
	'withPrivilege' => FALSE,  // 是否生成相应权限的数据
	'topPriName' => '',        // 顶级权限的名称
	'digui' => 0,             // 是否无限级（递归）
	'diguiName' => '',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	// 添加时允许接收的表单中的字段
	'insertFields' => "array('level_name','low_num','high_num','price_rate')",
	// 修改时允许接收的表单中的字段
	'updateFields' => "array('id','level_name','low_num','high_num','price_rate')",
	'validate' => "
		array('level_name', 'require', '级别名称不能为空！', 1, 'regex', 3),
		array('level_name', '1,30', '级别名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('low_num', 'require', '积分下限不能为空！', 1, 'regex', 3),
		array('low_num', 'number', '积分下限必须是一个整数！', 1, 'regex', 3),
		array('high_num', 'require', '积分上限不能为空！', 1, 'regex', 3),
		array('high_num', 'number', '积分上限必须是一个整数！', 1, 'regex', 3),
		array('price_rate', 'require', '折扣率，100:10折，90:9折不能为空！', 1, 'regex', 3),
		array('price_rate', 'number', '折扣率，100:10折，90:9折必须是一个整数！', 1, 'regex', 3),
	",
	/********************** 表中每个字段信息的配置 ****************************/
	'fields' => array(
		'level_name' => array(
			'text' => '级别名称',
			'type' => 'text',
			'default' => '',
		),
		'low_num' => array(
			'text' => '积分下限',
			'type' => 'text',
			'default' => '',
		),
		'high_num' => array(
			'text' => '积分上限',
			'type' => 'text',
			'default' => '',
		),
		'price_rate' => array(
			'text' => '折扣率，100:10折，90:9折',
			'type' => 'text',
			'default' => '',
		),
	),
);