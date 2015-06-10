<?php
namespace Admin\Controller;
class RoleController extends BaseController 
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Role');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('lst?p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	// 取出所有的权限
    	$priModel = D('Privilege');
    	$priData = $priModel->getTree();
    	$this->assign(array(
    		'priData' => $priData,
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '添加角色',
			'_page_btn_name' => '角色列表',
			'_page_btn_link' => U('lst?p='.I('get.p')),
			'_page_desc' => '添加角色',
		));
		$this->display();
    }
    public function edit()
    {
    	$id = I('get.id');
    	if(IS_POST)
    	{
    		$model = D('Role');
    		if($model->create(I('post.'), 2))
    		{
    			if($model->save() !== FALSE)
    			{
    				$this->success('修改成功！', U('lst', array('p' => I('get.p', 1))));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	$model = M('Role');
    	$data = $model->find($id);
    	$this->assign('data', $data);
    	// 取出所有的权限
    	$priModel = D('Privilege');
    	$priData = $priModel->getTree();
    	// 取出当前角色已经拥有的权限的ID并构造成一个一维数组
    	$rpModel = M('RolePrivilege');
    	$priId = $rpModel->field('GROUP_CONCAT(pri_id) pri_id')->where(array(
    		'role_id' => $id,
    	))->find();
    	$priId = explode(',', $priId['pri_id']);
    	$this->assign(array(
    		'priData' => $priData,
    		'priId' => $priId,
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '修改角色',
			'_page_btn_name' => '角色列表',
			'_page_btn_link' => U('lst?p='.I('get.p')),
			'_page_desc' => '修改角色',
		));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Role');
    	if($model->delete(I('get.id', 0)) !== FALSE)
    	{
    		$this->success('删除成功！', U('lst', array('p' => I('get.p', 1))));
    		exit;
    	}
    	else 
    	{
    		$this->error($model->getError());
    	}
    }
    public function lst()
    {
    	$model = D('Role');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '角色列表',
			'_page_btn_name' => '添加角色',
			'_page_btn_link' => U('add'),
			'_page_desc' => '角色列表',
		));
    	$this->display();
    }
}