<?php
return array(
	//'APP_DEBUG' => TRUE,
	//'SHOW_PAGE_TRACE' => TRUE,
	'DB_TYPE' => 'mysql',
	'DB_HOST' => '127.0.0.1',
	'DB_NAME' => 'php35',
	'DB_USER' => 'root',
	'DB_PWD' => '',
	'DB_PREFIX' => 'php35_',
	'DEFAULT_FILTER' => 'trim,htmlspecialchars',  // I函数使用哪些函数来过滤
	'TMPL_ACTION_ERROR' => APP_PATH . 'Common/admin_info.html',
	'TMPL_ACTION_SUCCESS' => APP_PATH . 'Common/admin_info.html',
	'DOMAIN' => 'http://www.35.com/',
	/*************** 图片相关配置 *********************/
	'IMAGE_PREFIX' => '/Public/Uploads/',
	'IMAGE_SAVE_PATH' => './Public/Uploads/',
	'IMG_maxSize' => 2, // 单位M
	'IMG_exts' => array('jpg', 'gif', 'png', 'jpeg', 'pjpeg', 'bmp'),
	/************** 加密时使用的密钥 *********************/
	'MD5_KEY' => 'fdasf~jf*#22.fdd)03934ld++_)32o23lDek~~!1``1i323sc?>',
	/************** 发邮件的配置 ***************/
	'MAIL_ADDRESS' => 'php28_28@163.com',   // 发货人的email
	'MAIL_FROM' => 'php28_28',      // 发货人姓名
	'MAIL_SMTP' => 'smtp.163.com',      // 邮件服务器的地址
	'MAIL_LOGINNAME' => 'php28_28',   
	'MAIL_PASSWORD' => 'php123123',
	/**************** 注册时验证码过期时间 ****************/
	'REG_EMAIL_CHECK_EXPIRE' => 7200, // 秒
);