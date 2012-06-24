<?php
/**
 * TaoAction.class.php
 * @copyright			copyright(c) 2011 - 2012 极好居
 * @author				anqiu xiao
 * @contact				QQ:89249294 E-mail:jihaoju@qq.com
 * @date				Fri Jun 15 14:40:24 CST 2012
 */
class TaoAction extends HomeCommonAction
{
    /**
     * 默认操作
     * 
     */
    public function index()
    {
    	$page = isset($_REQUEST['p']) && $_REQUEST['p'] >= 1 ? $_REQUEST['p'] : 1;
		$pageLimit = 10;
    	$cid = isset($_REQUEST['cid']) && $_REQUEST['cid'] ? intval($_REQUEST['cid']) : 0;
    	$t_type = isset($_REQUEST['t_type']) ? intval($_REQUEST['t_type']) : 0;
    	//分类
    	$categorys = TaoShopCategoryService::getAll();
    	$this->assign('categorys', $categorys);
    	$this->assign('cid', $cid);
    	
    	$params = array('cid' => $cid, 't_type'=>$t_type);
		$limit = array('begin'=>($page-1)*$pageLimit, 'offset'=>$pageLimit);
		$shopModel = D('TaoShop');
		$keys = array();
		$res = $shopModel->front($keys, $params, $limit);
		$this->assign('shops', $res['data']);
		$page_url = reUrl(MODULE_NAME."/".ACTION_NAME."?cid=$cid&p=[page]");
		$page_url = str_replace('%5bpage%5d', '[page]', $page_url);
		$p=new Page($page,
		$pageLimit,
		$res['count'],
		$page_url,
		5,
		5);
		$pagelink=$p->showStyle(3);
		$this->assign('pagelink', $pagelink);
		
    	$this->assign('page_title', '淘宝优惠券 - ' . $this->_CFG['site_title']);
    	$this->assign('page_keywords', $this->_CFG['site_keywords']);
    	$this->assign('page_description', $this->_CFG['site_description']);
    	$this->display();
    }
    
    public function show()
    {
    	$c_id = intval($_REQUEST['id']);
		$c_id or die('id invalid.');
		$ccModel = D('TaoCoupon');
		$detail = $ccModel->info($c_id);
		if(! $detail || $detail['is_active'] == 0){
			$this->error('该优惠券已下架，请选择商家其他的优惠券');
		}
		$fetch_limit_conf = CouponCodeConf::fetch_limit_conf();
		$ccmService = service('TaoShop');
		$shop = $ccmService->info($detail['s_id']);
		$localTimeObj = LocalTime::getInstance();
		$today = $localTimeObj->local_strtotime(date('Y-m-d 23:59:59'));
		if($detail['expiry_type'] == 1){
			$detail['expiry_timestamp'] = $detail['expiry'] + $this->_CFG['timezone']*3600;
			if(($detail['expiry'] - $today) == 0){
				$detail['expiry'] = 1;
			}else{
				$detail['expiry'] = ($detail['expiry'] - $today) > 0 ? ceil(($detail['expiry'] - $today)/(3600*24)) : 0;
			}
		}
		$title = '';
		if($detail['title']){
			$title .= $detail['title'];
		}else{
			$title .= $shop['title'];
			if($detail['c_type'] ==1){
				$title .= '满'.$detail['money_max'].'减'.$detail['money_reduce'].'元优惠券';
			}else{
				$title .= $detail['money_amount'] . '元代金券';
			}
		}
		if($detail['data']['seo_title']){
			$page_title = $detail['data']['seo_title'];
		}else{
			$page_title = '淘宝优惠券 - ' . $title;
		}
		$detail['title'] = $title;
		import('@.Com.Util.Ubb');
		$detail['data']['directions'] = Ubb::ubb2html($detail['data']['directions']);
		$detail['data']['fetch_limit'] = $fetch_limit_conf[$detail['data']['fetch_limit']];
		$this->assign('detail', $detail);
		$this->assign('shop', $shop);
    	$this->assign('page_title', $page_title . ' - ');
		$this->assign('page_keywords', $detail['data']['seo_keywords'] ? $detail['data']['seo_keywords'] : $this->_CFG['site_keywords']);
		$this->assign('page_description', $detail['data']['seo_desc'] ? $detail['data']['seo_desc'] : $this->_CFG['site_description']);
		$this->display();
    }
    
    public function out()
    {
    	$id = intval($_REQUEST['id']);
    	$ccmService = service('TaoShop');
		$shop = $ccmService->info($id);
		$shop or die('id invalid.');
		if ($shop['shop_click_url']){
			redirect($shop['shop_click_url']);
		}else{
			redirect('http://shop'.$shop['sid'].'.taobao.com');
		}
    }
    
    public function out_item()
    {
    	$item_id = intval($_REQUEST['item_id']);
		if ($_REQUEST['click_url']){
			redirect($_REQUEST['click_url']);
		}else{
			redirect($_REQUEST['item_url']);
		}
    }
}