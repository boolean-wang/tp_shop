<?php
namespace Home\Controller;
use Think\Controller;
class MemberController extends Controller 
{
	// 首页
	public function login()
	{
		if(IS_POST)
		{
			$model = D('Member');
			// 接收表单并验证表单
			if($model->validate($model->_login_validate)->create())
			{
				if($model->login() === TRUE)
					redirect('/');
			}
			$this->error($model->getError());
		}
		// 先设置页面信息
		$this->assign(array(
			'_hide_nav_' => 0,
			'_page_title' => '登录',
			'_page_keywords' => '登录',
			'_page_description' => '登录',
		));
		$this->display();
	}
	// 商品详情页
	public function regist()
	{
		if(IS_POST)
		{
			$model = D('Member');
			if($model->create(I('post.'), 1))
			{
				if($model->add())
				{
					$this->success('注册成功，已经向您的邮件发送了邮件，请登录邮箱进行验证，验证之后才可以登录！<p><a target="_blank" href="http://'.I('post.email').'">去验证</a></p>', U('login'), 20);
					exit;
				}
			}
			$this->error($model->getError());
		}
		// 先设置页面信息
		$this->assign(array(
			'_hide_nav_' => 1,
			'_page_title' => '注册',
			'_page_keywords' => '注册',
			'_page_description' => '注册',
		));
		$this->display();
	}
	public function chkcode()
	{
		$config =    array(
		    'fontSize'    =>    30,    // 验证码字体大小
		    'length'      =>    4,     // 验证码位数
		    'useNoise'    =>    false, // 关闭验证码杂点
		);
		$Verify = new \Think\Verify($config);
		$Verify->entry();
	}
	// 完成验证
	public function emailchk()
	{
		$id = I('get.id');
		$code = I('get.code');
		$mecModel = M('MemberEmailCode');
		$chkcode = $mecModel->field('addtime')->where(array(
			'member_id' => array('eq', $id),
			'chk_code' => array('eq', $code),
		))->find();
		if($chkcode)
		{
			$memberModel = M('Member');
			// 验证码删除掉
			$mecModel->where(array(
				'member_id' => array('eq', $id),
			))->delete();
			// 判断验证码有没有过期
			if((time() - $chkcode['addtime']) > C('REG_EMAIL_CHECK_EXPIRE'))
			{
				// 把这个注册的账号信息删除掉
				$memberModel->delete($id);
				$this->error('验证码已失效，请重新注册!', U('regist'));
			}
			else 
			{
				// 设置会员为验证成功
				$memberModel->where(array(
					'id' => array('eq', $id),
				))->setField('status', 1);
				$this->success('验证成功，现在可以登录！', U('login'));
				exit;
			}
		}
		else 
			$this->error('验证码无效！', U('regist'));
	}
	public function logout()
	{
		session(null);
		redirect('/');
	}
	public function ajaxChkLogin()
	{
		if(session('id'))
		{
			exit(json_encode(array(
				'login' => 1,
				'email' => session('email'),
			)));
		}
		else 
		{
			exit(json_encode(array(
				'login' => 0,
			)));
		}
	}
}





















