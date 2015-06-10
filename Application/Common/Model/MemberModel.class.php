<?php
namespace Common\Model;
use Think\Model;
class MemberModel extends Model 
{
	protected $insertFields = array('email','password','cpassword','must_click','chkcode','gender','age','user_desc');
	protected $updateFields = array('id','email','password','cpassword','must_click','chkcode','gender','age','user_desc');
	// 登录时使用这个规则
	public $_login_validate = array(
		array('email', 'require', 'Email不能为空！', 1, 'regex', 3),
		array('email', 'email', 'Email格式不正确！', 1, 'regex', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 3),
		array('chkcode', 'chkcode', '验证码不正确！', 1, 'callback', 3),
	);
	// 注册和修改时使用以下规则
	protected $_validate = array(
		array('must_click', 'require', '必须同意注册协议才可以注册！', 1, 'regex', 1),
		array('email', 'require', 'Email不能为空！', 1, 'regex', 3),
		array('email', 'email', 'Email格式不正确！', 1, 'regex', 3),
		array('email', '1,150', 'Email的值最长不能超过 150 个字符！', 1, 'length', 3),
		array('password', 'require', '密码不能为空！', 1, 'regex', 3),
		array('password', '6,12', '密码的值最长是6-12位！', 1, 'length', 3),
		array('cpassword', 'password', '两次密码输入不一致！', 1, 'confirm', 3),
		array('gender', '男,女,保密', "性别的值只能是在 '男,女,保密' 中的一个值！", 2, 'in', 3),
		array('age', 'number', '年龄必须是一个整数！', 2, 'regex', 3),
		array('chkcode', 'chkcode', '验证码不正确！', 1, 'callback', 3),
		array('email', 'chk_email', 'email已经存在！', 1, 'callback', 3),
	);
	public function chk_email($email)
	{
		// 先查询表没有这个用户名
		$user = $this->field('id,status')->where(array(
			'email' => array('eq', $email),
		))->find();
		if($user)
		{
			// 判断有没有通过验证
			if($user['status'] == 0)
			{
				// 取出验证码生成的时间
				$mecModel = M('MemberEmailCode');
				$mectime = $mecModel->field('addtime')->where(array(
					'member_id' => array('eq', $user['id']),
				))->find();
				// 判断有没有过期
				if((time() - $mectime['addtime']) > C('REG_EMAIL_CHECK_EXPIRE'))
				{
					// 把这个注册的账号信息删除掉
					$this->delete($user['id']);
					// 验证码也删除掉
					$mecModel->where(array(
						'member_id' => array('eq', $user['id']),
					))->delete();
					return TRUE;
				}
				else 
					return FALSE;
			}
			else 
				return FALSE; // 无法再注册
		}
		else 
			return TRUE;
	}
	public function chkcode($code)
	{
		$verify = new \Think\Verify();
		return $verify->check($code);
	}
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($username = I('get.username'))
			$where['username'] = array('like', "%$username%");
		if($email = I('get.email'))
			$where['email'] = array('like', "%$email%");
		$gender = I('get.gender');
		if($gender != '' && $gender != '-1')
			$where['gender'] = array('eq', $gender);
		$start_age = I('get.start_age');
		$end_age = I('get.end_age');
		if($start_age && $end_age)
			$where['age'] = array('between', array($start_age, $end_age));
		elseif($start_age)
			$where['age'] = array('egt', $start_age);
		elseif($end_age)
			$where['age'] = array('elt', $end_age);
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->alias('a')->where($where)->group('a.id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option)
	{
		$data['password'] = md5($data['password'] . C('MD5_KEY'));
		if(isset($_FILES['face']) && $_FILES['face']['error'] == 0)
		{
			$ret = uploadOne('face', 'Admin', array(
				array(150, 150, 2),
			));
			if($ret['ok'] == 1)
			{
				$data['face'] = $ret['images'][0];
				$data['face'] = $ret['images'][1];
			}
			else 
			{
				$this->error = $ret['error'];
				return FALSE;
			}
		}
	}
	// 修改前
	protected function _before_update(&$data, $option)
	{
		if(isset($_FILES['face']) && $_FILES['face']['error'] == 0)
		{
			$ret = uploadOne('face', 'Admin', array(
				array(150, 150, 2),
			));
			if($ret['ok'] == 1)
			{
				$data['face'] = $ret['images'][0];
				$data['face'] = $ret['images'][1];
			}
			else 
			{
				$this->error = $ret['error'];
				return FALSE;
			}
			deleteImage(array(
				I('post.old_face'),
				I('post.old_face'),
	
			));
		}
	}
	// 删除前
	protected function _before_delete($option)
	{
		if(is_array($option['where']['id']))
		{
			$this->error = '不支持批量删除';
			return FALSE;
		}
		$images = $this->field('face,face')->find($option['where']['id']);
		deleteImage($images);
	}
	protected function _after_insert($data, $option)
	{
		// 会员注册成功之后判断session中是否有openid
		if(isset($_SESSION['openid']))
		{
			// 把OPENDI和当前会员关联上
			$this->where(array(
				'id' => array('eq', $data['id'])
			))->setField('openid', $_SESSION['openid']);
			unset($_SESSION['openid']);
		}
		// 生成唯一的验证码
		$code = md5(uniqid());
		$mecModel = M('MemberEmailCode');
		$mecModel->add(array(
			'addtime' => time(),
			'chk_code' => $code,
			'member_id' => $data['id'],
		));
		// 把验证码发给用户
		// heredoc语法：
		$domain = C('DOMAIN');
		$content =<<<HTML
			欢迎您加入php35网站，请点击以下链接地址完成注册：
		<p>
		<a target="_blank" href="{$domain}index.php/Home/Member/emailchk/id/{$data['id']}/code/$code">点击完成验证</a>
		</p>
HTML;
		sendMail($data['email'], 'php35网站-email验证', $content);
	}
	/************************************ 其他方法 ********************************************/
	public function login()
	{
		$email = $this->email;
		$password = $this->password;
		$user = $this->where(array(
			'email' => array('eq', $email),
		))->find();
		if($user)
		{
			if($user['status'] == 1)
			{
				if($user['password'] == md5($password . C('MD5_KEY')))
				{
					session('id', $user['id']);
					session('username', $user['username']);
					session('email', $user['email']);
					return TRUE;
				}
				else 
				{
					$this->error = '密码不正确！';
					return FALSE;
				}
			}
			else 
			{
				$this->error = '没有通过Email验证无法登录！';
				return FALSE;
			}
		}
		else 
		{
			$this->error = '账号不存在！';
			return FALSE;
		}
	}
}




















