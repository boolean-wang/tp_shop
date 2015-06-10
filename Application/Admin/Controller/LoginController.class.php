<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller
{
	public function login()
	{
		if(IS_POST)
		{
			$model = D('Admin');
			// 接收登录的表单并且验证表单
			if($model->create(I('post.'), 9))
			{
				if($model->login())
				{
					$this->success('登录成功！', U('Admin/Layout/index'));
					exit;
				}
			}
			$this->error($model->getError());
		}
		$this->display();
	}
	public function logout()
	{
		
	}
	// 生成验证码的图片
	public function checkcode()
	{
		$config =    array(
		    'fontSize'    =>    26,    // 验证码字体大小
		    'length'      =>    4,     // 验证码位数
		    'useNoise'    =>    TRUE, // 关闭验证码杂点
		);
		$Verify = new \Think\Verify($config);
		$Verify->entry();
	}
}