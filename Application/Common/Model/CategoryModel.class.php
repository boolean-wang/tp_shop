<?php
namespace Common\Model;
use Think\Model;
class CategoryModel extends Model 
{
	protected $insertFields = array('cat_name','parent_id','sort_num','is_rec','seo_keyword','seo_description');
	protected $updateFields = array('id','cat_name','parent_id','sort_num','is_rec','seo_keyword','seo_description');
	protected $_validate = array(
		array('cat_name', 'require', '权限名称不能为空！', 1, 'regex', 3),
		array('cat_name', '1,30', '权限名称的值最长不能超过 30 个字符！', 1, 'length', 3),
		array('parent_id', 'number', '上级的ID，0:顶级必须是一个整数！', 2, 'regex', 3),
		array('sort_num', 'number', '排序的数字必须是一个整数！', 2, 'regex', 3),
		array('is_rec', 'number', '是否推荐到首页中间,1代表推荐必须是一个整数！', 2, 'regex', 3),
		array('seo_keyword', '1,150', 'SEO关键字-seo优化的值最长不能超过 150 个字符！', 2, 'length', 3),
		array('seo_description', '1,150', 'SEO描述-seo优化的值最长不能超过 150 个字符！', 2, 'length', 3),
	);
	/************************************* 递归相关方法 *************************************/
	public function getTree()
	{
		$data = $this->select();
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
		// 先找出所有的子分类
		$children = $this->getChildren($option['where']['id']);
		// 如果有子分类都删除掉
		if($children)
		{
			$children = implode(',', $children);
			$this->execute("DELETE FROM php35_category WHERE id IN($children)");
		}
	}
	// 获取导航上的数据
	public function getNavCat()
	{
		// 取出所有的分类
		$all = $this->select();
		$data = array();// 存放最终的数组
		foreach ($all as $k => $v)
		{
			if($v['parent_id'] == 0)
			{
				// 再找这个顶级的二级分类
				foreach ($all as $k1 => $v1)
				{
					if($v1['parent_id'] == $v['id'])
					{
						// 再找三级的
						foreach ($all as $k2 => $v2)
						{
							if($v2['parent_id'] == $v1['id'])
							{
								$v1['children'][] = $v2;
							}
						}
						$v['children'][] = $v1;
					}
				}
				$data[] = $v;
			}
		}
		return $data;
	}
	// 获取首页中间推荐的大类【一个大类是一楼】
	public function getTopRecCatData()
	{
		$gcModel = M('GoodsCat'); // 扩展分类表
		$goodsModel = D('Goods');
		$ret = array(); // 放最终的结果
		// 先取出所有的分类
		$all = $this->order('sort_num ASC')->select();
		// 从所有的分类中挑出被推荐的顶级分类
		foreach ($all as $k => $v)
		{
			if($v['parent_id'] == 0 && $v['is_rec'] == 1)
			{
				$rec_count = 0; // 找到了几个推荐的二级分类
				// 再从所有的分类出再挑出这个顶级分类的二级分类，并分别存在两个数组中，一个保存未被的，一个保存推荐了的
				foreach ($all as $k1 => $v1)
				{
					if($v1['parent_id'] == $v['id'])
					{
						// 把推荐的和未推荐的二级分类放到不同的字段中
						// 前四个推荐的放到rec_children，之后再推荐的也放到children字段中
						if($v1['is_rec'] == 1 && $rec_count < 4)
						{
							// 取出这个推荐的二级分类下的商品
							$subCatRecGoods = $goodsModel->getGoodsByCatId($v1['id'], 8, array(
								'is_best' => array('eq', 1),
							));
							// 把这个二级分类下的商品放到这个二级分类的good字段中
							$v1['goods'] = $subCatRecGoods;
							// 把这个推荐的二级分类放到顶级分类的rec_children字段中
							$v['rec_children'][] = $v1;
							$rec_count++;  // 找到一个推荐的二级分类就计一个数
						}
						else 
							$v['children'][] = $v1;
					}
				}
				/***************** 再取出这个顶级分类以就子分类下被推荐的8件商品【放在第一个框中的】 *****************/
				$topCatRecGoods = $goodsModel->getGoodsByCatId($v['id'], 8, array(
					'is_best' => array('eq', 1),
				));
				$v['goods'] = $topCatRecGoods;  // 存放顶级分类的goods字段中
				$ret[] = $v;
			}
		}
		return $ret;
	}
	// 获取上级分类
	public function getCatParentPath($catId, $parent_id)
	{
		static $ret = array();
		$parent = $this->field('id,cat_name,parent_id')->where(array(
			'id' => array('eq', $parent_id),
		))->find();
		$ret[] = $parent;
		if($parent['parent_id'] != 0)
			// 再找上级的上级
			$this->getCatParentPath($parent['id'], $parent['parent_id']);
		return $ret;
	}
}










