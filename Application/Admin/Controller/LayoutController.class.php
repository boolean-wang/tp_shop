<?php
namespace Admin\Controller;
class LayoutController extends BaseController
{
	public function top()
	{
		$this->display();
	}
	public function menu()
	{
		// 取出当前管理员所拥有的前两级的权限
		$priModel = D('Privilege');
		$menu = $priModel->getTop2LevelPri();
		$this->assign('menu', $menu);
		$this->display();
	}
	public function index()
	{
		$this->display();
	}
	public function main()
	{
		$this->display();
	}
}