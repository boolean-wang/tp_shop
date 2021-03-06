<?php
namespace Admin\Controller;
use Think\Controller;
class CategoryController extends Controller 
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Category');
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
		$parentModel = D('Category');
		$parentData = $parentModel->getTree();
		$this->assign('parentData', $parentData);

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '添加分类',
			'_page_btn_name' => '分类列表',
			'_page_btn_link' => U('lst?p='.I('get.p')),
			'_page_desc' => '添加分类',
		));
		$this->display();
    }
    public function edit()
    {
    	$id = I('get.id');
    	if(IS_POST)
    	{
    		$model = D('Category');
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
    	$model = M('Category');
    	$data = $model->find($id);
    	$this->assign('data', $data);
		$parentModel = D('Category');
		$parentData = $parentModel->getTree();
		$children = $parentModel->getChildren($id);
		$this->assign(array(
			'parentData' => $parentData,
			'children' => $children,
		));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '修改分类',
			'_page_btn_name' => '分类列表',
			'_page_btn_link' => U('lst?p='.I('get.p')),
			'_page_desc' => '修改分类',
		));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Category');
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
    	$model = D('Category');
		$data = $model->getTree();
    	$this->assign(array(
    		'data' => $data,
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '分类列表',
			'_page_btn_name' => '添加分类',
			'_page_btn_link' => U('add'),
			'_page_desc' => '分类列表',
		));
    	$this->display();
    }
}