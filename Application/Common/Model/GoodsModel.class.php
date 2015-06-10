<?php
namespace Common\Model;
use Think\Model;
class GoodsModel extends Model
{
	// 是否使用表单的批量验证功能
	protected $patchValidate = FALSE;
	// 在添加时，表单中允许出现的字段
	protected $insertFields = array('goods_name','market_price','shop_price','is_on_sale','goods_desc','sort_num','cat_id','type_id','promote_price','promote_start_time','promote_end_time','is_new','is_hot','is_rec','is_best');
	// 在修改时，表单中允许出现的字段
	protected $updateFields = array('id','goods_name','market_price','shop_price','is_on_sale','goods_desc','sort_num','cat_id','type_id','promote_price','promote_start_time','promote_end_time','is_new','is_hot','is_rec','is_best');
	// 定义表单验证规则
	protected $_validate = array(
		array('cat_id', 'require', '商品分类不能为空！', 1, 'regex', 3),
		array('cat_id', '/^[1-9]\d*$/', '商品分类必须是大于0的整数！', 1, 'regex', 3),
		array('goods_name', 'require', '商品名称不能为空！', 1, 'regex', 3),
		array('market_price', 'require', '市场价格不能为空！', 1, 'regex', 3),
		array('shop_price', 'require', '市场价格不能为空！', 1, 'regex', 3),
		array('market_price', 'currency', '市场价格必须是货币格式！', 1, 'regex', 3),
		array('shop_price', 'currency', '本店价格必须是货币格式！', 1, 'regex', 3),
		array('is_on_sale', '0,1', '是否上架只能是1，0两个值！', 1, 'in', 3),
		array('sort_num', 'number', '排序数字必须是数字！', 1, 'regex', 3),
		array('type_id', 'number', '类型id必须是数字！', 2, 'regex', 3),
	);
	// 翻页、搜索、排序
	public function search($perPage = 15)
	{
		/********************* 搜索 ************************/
		$where = array('a.is_delete' => array('eq', 0)); // is_delete=0
		// 如果不是超级管理员并且没有能够管理所有商品的权限时，只能看到自己的
		$priModel = D('Privilege');
		$adminId = session('admin_id');
		if($adminId != 1 && !$priModel->hasPri('Admin/Goods/manageAll'))
			$where['a.admin_id'] = $adminId;
		// 根据商品分类的ID搜索商品
		$catId = I('get.cat_id');
		if($catId)
		{
			// 先获取当前这个分类所有子分类的id
			$catModel = D('Category');
			$children = $catModel->getChildren($catId);
			$children[] = $catId; // 把当前分类和子分类的ID都放到一起
			// 把ID转化成字符串
			$children = implode(',', $children);
			// 先取出这个扩展分类下所有的商品的ID
			$gcModel = M('GoodsCat');
			$gid = $gcModel->field('GROUP_CONCAT(goods_id) goods_id')->where(array(
				'cat_id' => array('in', $children),
			))->find();
			// 主分类或者扩展分类有这个分类的都要取出来
			$where['a.cat_id'] = array('EXP', "IN($children) OR a.id IN({$gid['goods_id']})");
		}
		// 商品名称搜索
		$goodsName = I('get.goods_name');
		if($goodsName)
			$where['a.goods_name'] = array('like', "%$goodsName%");  // goods_name like "%xx%'
		// 本店价格搜索
		$priceFrom = I('get.price_from');
		$priceTo = I('get.price_to');
		if($priceFrom && $priceTo)
			$where['a.shop_price'] = array('between', array($priceFrom, $priceTo));
		elseif ($priceFrom)
			$where['a.shop_price'] = array('egt', $priceFrom);
		elseif ($priceTo)
			$where['a.shop_price'] = array('elt', $priceTo);
		// 是否上架的搜索
		$isonsale = I('get.is_on_sale', -1);  // 如果没有传这个变量，默认是-1
		if($isonsale != -1)
			$where['a.is_on_sale'] = array('eq', $isonsale);
		/*********************** 排序 **********************/
		$orderby = 'id';
		$orderWay = 'desc';
		$od = I('get.od');
		if($od)
		{
			if($od == 'id_asc')
				$orderWay = 'asc';
			elseif ($od == 'price_asc')
			{
				$orderby = 'a.shop_price';
				$orderWay = 'asc';
			}
			elseif ($od == 'price_desc')
				$orderby = 'a.shop_price';
		}
		/************************ 翻页 ********************/
		// 取出总的记录数
		$count = $this->alias('a')->where($where)->count();
		$page = new \Think\Page($count, $perPage);
		$page->setConfig('next', '下一页');
		$page->setConfig('prev', '上一页');
		$pageString = $page->show(); // 获取翻页用的字符串，这个是显示在页面中的
		$offset = $page->firstRow;
		/*********************** 取数据 **********************/
		$data = $this->field('a.*,COUNT(b.id) pic_count,GROUP_CONCAT(b.sm_image_url) image_url,SUM(c.goods_number) gn')->alias('a')->join('LEFT JOIN __GOODS_PICS__ b ON a.id=b.goods_id LEFT JOIN __GOODS_NUMBER__ c ON a.id=c.goods_id')->where($where)->order("sort_num ASC,$orderby $orderWay")->group('a.id')->limit("$offset,$perPage")->select();
		return array(
			'data' => $data,
			'page' => $pageString,
		);
	}
	// 这个方法在add方法插入数据之前会自动执行
	// $data：表单中的数据
	protected function _before_insert(&$data, $option)
	{
		$data['admin_id'] = session('admin_id');
		$data['goods_desc'] = clearXSS($_POST['goods_desc']);
		// 如果上传了图片才执行以下代码
		if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0)
		{
			$IMAGE_SAVE_PATH = C('IMAGE_SAVE_PATH');
			/*** 上传图片的代码 ***/
			$upload = new \Think\Upload(array(
				'maxSize' => 3145728,
				'rootPath' => $IMAGE_SAVE_PATH,
				'exts' => array('jpg', 'gif', 'png', 'jpeg', 'pjpeg', 'bmp'),
				'savePath' => 'Goods/',
			));
		    // 只上传logo这张图片 
		    $info   =   $upload->upload(array(
		    	'logo' => $_FILES['logo']
		    ));
		    if(!$info)
		    {
		    	// 先把错误信息给模型中error属性，之后返回控制器之后在控制器中会再调用这个模型中getError获取到这个错误信息
		    	$this->error = $upload->getError();
		    	return FALSE;  // 返回到控制器
		    }
		    // 上传成功之后把图片路径保存到logo字段中
		    $data['logo'] = $info['logo']['savepath'] . $info['logo']['savename']; 
		    // 拼出缩略图的文件名
		    $data['sm_logo'] = $info['logo']['savepath'] . 'sm_' . $info['logo']['savename']; 
		    // 生成缩略图
		    $image = new \Think\Image(); 
		    // 打开要处理的图片
		    $image->open($IMAGE_SAVE_PATH.$data['logo']);
		    // 生成缩略图
		    $image->thumb(150, 150, 1)->save($IMAGE_SAVE_PATH.$data['sm_logo']);
		}
	}
	protected function _before_update(&$data, $option)
	{
		// 如果没有推荐就设置成0
		if(!isset($data['is_hot']))
			$data['is_hot'] = 0;
		if(!isset($data['is_new']))
			$data['is_new'] = 0;
		if(!isset($data['is_rec']))
			$data['is_rec'] = 0;
		if(!isset($data['is_best']))
			$data['is_best'] = 0;
			
		if(isset($data['goods_desc']))
			$data['goods_desc'] = clearXSS($_POST['goods_desc']);
		// 如果上传了图片才执行以下代码
		if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0)
		{
			/*************** 删除原图片 ******************/
			$oldLogo = $this->field('sm_logo,logo')->find($option['where']['id']);
			if($oldLogo)
				deleteImage($oldLogo);
			$IMAGE_SAVE_PATH = C('IMAGE_SAVE_PATH');
			/*** 上传图片的代码 ***/
			$upload = new \Think\Upload(array(
				'maxSize' => 3145728,
				'rootPath' => $IMAGE_SAVE_PATH,
				'exts' => array('jpg', 'gif', 'png', 'jpeg', 'pjpeg', 'bmp'),
				'savePath' => 'Goods/',
			));
		    // 只上传logo这张图片 
		    $info   =   $upload->upload(array(
		    	'logo' => $_FILES['logo']
		    ));
		    if(!$info)
		    {
		    	// 先把错误信息给模型中error属性，之后返回控制器之后在控制器中会再调用这个模型中getError获取到这个错误信息
		    	$this->error = $upload->getError();
		    	return FALSE;  // 返回到控制器
		    }
		    // 上传成功之后把图片路径保存到logo字段中
		    $data['logo'] = $info['logo']['savepath'] . $info['logo']['savename']; 
		    // 拼出缩略图的文件名
		    $data['sm_logo'] = $info['logo']['savepath'] . 'sm_' . $info['logo']['savename']; 
		    // 生成缩略图
		    $image = new \Think\Image(); 
		    // 打开要处理的图片
		    $image->open($IMAGE_SAVE_PATH.$data['logo']);
		    // 生成缩略图
		    $image->thumb(150, 150, 1)->save($IMAGE_SAVE_PATH.$data['sm_logo']);
		}
		/********************************** 修改相册中的图片 **************************/
		$has = 0;  // 默认认为没有图片
		// 判断有没有上传图片
		foreach ($_FILES['image']['error'] as $v)
		{
			if($v == 0)
			{
				$has = 1;
				break ; // 不用再循环判断了，只要找到一个就算有
			}
		}
		// 表单中有图片就上传
		if($has == 1)
		{
			$IMAGE_SAVE_PATH = C('IMAGE_SAVE_PATH');
			/*** 上传图片的代码 ***/
			$upload = new \Think\Upload(array(
				'maxSize' => 3145728,
				'rootPath' => $IMAGE_SAVE_PATH,
				'exts' => array('jpg', 'gif', 'png', 'jpeg', 'pjpeg', 'bmp'),
				'savePath' => 'Goods/',
			));
		    // 上传名称为 image 的图片 
		    $info   =   $upload->upload(array(
		    	'image' => $_FILES['image']
		    ));
		    $gpModel = M('GoodsPics');  // 生成相册表模型
		    $image = new \Think\Image(); 
		    // 循环每张图片并生成缩略图
		    foreach ($info as $k => $v)
		    {
		    	$oname = $v['savepath'].$v['savename'];   // 原图名称
		    	$tname = $v['savepath'].'sm_'.$v['savename']; // 缩略图名称
		    	$image->open($IMAGE_SAVE_PATH.$oname);
		    	$image->thumb(150, 150, 1)->save($IMAGE_SAVE_PATH.$tname);	
		    	// 插入到商品相册表中
		    	$gpModel->add(array(
		    		'image_url' => $oname,
		    		'sm_image_url' => $tname,
		    		'goods_id' => $option['where']['id'], // 商品的ID
		    	));
		    }
		}
		/********************* 处理扩展分类 ******************/
		$ecatId = I('ext_cat_id');
		// 先删除原数据
		$gcModel = M('GoodsCat');
		$gcModel->where(array(
			'goods_id' => $option['where']['id'],
		))->delete();
		if($ecatId)
		{
			foreach ($ecatId as $k => $v)
			{
				if($v == 0)
					continue ;
				$gcModel->add(array(
					'goods_id' => $option['where']['id'],
					'cat_id' => $v,
				));
			}
		}
		/**
		 * ********** 处理会员价格××××××××××××××××
		 */
		$mp = I('post.mp');
		if($mp)
		{
			$mpModel = M('MemberPrice');
			// 先删除原数据
			$mpModel->where(array(
				'goods_id' => $option['where']['id'],
			))->delete();
			foreach ($mp as $k => $v)
			{
				if(empty($v)) continue ; // 没有价格就不处理这条
				$mpModel->add(array(
					'goods_id' => $option['where']['id'],
					'level_id' => $k,
					'level_price' => $v,
				));
			}
		}
		/******************* 处理商品属性 *************************/
		// 先处理添加的新属性
		$attrValue = I('post.attr_value');
		$attrPrice = I('post.attr_price');
		$gaModel = M('GoodsAttr');
		foreach ($attrValue as $k => $v)
		{
			foreach ($v as $k1 => $v1)
			{
				// 找出对应的价格
				if(isset($attrPrice[$k][$k1]))
					$_price = $attrPrice[$k][$k1];
				else 
					$_price = '';
				$gaModel->add(array(
					'goods_id' => $option['where']['id'],
					'attr_id' => $k,
					'attr_value' => $v1,
					'attr_price' => $_price,
				));
			}
		}
		// 处理旧属性
		$oattrValue = I('post.old_attr_value');
		$oattrPrice = I('post.old_attr_price');
		foreach ($oattrValue as $k => $v)
		{
			foreach ($v as $k1 => $v1)
			{
				// 找出对应的价格
				if(isset($oattrPrice[$k][$k1]))
					$_price = $oattrPrice[$k][$k1];
				else 
					$_price = '';
				$gaModel->where(array('id' => array('eq', $k1)))->save(array(
					'attr_value' => $v1,
					'attr_price' => $_price,
				));
			}
		}
	}
	// $data ： 插入数据库之后的数据，其中$data['id']就是新插入的商品的ID
	protected function _after_insert($data, $option)
	{
		$has = 0;  // 默认认为没有图片
		// 判断有没有上传图片
		foreach ($_FILES['image']['error'] as $v)
		{
			if($v == 0)
			{
				$has = 1;
				break ; // 不用再循环判断了，只要找到一个就算有
			}
		}
		// 表单中有图片就上传
		if($has == 1)
		{
			$IMAGE_SAVE_PATH = C('IMAGE_SAVE_PATH');
			/*** 上传图片的代码 ***/
			$upload = new \Think\Upload(array(
				'maxSize' => 3145728,
				'rootPath' => $IMAGE_SAVE_PATH,
				'exts' => array('jpg', 'gif', 'png', 'jpeg', 'pjpeg', 'bmp'),
				'savePath' => 'Goods/',
			));
		    // 上传名称为 image 的图片 
		    $info   =   $upload->upload(array(
		    	'image' => $_FILES['image']
		    ));
		    $gpModel = M('GoodsPics');  // 生成相册表模型
		    $image = new \Think\Image(); 
		    // 循环每张图片并生成缩略图
		    foreach ($info as $k => $v)
		    {
		    	$oname = $v['savepath'].$v['savename'];   // 原图名称
		    	$tname = $v['savepath'].'sm_'.$v['savename']; // 缩略图名称
		    	$image->open($IMAGE_SAVE_PATH.$oname);
		    	$image->thumb(150, 150, 1)->save($IMAGE_SAVE_PATH.$tname);	
		    	// 插入到商品相册表中
		    	$gpModel->add(array(
		    		'image_url' => $oname,
		    		'sm_image_url' => $tname,
		    		'goods_id' => $data['id'], // 商品的ID
		    	));
		    }
		}
		/**************** 处理扩展分类 ********************/
		$ecatId = I('ext_cat_id');
		if($ecatId)
		{
			$gcModel = M('GoodsCat');
			foreach ($ecatId as $k => $v)
			{
				if($v == 0)
					continue ;
				$gcModel->add(array(
					'goods_id' => $data['id'],
					'cat_id' => $v,
				));
			}
		}
		/******************* 处理商品属性 *************************/
		$attrValue = I('post.attr_value');
		$attrPrice = I('post.attr_price');
		$gaModel = M('GoodsAttr');
		foreach ($attrValue as $k => $v)
		{
			foreach ($v as $k1 => $v1)
			{
				// 找出对应的价格
				if(isset($attrPrice[$k][$k1]))
					$_price = $attrPrice[$k][$k1];
				else 
					$_price = '';
				$gaModel->add(array(
					'goods_id' => $data['id'],
					'attr_id' => $k,
					'attr_value' => $v1,
					'attr_price' => $_price,
				));
			}
		}
		/**
		 * ********** 处理会员价格××××××××××××××××
		 */
		$mp = I('post.mp');
		if($mp)
		{
			$mpModel = M('MemberPrice');
			foreach ($mp as $k => $v)
			{
				if(empty($v)) continue ; // 没有价格就不处理这条
				$mpModel->add(array(
					'goods_id' => $data['id'],
					'level_id' => $k,
					'level_price' => $v,
				));
			}
		}
	}
	public function recycle($goodsId)
	{
		return $this->where(array(
			'id' => array('eq', $goodsId),
		))->setField('is_delete', 0);
	}
	public function trash($goodsId)
	{
		// 当调用setField时_before_update会先被调用
		return $this->where(array(
			'id' => array('eq', $goodsId),
		))->setField('is_delete', 1);
	}
	public function trash_list($perPage = 15)
	{
		$where = array('is_delete' => array('eq', 1));
		$count = $this->where($where)->count();
		$page = new \Think\Page($count, $perPage);
		$page->setConfig('next', '下一页');
		$page->setConfig('prev', '上一页');
		$pageString = $page->show(); // 获取翻页用的字符串，这个是显示在页面中的
		$offset = $page->firstRow;
		/*********************** 取数据 **********************/
		$data = $this->where($where)->limit("$offset,$perPage")->select();
		return array(
			'data' => $data,
			'page' => $pageString,
		);
	}
	public function _before_delete($data)
	{
		$deleteImage = array();  // 要删除的图片路径
		// 在删除商品之前先把图片都删除掉
		$logo = $this->field('sm_logo,logo')->find($data['where']['id']);
		if($logo)
		{
			// 先把图片的路径放到这个数组中最后一起删除
			$deleteImage[] = $logo['sm_logo'];
			$deleteImage[] = $logo['logo'];
		}
		// 再删除相册中的图片
		$gpModel = M('GoodsPics');
		$image = $gpModel->field('image_url,sm_image_url')->where(array(
			'goods_id' => array('eq', $data['where']['id'])
		))->select();
		// 如果相册中有图片就删除
		if($image)
		{
			foreach ($image as $v)
			{
				// 先把图片的路径放到这个数组中最后一起删除
				$deleteImage[] = $v['image_url'];
				$deleteImage[] = $v['sm_image_url'];
			}
			// 删除相册表中对应的记录
			$gpModel->where(array(
				'goods_id' => array('eq', $data['where']['id'])
			))->delete();
		}
		// 如果有图片就从硬盘上删除
		if($deleteImage)
			deleteImage($deleteImage);
		// 把商品分类表中对应的数据也删除掉
		$gcModel = M('GoodsCat');
		$gcModel->where(array(
			'goods_id' => array('eq', $data['where']['id'])
		))->delete();
		// 把这件商品对应的会员价格也删除掉
		$mpModel = M('MemberPrice');
		$mpModel->where(array(
			'goods_id' => array('eq', $data['where']['id'])
		))->delete();
		// 把这件商品对应的商品属性也删除掉
		$gaModel = M('GoodsAttr');
		$gaModel->where(array(
			'goods_id' => array('eq', $data['where']['id'])
		))->delete();
	}
	// 获取当前正在促销中的商品 -》　疯狂抢购
	public function getPromotingGoods($limit = 5)
	{
		$now = time();
		return $this->field('id,goods_name,promote_price price,sm_logo')->where(array(
			'is_delete' => array('eq', 0),
			'is_on_sale' => array('eq', 1),
			'promote_start_time' => array('elt', $now),
			'promote_end_time' => array('egt', $now),
		))->order('sort_num ASC')->limit($limit)->select();
	}
	public function getHot($limit = 5)
	{
		return $this->field('id,goods_name,shop_price price,sm_logo')->where(array(
			'is_delete' => array('eq', 0),
			'is_on_sale' => array('eq', 1),
			'is_hot' => array('eq', 1),
		))->order('sort_num ASC')->limit($limit)->select();
	}
	public function getNew($limit = 5)
	{
		return $this->field('id,goods_name,shop_price price,sm_logo')->where(array(
			'is_delete' => array('eq', 0),
			'is_on_sale' => array('eq', 1),
			'is_new' => array('eq', 1),
		))->order('sort_num ASC')->limit($limit)->select();
	}
	public function getRec($limit = 5)
	{
		return $this->field('id,goods_name,shop_price price,sm_logo')->where(array(
			'is_delete' => array('eq', 0),
			'is_on_sale' => array('eq', 1),
			'is_rec' => array('eq', 1),
		))->order('sort_num ASC')->limit($limit)->select();
	}
	/**
	 * 获取一个分类下所有的商品【考虑子分类和扩展分类】
	 *
	 * @param unknown_type $catId
	 * @param unknown_type $limit
	 * @param unknown_type $limit ： 额外的where条件
	 */
	public function getGoodsByCatId($catId, $limit, $extraWhere = array())
	{
		$catModel = D('Category');
		// 先取出这个分类所有子分类的ID
		$children = $catModel->getChildren($catId);
		// 所分类和子分类的ID都放到一起
		$children[] = $catId;  // 1,2,3,4,5,7
		$children = implode(',', $children);
		// 先取出扩展分类在这些分类下的商品的ID
		$gcModel = M('GoodsCat');
		$extCatGoodsId = $gcModel->field('GROUP_CONCAT(DISTINCT goods_id) gid')->where(array(
			'cat_id' => array('in', $children),
		))->find();
		// 如果有扩展分类下的商品ID就拼成一个OR的SQL语句
		if($extCatGoodsId['gid'])
			$extCatGoodsId = " OR id IN({$extCatGoodsId['gid']})";
		// 取出主分类和扩展分类都在这些分类ID下的推荐了的商品
		$where = array(
			'cat_id' => array('exp', "IN($children) $extCatGoodsId"),  // 主分类和扩展分类下的商品
			'is_on_sale' => array('eq', 1),
			'is_delete' => array('eq', 0),
		);
		// 如果有额外的条件，就合并到一起
		if($extraWhere)
			$where = array_merge($where, $extraWhere);
		return $this->field('id,goods_name,sm_logo,shop_price')->where($where)->limit($limit)->order('sort_num ASC')->select();
	}
	/**
	 * 根据商品ID获取商品详情页信息
	 */
	public function goodsData($goodsId)
	{
		/******************************* 商品基本信息 **************************/
		// 先取出商品的详情信息和主分类的信息
		$field = 'a.id,a.goods_name,a.shop_price,a.market_price,a.cat_id,a.logo,a.sm_logo,a.goods_desc,b.parent_id,b.cat_name';
		$info = $this->alias('a')->field($field)->join('LEFT JOIN __CATEGORY__ b ON a.cat_id=b.id')->where(array(
			'a.id' => array('eq', $goodsId),
			'a.is_delete' => array('eq', 0),
			'a.is_on_sale' => array('eq', 1),
		))->find();
		if(!$info)
			return FALSE;
		/************************** 取导航条 *************************/
		// 获取主分类所有的上级分类
		$catModel = D('Category');
		$parentCat = $catModel->getCatParentPath($info['cat_id'], $info['parent_id']);
		// 根据上级分类制作面包屑导航
		krsort($parentCat);
		$position = '';
		foreach ($parentCat as $k => $v)
		{
			$position .= "<a href='".U('search?cat_id='.$v['id'])."'>{$v['cat_name']}</a> > ";
		}
		$position .= '<a href="'.U('search?cat_id='.$info['cat_id']).'">'.$info['cat_name'].'</a> > ' . $info['goods_name'];
		/************************ 商品相关分类【和商品主分类同级的分类】 *******************************/
		$relCat = $catModel->where(array(
			'parent_id' => array('eq', $info['parent_id']),
			'id' => array('neq', $info['cat_id']),
		))->select();
		/************************ 取出商品相册中的图片 *******************************/
		$gpModel = M('GoodsPics');
		$gpData = $gpModel->field('image_url')->where(array(
			'goods_id' => array('eq', $goodsId),
		))->select();
		/********************** 获取商品唯一的属性和单选的属性 *************************/
		$_radAttr = $this->getGoodsRadioAttr($goodsId);
		// 循环这个数组：把属性相同的放到一起
		$radAttr = array();
		foreach ($_radAttr as $k => $v)
		{
			$radAttr[$v['attr_id']][] = $v;
		}
		$uniAttr = $this->getGoodsUniqueAttr($goodsId);
		
		return array(
			'info' => $info,
			'position' => $position,
			'relCat' => $relCat,
			'gpData' => $gpData,
			'uniAttr' => $uniAttr,
			'radAttr' => $radAttr,
		);
	}
	// 获取商品单选属性
	public function getGoodsRadioAttr($goodsId)
	{
		$gaModel = M('GoodsAttr');
		$field = 'a.*,b.attr_name';
		return $gaModel->field($field)->alias('a')
		->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')
		->where(array(
			'a.goods_id' => array('eq', $goodsId),
			'b.attr_type' => array('eq', 1),
		))->select();
	}
	// 获取商品唯一属性
	public function getGoodsUniqueAttr($goodsId)
	{
		$gaModel = M('GoodsAttr');
		$field = 'a.*,b.attr_name';
		return $gaModel->field($field)->alias('a')
		->join('LEFT JOIN __ATTRIBUTE__ b ON a.attr_id=b.id')
		->where(array(
			'a.goods_id' => array('eq', $goodsId),
			'b.attr_type' => array('eq', 0),
		))->select();
	}
	// 获取一个商品的会员价格
	public function getMemberPrice($goodsId)
	{
		$memberId = session('id');
		// 如果没有登录直接返回本店价
		$goodsModel = M('Goods');
		$price = $goodsModel->field('shop_price')->find($goodsId);
		if($memberId)
		{
			// 如果登录了，根据当前会员的积分计算当前会员所在的级别ID和折扣率
			$memberModel = M('Member');
			$levelInfo = $memberModel->field('b.id,b.price_rate')->alias('a')
			->join('LEFT JOIN __MEMBER_LEVEL__ b ON (a.jifen BETWEEN b.low_num AND b.high_num)')
			->where(array(
				'a.id' => array('eq', $memberId),
			))->find();
			if($levelInfo)
			{
				// 再查询这件商品在当前这个级别有没有设置价格
				$mpModel = M('MemberPrice');
				$mpData = $mpModel->field('level_price')->where(array(
					'goods_id' => $goodsId,
					'level_id' => $levelInfo['id'],
				))->find();
				if($mpData)
					// 如果这个级别设置了价格就直接返回
					return $mpData['level_price'];
				else 
				{
					// 如果没有设置会员价格就用哲率乘上本店价格
					return $price['shop_price'] * $levelInfo['price_rate'] / 100;
				}
			}
		}
		return $price['shop_price'];
	}
}





















