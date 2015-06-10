<?php
return array(
	'tableName' => 'php35_admin',    // 表名
	'tableCnName' => '管理员',  // 表的中文名
	'moduleName' => 'Admin',  // 代码生成到的模块
	'withPrivilege' => FALSE,  // 是否生成相应权限的数据
	'topPriName' => '',        // 顶级权限的名称
	'digui' => 0,             // 是否无限级（递归）
	'diguiName' => '',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	// 添加时允许接收的表单中的字段
	'insertFields' => "array('username','password','is_use')",
	// 修改时允许接收的表单中的字段
	'updateFields' => "array('id','username','password','is_use')",
	'validate' => "
		array('username', 'require', '账号不能为空！', 1, 'regex', 3),
		array('username', '1,30', '账号的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 3),
		array('password', '1,32', '密码的值最长不能超过 32 个字符！', 1, 'length', 3),
		array('is_use', '0,1', '是否禁用只能是1，或者0两个值！', 2, 'in', 3),
	",
	/********************** 表中每个字段信息的配置 ****************************/
	'fields' => array(
		'username' => array(
			'text' => '账号',
			'type' => 'text',
			'default' => '',
		),
		'password' => array(
			'text' => '密码',
			'type' => 'password',
			'default' => '',
		),
		'is_use' => array(
			'text' => '是否使用 1：使用 0：禁用',
			'type' => 'radio',
			'values' => array(
				'1' => '使用',
				'0' => '禁用',
			),
			'default' => '1',
		),
	),
	/**************** 搜索字段的配置 **********************/
	'search' => array(
		array('username', 'normal', '', 'like', '账号'),
		array('is_use', 'in', '1-使用,0-禁用', '', '是否使用'),
	),
);












