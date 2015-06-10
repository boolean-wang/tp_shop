<?php
namespace Admin\Model;
use Think\Model;
class PrivilegeModel extends Model 
{
	protected $insertFields = array('pri_name','module_name','controller_name','action_name','parent_id','sort_num');
	protected $updateFields = array('id','pri_name','module_name','controller_name','action_name','parent_id','sort_num');
	protected $_validate = array(
		array('pri_name', 'require', '权限名称不能为空！', 1, 'regex', 3),
		array('pri_name', '1,30', '权限名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('module_name', 'require', '对应的模块不能为空！', 1, 'regex', 3),
		array('module_name', '1,30', '对应的模块的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('controller_name', 'require', '对应的控制器不能为空！', 1, 'regex', 3),
		array('controller_name', '1,30', '对应的控制器的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('action_name', 'require', '对应的方法不能为空！', 1, 'regex', 3),
		array('action_name', '1,30', '对应的方法的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('parent_id', 'number', '上级权限的ID，0:顶级权限必须是一个整数！', 2, 'regex', 3),
		array('sort_num', 'number', '排序的数字必须是一个整数！', 2, 'regex', 3),
	);
	/************************************* 递归相关方法 *************************************/
	public function getTree()
	{
		$data = $this->order('sort_num ASC')->select();
		return $this->_reSort($data);
	}
	private function _reSort($data, $parent_id=0, $level=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$v['level'] = $level;
				$ret[] = $v;
				$this->_reSort($data, $v['id'], $level+1, FALSE);
			}
		}
		return $ret;
	}
	public function getChildren($id)
	{
		$data = $this->select();
		return $this->_children($data, $id);
	}
	private function _children($data, $parent_id=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$ret[] = $v['id'];
				$this->_children($data, $v['id'], FALSE);
			}
		}
		return $ret;
	}
	/************************************ 其他方法 ********************************************/
	public function _before_delete($option)
	{
		// 获取当前权限的所有子权限的ID并返回一维数组
		$children = $this->getChildren($option['where']['id']);
		// 如果有子不允许删除掉
		if($children)
		{
			$this->error = '有子权限,无法删除！';
			return FALSE;
			// 把子权限一起删除掉
//			$children = implode(',', $children);
//			$this->execute("DELETE FROM __PRIVILEGE__ WHERE id IN($children)");
		}
	}
	/**
	 * 判断一个管理员有没有一个页面的权限
	 *
	 * @param unknown_type $url ： 模块/控制器/方法拼成的一个字符串,
	 * 比如我要判断一个管理员有没有添加商品的权限：
	 * $priModel = D('Privilege');
	 * $priModel->hasPri('Admin/Goods/add');
	 */
	public function hasPri($url)
	{
		$adminId = session('admin_id');
		// 如果是超级管理员直接有
		if($adminId == 1)
			return TRUE;
		// 先取出当前管理员所属的角色的ID
		$arModel = M('AdminRole');
//		SELECT COUNT(c.id) FROM  `php35_admin_role` a
//LEFT JOIN php35_role_privilege b ON a.role_id = b.role_id
//LEFT JOIN php35_privilege c ON b.pri_id = c.id
//WHERE CONCAT( c.module_name,  '/', c.controller_name,  '/', c.action_name ) =  'Admin/Goods/add'
//AND a.admin_id =2
		$has = $arModel->alias('a')->join('LEFT JOIN __ROLE_PRIVILEGE__ b ON a.role_id = b.role_id LEFT JOIN __PRIVILEGE__ c ON b.pri_id = c.id')->where("a.admin_id=$adminId AND CONCAT(c.module_name,'/',c.controller_name,'/',c.action_name)='$url'")->count();
		return ($has > 0);
	}
	/**
	 * 取出当前管理员所拥有的前两级的权限
	 */
	public function getTop2LevelPri()
	{
		// 获取管理员ID
		$adminId = session('admin_id');
		/******************* 取出当前管理员所拥有的权限 *********************/
		if($adminId == 1)
			$priData = $this->order('sort_num ASC')->select();  // 从数据库中取出所有的权限
		else 
		{
			$arModel = M('AdminRole');
			// 流程：先根据管理员ID取出所在的角色的ID，再根据角色的ID取出所拥有的权限的ID，再根据权限的ID取出权限数据
			$priData = $arModel->field('c.*')->alias('a')->join('LEFT JOIN __ROLE_PRIVILEGE__ b ON a.role_id = b.role_id LEFT JOIN __PRIVILEGE__ c ON b.pri_id = c.id')->where("a.admin_id=$adminId")->order('c.sort_num ASC')->select();
		}
		/******************** 从权限中挑出前两级的权限 *********************/
		// 定义一个用来放前两级权限的数组
		$data = array();
		// 循环所有的权限
		foreach ($priData as $k => $v)
		{
			if($v['parent_id'] == 0)  // 挑出顶级的
			{
				// 再循环所有的权限找出这个$v的子权限
				foreach ($priData as $k1 => $v1)
				{
					if($v1['parent_id'] == $v['id'])   // 挑出这个顶级的子级
						// 把子级放到顶级的children字段上再做一个数组
						$v['children'][] = $v1;
				}
				// 把这个顶级放到$data里
				$data[] = $v;
			}
		}
		return $data;
	}
}






















