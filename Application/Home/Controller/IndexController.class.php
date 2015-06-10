<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller 
{
	// 首页
	public function index()
	{
		$goodsModel = D('Goods');
		$catModel = D('Category');
		// 疯狂抢购
		$goods1 = $goodsModel->getPromotingGoods();
		$goods2 = $goodsModel->getHot();
		$goods3 = $goodsModel->getNew();
		$goods4 = $goodsModel->getRec();
		// 取出中间推荐的大类的数据
		$topCatData = $catModel->getTopRecCatData();
		$this->assign(array(
			'goods1' => $goods1,
			'goods2' => $goods2,
			'goods3' => $goods3,
			'goods4' => $goods4,
			'topCatData' => $topCatData,
		));
		// 先设置页面信息
		$this->assign(array(
			'_hide_nav_' => 0,
			'_page_title' => '首页',
			'_page_keywords' => '首页',
			'_page_description' => '首页',
		));
		$this->display();
	}
	// 商品详情页
	public function goods()
	{
		$goodsId = I('get.id');
		$goodsModel = D('Goods');
		$data = $goodsModel->goodsData($goodsId);
		
		if(!$data)
			$this->error('商品不存在或者已经被删除！');
		
		$this->assign(array(
			'data' => $data,
			'image_pre' => C('IMAGE_PREFIX'),
		));
		
		// 先设置页面信息
		$this->assign(array(
			'_hide_nav_' => 1,
			'_page_title' => '商品详情页-xxxxxxxx',
			'_page_keywords' => '商品详情页-xxxxxxxx',
			'_page_description' => '商品详情页-xxxxxxxx',
		));
		$this->display();
	}
	// 处理商品详情页中AJAX的三个数据
	public function ajaxGetGoodsData()
	{
		$ret = array();
		// 会员价格
		$goodsId = I('get.goods_id');
		$model = D('Goods');
		$ret['memberPrice'] = $model->getMemberPrice($goodsId);
		// 浏览历史  -> 下回分解
		$ret['viewHistory'] = '';
		// 浏览记录历史-》浏览了这件商品还浏览了。。。  -> 下回分解
		$ret['viewHistory'] = '';
		
		echo json_encode($ret);
	}
}













