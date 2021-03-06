<?php
namespace Admin\Controller;
class MemberController extends BaseController 
{
    public function add()
    {
    	if(IS_POST)
    	{
    		$model = D('Member');
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

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '添加会员',
			'_page_btn_name' => '会员列表',
			'_page_btn_link' => U('lst?p='.I('get.p')),
			'_page_desc' => '添加会员',
		));
		$this->display();
    }
    public function edit()
    {
    	$id = I('get.id');
    	if(IS_POST)
    	{
    		$model = D('Member');
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
    	$model = M('Member');
    	$data = $model->find($id);
    	$this->assign('data', $data);

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '修改会员',
			'_page_btn_name' => '会员列表',
			'_page_btn_link' => U('lst?p='.I('get.p')),
			'_page_desc' => '修改会员',
		));
		$this->display();
    }
    public function delete()
    {
    	$model = D('Member');
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
    	$model = D('Member');
    	$data = $model->search();
    	$this->assign(array(
    		'data' => $data['data'],
    		'page' => $data['page'],
    	));

		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '会员列表',
			'_page_btn_name' => '添加会员',
			'_page_btn_link' => U('add'),
			'_page_desc' => '会员列表',
		));
    	$this->display();
    }
}