<?php
namespace Admin\Controller;
class GoodsController extends BaseController
{
	public function add()
	{
		// 2. 处理表单
		if(IS_POST)
		{
//			var_dump($_POST);die;
			// 如果30秒处理不完工就加上这个函数。设置脚本执行时间，0代表执行到结束
//			set_time_limit(0);
			// 3. 生成模型
			$model = D('Goods');
			// 4. 接收表单并且验证表单【表单验证的规则要自己在模型中定义】
			// 在调用createa方法之前，把时间转化成时间戳
			if(isset($_POST['promote_price']) && isset($_POST['promote_start_time']) && isset($_POST['promote_end_time']))
			{
				$_POST['promote_start_time'] = strtotime("{$_POST['promote_start_time']} 00:00:00");
				$_POST['promote_end_time'] = strtotime("{$_POST['promote_end_time']}");
			}
			if($model->create(I('post.'), 1))
			{
				// 5. 如果表单验证成功
				if($model->add())
				{
					$this->success('添加成功！', U('lst'));
					// 因为success 1秒 之后才会跳转，如果不停止后面的代码还会继续执行的
					exit;
				}
			}
			// 如果出错获取出错误的原因
			$error = $model->getError();
			// 打印错误信息并在3秒之后返回上一个页面
			$this->error($error);
		}
		// 取出所有的分类制作下拉框
		$catModel = D('Category');
		$catData = $catModel->getTree();
		// 取出所有的类型
    	$typeModel = M('Type');
    	$typeData = $typeModel->select();
    	// 取出所有的会员级别
    	$mlModel = M('MemberLevel');
    	$mlData = $mlModel->select();
    	
		$this->assign(array(
			'catData' => $catData,
			'typeData' => $typeData,
			'mlData' => $mlData,
		));
		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '添加商品',
			'_page_btn_name' => '商品列表',
			'_page_btn_link' => U('Admin/Goods/lst'),
			'_page_desc' => '添加商品',
		));
		// 1. 显示表单
		$this->display();
	}
	public function save()
	{
		$goodsId = I('get.id');
		// 判断当前管理员是否是超级管理员和是否拥有修改所有商品的权限
		$adminId = session('admin_id');
		$priModel = D('Privilege');
		if($adminId != 1 && !$priModel->hasPri('Admin/Goods/manageAll'))
		{
			$goodsModel = M('Goods');
			// 这件商品是否是这个管理员添加的
			$addId = $goodsModel->field('admin_id')->find($goodsId); // 取出这件商品添加人
			if($addId['admin_id'] != $adminId)
				$this->error('只能修改自己添加的商品！');
		}
		$model = D('Goods');
		if(IS_POST)
		{
			// 在调用createa方法之前，把时间转化成时间戳
			if(isset($_POST['promote_price']) && isset($_POST['promote_start_time']) && isset($_POST['promote_end_time']))
			{
				$_POST['promote_start_time'] = strtotime("{$_POST['promote_start_time']} 00:00:00");
				$_POST['promote_end_time'] = strtotime("{$_POST['promote_end_time']}");
			}
			if($model->create(I('post.'), 2))
			{
				// save()方法返回值是mysql_affected_rows受影响的条数，如果没有修改任何字段返回的是0，但0代表成功不应该是失败，只有返回FALSE时才算失败
				if(FALSE !== $model->save())
				{
					$this->success('修改成功！', U('lst'));
					exit;
				}
			}
			$error = $model->getError();
			$this->error($error);
		}
		/******************** 取修改的商品的信息 *********************/
		// 先把这件商品的信息取出来填到表单中
		$info = $model->find($goodsId);
		// 再取出当前商品相册中的图片，在表单中列出来,让用户修改
		$gpModel = M('GoodsPics');
		$gpData = $gpModel->where(array(
			'goods_id' => array('eq', $goodsId),
		))->select();
		// 取出所有的分类制作下拉框
		$catModel = D('Category');
		$catData = $catModel->getTree();
		// 取出这件商品所拥有的所有的扩展分类的ID
		$gcModel = M('GoodsCat');
		$ecatId = $gcModel->field('cat_id')->where(array(
			'goods_id' => array('eq', $goodsId),
		))->select();
		// 取出所有的会员级别
    	$mlModel = M('MemberLevel');
    	$mlData = $mlModel->select();
    	// 取出当前商品的会员价格
    	$mpModel = M('MemberPrice');
    	$mpData = $mpModel->where(array(
    		'goods_id' => array('eq', $goodsId),
    	))->select();
    	// 取出所有的类型
    	$typeModel = M('Type');
    	$typeData = $typeModel->select();
    	// 取出当前商品现在拥有的所有的属性
    	$gaModel = M('GoodsAttr');
    	$gaData = $gaModel->field('a.*,b.attr_name,b.attr_type,b.attr_option_value')->alias('a')->join('__ATTRIBUTE__ b ON a.attr_id=b.id')->where(array(
    		'a.goods_id' => array('eq', $goodsId)
    	))->order('a.attr_id ASC,a.id ASC')->select();
    	
		$this->assign(array(
			'info' => $info,
			'gpData' => $gpData,
			'catData' => $catData,
			'ecatId' => $ecatId,
			'mlData' => $mlData,
			'mpData' => $mpData,
			'typeData' => $typeData,
			'gaData' => $gaData,
		));
		
		$this->assign(array(
			'_page_title' => '修改商品',
			'_page_btn_name' => '商品列表',
			'_page_btn_link' => U('Admin/Goods/lst'),
			'_page_desc' => '修改商品',
		));
		$this->display();
	}
	// 商品列表页
	public function lst()
	{
		$model = D('Goods');
		$data = $model->search();
		$this->assign(array(
			'data' => $data['data'],
			'page' => $data['page'],
		));
		// 取出所有的分类制作下拉框
		$catModel = D('Category');
		$catData = $catModel->getTree();
		$this->assign(array(
			'catData' => $catData
		));
		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '商品列表',
			'_page_btn_name' => '添加商品',
			'_page_btn_link' => U('Admin/Goods/add'),
			'_page_desc' => '商品列表',
		));
		$this->display();
	}
	// 放入回收站
	public function trash()
	{
		$id = I('get.id');
		$model = D('Goods');
		if($model->trash($id) !== FALSE)
			$this->success('操作成功！');
		else 
			$this->error('操作失败，请重试！');
	}
	// 还原
	public function recycle()
	{
		$id = I('get.id');
		$model = D('Goods');
		if($model->recycle($id) !== FALSE)
			$this->success('操作成功！');
		else 
			$this->error('操作失败，请重试！');
	}
	// 彻底删除
	public function delete()
	{
		$id = I('get.id');
		$model = D('Goods');
		if($model->delete($id) !== FALSE)
			$this->success('操作成功！');
		else 
			$this->error('操作失败，请重试！');
	}
	public function trash_list()
	{
		$model = D('Goods');
		$data = $model->trash_list();
		$this->assign(array(
			'data' => $data['data'],
			'page' => $data['page'],
		));
		// 设置页面中的信息
		$this->assign(array(
			'_page_title' => '回收站',
			'_page_desc' => '回收站',
		));
		$this->display();
	}
	public function ajaxDelImage()
	{
		$picId = I('get.pic_id');
		$gpModel = M('GoodsPics');
		$gpImg = $gpModel->field('sm_image_url,image_url')->find($picId);
		if($gpImg)
		{
			deleteImage($gpImg); // 从硬盘上删除图片
			$gpModel->delete($picId);  // 把数据库中的记录删除掉
		}
	}
	// 处理AJAX请求
	public function ajaxGetAttr()
	{
		$typeId = I('get.type_id');
		$attrModel = M('Attribute');
		$attrData = $attrModel->where(array(
			'type_id' => array('eq', $typeId),
		))->select();
		echo json_encode($attrData);
	}
	public function gn()
	{
		$goodsId = I('get.id');
		if(IS_POST)
		{
			$gn = I('goods_number');
			$gaid = I('goods_attr_id');
			// 计算两个数组中比率是多少【一个库存量数字对应几个商品属性ID】
			$rate = count($gaid) / count($gn);
			$gnModel = M('GoodsNumber');
			// delete old data
			$gnModel->where(array(
				'goods_id' => array('eq', $goodsId),
			))->delete();
			$_i = 0;// 商品属性ID数组中要提取的ID的下标
			foreach ($gn as $k => $v)
			{
				$_attr = array(); // 存放每次提取出来的商品属性的ID
				// 从商品属性ID数组中提取出相应数量的商品属性ID
				for($i=0; $i<$rate; $i++)
					$_attr[] = $gaid[$_i++];
				// 升序
				sort($_attr);
				$_attr = implode('|', $_attr);
				$gnModel->add(array(
					'goods_id' => $goodsId,
					'goods_number' => $v,
					'goods_attr_id' => $_attr,
				));
			}
			$this->success('ok');
			exit;
		}
		// 取出当前商品所有单选的属性
		$gaModel = M('GoodsAttr');
		$_gaData = $gaModel->field('a.*,b.attr_name')->alias('a')->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')->where(array(
			'a.goods_id' => array('eq', $goodsId),
			'b.attr_type' => array('eq', 1),
		))->select();
		// 把二维转化成三维的：把attr_id相同的放到一起
		$gaData = array();
		foreach ($_gaData as $k => $v)
		{
			$gaData[$v['attr_id']][] = $v;
		}
		$gnModel = M('GoodsNumber');
		$gnData = $gnModel->where(array(
			'goods_id' => array('eq', $goodsId),
		))->select();
		// 设置页面中的信息
		$this->assign(array(
			'gaData' => $gaData,
			'gnData' => $gnData,
			'_page_title' => '库存量',
			'_page_btn_name' => '商品列表',
			'_page_btn_link' => U('Admin/Goods/lst'),
			'_page_desc' => '库存量',
		));
		$this->display();
	}
	public function ajaxDelAttr()
	{
		$gaid = I('get.gaid');
		// 要先判断这个商品属性有没有设置过库存量
		$gnModel = M('GoodsNumber');
		// SELECT CONCAT('|',`goods_attr_id`,'|') b FROM `php35_goods_number` HAVING b LIKE '%|19|%' LIMIT 1
		$_c = $gnModel->field("CONCAT('|',goods_attr_id,'|') b")->having("b LIKE '%|$gaid|%'")->find();
		if($_c)
		{
			exit(json_encode(array(
				'ok' => 0,
				'error' => '已经设置了库存量，无法删除！',
			)));
		}
		$gaModel = M('GoodsAttr');
		$gaModel->delete($gaid);
		exit(json_encode(array(
			'ok' => 1,
		)));
	}
}