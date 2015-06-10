<?php
return array(
	'tableName' => 'php35_member',    // 表名
	'tableCnName' => '会员',  // 表的中文名
	'moduleName' => 'Admin',  // 代码生成到的模块
	'withPrivilege' => FALSE,  // 是否生成相应权限的数据
	'topPriName' => '',        // 顶级权限的名称
	'digui' => 0,             // 是否无限级（递归）
	'diguiName' => '',        // 递归时用来显示的字段的名字，如cat_name（分类名称）
	'pk' => 'id',    // 表中主键字段名称
	/********************* 要生成的模型文件中的代码 ******************************/
	// 添加时允许接收的表单中的字段
	'insertFields' => "array('username','email','password','gender','age','user_desc')",
	// 修改时允许接收的表单中的字段
	'updateFields' => "array('id','username','email','password','gender','age','user_desc')",
	'validate' => "
		array('username', 'require', '用户名不能为空！', 1, 'regex', 3),
		array('username', '1,30', '用户名的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('email', 'require', 'Email不能为空！', 1, 'regex', 3),
		array('email', 'email', 'Email格式不正确！', 1, 'regex', 3),
		array('email', '1,150', 'Email的值最长不能超过 150 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 3),
		array('password', '1,32', '密码的值最长不能超过 32 个字符！', 1, 'length', 3),
		array('gender', 'require', \"必须不能为空！\", 1, 'regex', 3),
		array('gender', '男,女,保密', \"性别的值只能是在 '男,女,保密' 中的一个值！\", 2, 'in', 3),
		array('age', 'number', '年龄必须是一个整数！', 2, 'regex', 3),
	",
	/********************** 每个字段在表单中以什么形式显示【单选框、文本框、在线编辑器等等】 ****************************/
	'fields' => array(
		'username' => array(
			'text' => '用户名',
			'type' => 'text', // 文本框
			'default' => '',
		),
		'email' => array(
			'text' => 'Email',
			'type' => 'text',
			'default' => '',
		),
		'password' => array(
			'text' => '密码',
			'type' => 'password',
			'default' => '',
		),
		'gender' => array(
			'text' => '性别',
			'type' => 'radio',  // 单选框
			'values' => array(
				'男' => '男',
				'女' => '女',
				'保密' => '保密',
			),
			'default' => '保密',
		),
		'age' => array(
			'text' => '年龄',
			'type' => 'text',
			'default' => '0',
		),
		// 程序上判断如果字段的名字叫做：face|pic|image|logo就认为是一个图片
		'face' => array(
			'text' => '头像',
			'type' => 'file',   // 文本域
			// 配置缩略图【默认生成三张图片】
			'thumbs' => array(
				array(150, 150, 2),
			),
			// 生成的图片保存到哪个字段中,第一个：原图存到哪个字段，第二个是第一个缩略图存到哪个字段。。。。
			'save_fields' => array('face', 'sm_face'),
			'default' => '',
		),
		'user_desc' => array(
			'text' => '个人介绍',
			'type' => 'html',   // 在线富文本编辑器
			'default' => '',
		),
	),
	/**************** 搜索字段的配置 **********************/
	'search' => array(
		array('username', 'normal', '', 'like', '用户名'),
		array('email', 'normal', '', 'like', 'Email'),
		array('gender', 'in', '男-男,女-女,保密-保密', '', '性别'),
		// 如果是一个区间搜索，那么两个文本框的名字是start_age和end_age
		array('age', 'between', 'start_age,end_age', '', '年龄'),
	),
);