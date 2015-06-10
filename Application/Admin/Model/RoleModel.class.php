<?php
namespace Admin\Model;
use Think\Model;
class RoleModel extends Model 
{
	protected $insertFields = array('role_name');
	protected $updateFields = array('id','role_name');
	protected $_validate = array(
		array('role_name', 'require', '角色名称不能为空！', 1, 'regex', 3),
		array('role_name', '1,30', '角色名称的值最长不能超过 30 个字符！', 1, 'length', 3),
	);
	public function search($pageSize = 20)
	{
		/**************************************** 搜索 ****************************************/
		$where = array();
		/************************************* 翻页 ****************************************/
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $pageSize);
		// 配置翻页的样式
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$data['page'] = $page->show();
		/************************************** 取数据 ******************************************/
		//SELECT a.role_name,GROUP_CONCAT(c.pri_name) pri_name FROM php35_role a LEFT JOIN php35_role_privilege b ON a.id=b.role_id LEFT JOIN php35_privilege c ON b.pri_id=c.id GROUP BY a.id
		$data['data'] = $this->field('a.*,GROUP_CONCAT(c.pri_name) pri_name')->alias('a')->join('LEFT JOIN php35_role_privilege b ON a.id=b.role_id LEFT JOIN php35_privilege c ON b.pri_id=c.id')->where($where)->group('a.id')->limit($page->firstRow.','.$page->listRows)->select();
		return $data;
	}
	// 添加前
	protected function _before_insert(&$data, $option)
	{
	}
	// 添加完角色之后
	protected function _after_insert($data, $option)
	{
		$priId = I('post.pri_id');
		if($priId)
		{
			$rpModel = M('RolePrivilege');
			foreach ($priId as $v)
			{
				$rpModel->add(array(
					'role_id' => $data['id'],
					'pri_id' => $v,
				));
			}
		}
	}
	// 修改前
	protected function _before_update(&$data, $option)
	{
		// 把角色所拥有的权限的中间表也修改
		// 思路：先把这个角色拥有的所有的权限先全部删除掉，然后把修改的当作新的重新添加一遍即可
		$priId = I('post.pri_id');
		$rpModel = M('RolePrivilege');
		// 先删除原权限
		$rpModel->where(array(
			'role_id' => array('eq', $option['where']['id'])
		))->delete();
		if($priId)
		{
			foreach ($priId as $v)
			{
				$rpModel->add(array(
					'role_id' => $option['where']['id'],
					'pri_id' => $v,
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
		// 判断这个角色下有没有管理员，如果有不能删除
		$arModel = M('AdminRole');
		$hasAdmin = $arModel->where(array(
			'role_id' => array('eq', $option['where']['id'])
		))->count();
		if($hasAdmin > 0)
		{
			$this->error = '角色下有管理员，不允许删除！';
			return FALSE;
		}
		// 先这个角色拥有的中间表中的权限也删除
		$rpModel = M('RolePrivilege');
		$rpModel->where(array(
			'role_id' => array('eq', $option['where']['id'])
		))->delete();
	}
	/************************************ 其他方法 ********************************************/
}