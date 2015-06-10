<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model 
{
	protected $insertFields = array('username','password','is_use','cpassword');
	protected $updateFields = array('id','username','password','is_use','cpassword');
	protected $_validate = array(
		array('checkcode', 'check_verify', '验证码不正确！', 1, 'callback', 9),
		array('username', 'require', '账号不能为空！', 1, 'regex', 3),
		array('username', '1,30', '账号的值最长不能超过 30 个字符！', 1, 'length', 3),
		// 第 六个参数设置为1：代表只有添加时这个规则生效
		array('password', 'require', '密码不能为空！', 1, 'regex', 1),
		// 第4个参数为2：只有密码不为空时才验证，如果没填密码就不验证
		array('password', '1,32', '密码的值最长不能超过 32 个字符！', 2, 'length', 3),	
		array('cpassword', 'password', '两次密码输入不一致！', 1, 'confirm', 1),
		array('cpassword', 'password', '两次密码输入不一致！', 1, 'confirm', 2),
		array('is_use', '0,1', '是否禁用只能是1，或者0两个值！', 2, 'in', 3),
	);
	// 验证验证码的方法
	function check_verify($code, $id = ''){
	    $verify = new \Think\Verify();
	    return $verify->check($code, $id);
	}
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		if($username = I('get.username'))
			$where['username'] = array('like', "%$username%");
		$is_use = I('get.is_use');
		if($is_use != '' && $is_use != '-1')
			$where['is_use'] = array('eq', $is_use);
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		$data['data'] = $this->field('a.*,GROUP_CONCAT(c.role_name) role_name')->alias('a')->join('LEFT JOIN __ADMIN_ROLE__ b ON a.id=b.admin_id LEFT JOIN __ROLE__ c ON b.role_id=c.id')->where($where)->group('a.id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option)
	{
		$data['password'] = md5($data['password'] . C('MD5_KEY'));
	}
	protected function _after_insert($data, $option)
	{
		$roleId = I('post.role_id');
		if($roleId)
		{
			$arModel = M('AdminRole');
			foreach ($roleId as $v)
			{
				$arModel->add(array(
					'admin_id' => $data['id'],
					'role_id' => $v,
				));
			}
		}
	}
	// 修改前
	protected function _before_update(&$data, $option)
	{
		if($option['where']['id'] == 1 && isset($data['is_use']))
		{
			$this->error = '不能禁用超级管理员';
			return FALSE;
		}
		if($data['password'])
			$data['password'] = md5($data['password'] . C('MD5_KEY'));
		else 
			unset($data['password']); // 从表单中删除，就不修改这个字段
		// 处理角色
		$roleId = I('post.role_id');
		$arModel = M('AdminRole');
		$arModel->where(array(
			'admin_id' => $option['where']['id'],
		))->delete();
		if($roleId)
		{
			foreach ($roleId as $v)
			{
				$arModel->add(array(
					'admin_id' => $option['where']['id'],
					'role_id' => $v,
				));
			}
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
		if($option['where']['id'] == 1)
		{
			$this->error = '超级管理员不能删除！';
			return FALSE;
		}
		// 把这个管理员所在的角色的数据也删除掉
		$arModel = M('AdminRole');
		$arModel->where(array(
			'admin_id' => array('eq', $option['where']['id'])
		))->delete();
	}
	/************************************ 其他方法 ********************************************/
	public function login()
	{
		// 接收用户名密码，因为在控制器中已经调用了$model->create()方法接收表单，这个方法就已经把表单中的数据接收到这个模型中了，所以现在在模型中可以直接使用$this来访问接收到的数据
		$username = $this->username;
		$password = $this->password;
		// 先查询账号是否存在
		$user = $this->where(array(
			'username' => array('eq', $username),
		))->find();
		if($user)
		{
			// 再判断这个账号有没有被禁用
			if($user['is_use'] == 1)
			{
				// 再判断密码对不对
				if($user['password'] == md5($password . C('MD5_KEY')))
				{
					// 登录成功
					session('admin_id', $user['id']);
					session('username', $user['username']);
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
				$this->error = '账号被禁用！';
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


















