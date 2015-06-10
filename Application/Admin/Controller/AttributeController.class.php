<?php
namespace Admin\Controller;
use Think\Controller;
class AttributeController extends Controller 
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Attribute');
    		if($model->create(I('post.'), 1))
    		{
    			if($id = $model->add())
    			{
    				$this->success('添加成功！', U('lst?type_id='.I('get.type_id').'&p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	
    	// 取出所有的类型
    	$typeModel = M('Type');
    	$typeData = $typeModel->select();

		// 设置页面中的信息
		$this->assign(array(
			'typeData' => $typeData,
			'_page_title' => '添加属性',
			'_page_btn_name' => '属性列表',
			'_page_btn_link' => U('lst?type_id='.I('get.type_id').'&p='.I('get.p')),
			'_page_desc' => '添加属性',
		));
		$this->display();
    }
    public function edit()
    {
    	$id = I('get.id');
    	if(IS_POST)
    	{
    		$model = D('Attribute');
    		if($model->create(I('post.'), 2))
    		{
    			if($model->save() !== FALSE)
    			{
    				$this->success('修改成功！', U('lst?type_id='.I('get.type_id').'&p='.I('get.p')));
    				exit;
    			}
    		}
    		$this->error($model->getError());
    	}
    	$model = M('Attribute');
    	$data = $model->find($id);
    	// 取出所有的类型
    	$typeModel = M('Type');
    	$typeData = $typeModel->select();
    	
    	$this->assign(array(
    		'data' => $data,
    		'typeData' => $typeData,
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '修改属性',
			'_page_btn_name' => '属性列表',
			'_page_btn_link' => U('lst?type_id='.I('get.type_id').'&p='.I('get.p')),
			'_page_desc' => '修改属性',
		));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Attribute');
    	if($model->delete(I('get.id', 0)) !== FALSE)
    	{
    		$this->success('删除成功！', U('lst?type_id='.I('get.type_id').'&p='.I('get.p')));
    		exit;
    	}
    	else 
    	{
    		$this->error($model->getError());
    	}
    }
    public function lst()
    {
    	$model = D('Attribute');
    	$data = $model->search();
    	// 取出所有的类型
    	$typeModel = M('Type');
    	$typeData = $typeModel->select();
    	
    	$this->assign(array(
    		'typeData' => $typeData,
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '属性列表',
			'_page_btn_name' => '添加属性',
			'_page_btn_link' => U('add?type_id='.I('get.type_id')),
			'_page_desc' => '属性列表',
		));
    	$this->display();
    }
}