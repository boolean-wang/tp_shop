<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller
{
	public function __construct()
	{
		// 先调用父类的【PHP的要求】
		parent::__construct();
		// 必须登录
		if(!session('admin_id'))
			$this->error('必须先登录！', U('Admin/Login/login'));
		// 判断管理员有没有访问这个页面的权限
		$priModel = D('Privilege');
		// 所有管理员都可以进入后台查看布局主页面
		if(CONTROLLER_NAME == 'Layout')
			return TRUE;
		// 先拼出当前正在访问的页面的url
		$url = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
		if(!$priModel->hasPri($url))
			$this->error('无权访问');
	}
}