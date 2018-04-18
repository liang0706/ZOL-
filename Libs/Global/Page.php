<?php
/**
* 强大的分页类
* 具有模板功能
* @example
*	$page = new Page(array('total'=>500));
*	echo $page->display(1);
*	echo '<br/>' . $page->display('{PREV:[上一页]}{NEXT:[下一页]}');
* @author wiki
* @copyright (c) 2009-4-1
*/
class Libs_Global_Page {

	protected $pageKey = 'page';

        protected $placeHd = '{PAGE}';

	/**
	* 模板
	* @var string
	*/
	protected $Template = '';

	/**
	* 当前页
	* @var integer
	*/
	protected $Page = 1;

	/**
	* 每页显示的数目
	* @var integer
	*/
	protected $RowNum = 20;

	/**
	* 总数
	* @var integer
	*/
	protected $Total = 0;

	/**
	* 总页数
	*/
	protected $TotalPage = 0;

	/**
	* 有没有页码Bar
	*/
	protected $existBar = false;

	/**
	* 当前页码焦点偏移量
	* @var integer
	*/
	protected $OffsetNum = 0;

	/**
	* 当面页码条中间显示宽
	* @var integer
	*/
	protected $BarLength = 0;

	protected $Url = '';

	private $isRewrite = false;#开头
	protected $target = '';

	/**
	* 系统标签
	*/
	protected $FirstTag   = 'FIRST';
	protected $PrevTag    = 'PREV';
	protected $PrevHdTag  = 'PREVHD';#上一页的引导符
	protected $BarTag     = 'BAR';
	protected $NextTag    = 'NEXT';
	protected $NextHdTag  = 'NEXTHD';#下一页的引导符
	protected $LasttTag   = 'LAST';
	protected $CurrentTag = 'CURRENT';
	protected $TotalTag   = 'TOTAL';

	/**
	* 页码标签
	*/
	protected $NumTag  = '[NUM]';

	protected $TagsVal = array();
	protected $Label   = array();
	/**
	* 标签分割符
	*
	*/
	protected $TagDelimiter = '|';

	/**
	* 属性-值分割符
	*/
	protected $AttrDelimiter = ':';

	/**
	* 处理模板表达式模板
	* {TAGS} = $this->Tag
	* {ATTRDELIMITER} = $this->AttrDelimiter
	*/
	protected $RegexTemplate = '{(({TAGS}){ATTRDELIMITER}([^}]+))}';

	protected  $Style = array(
					'FIRST'   => '',
					'PREV'    => '',
					'PREVHD'  => '',
					'BAR'     => '',
					'NEXT'    => '',
					'NEXTHD'  => '',
					'LAST'    => '',
					'CURRENT' => '',
					'TOTAL'   => '',
				);

	/**
	* 是否设置智能模式 自动处理各个标签显示
	*
	* @var mixed
	*/
	protected $SmartShow = array(
					'FIRST'   => true,
					'PREV'    => false,
					'PREVHD'  => true,
					'BAR'     => true,
					'NEXT'    => false,
					'NEXTHD'  => true,
					'LAST'    => true,
					'CURRENT' => true,
					'TOTAL'   => true,
				);

	#开始页码
	private $startPage;

	#结束页码
	private $endPage;

	private $startOver = true;#开头

	private $endOver = false;#结束

	private $startPageOver = true;#当前页是否是首页
	private $endPageOver   = false;#当前页是否是首尾

        private $isStatic   = false;#是否获取伪静态地址

	/**
	* @param array $opiton = array('page'=>1, 'rownum'=>20, 'total'=>542, 'template'=>'');
	*/
	public function __construct($option)
	{
		if (is_array($option)) {
			if (!array_key_exists('total', $option)) {
				return false;
			}
			$this->Total = intval($option['total']);
			isset($option['rownum'])     && ($this->RowNum = intval($option['rownum']));//当前页面显示多少条，默认20
			isset($option['offsetnum'])  && ($this->OffsetNum = intval($option['offsetnum']));//当前页码偏移量
			isset($option['barlength'])  && ($this->BarLength = intval($option['barlength']));//当面页码条中间显示宽
			isset($option['pagekey'])    && ($this->pageKey = $option['pagekey']);//当前页码
			isset($option['template'])   && ($this->Template = $option['template']);//模板
			isset($option['target'])     && ($this->target = $option['target']);//链接打开形式
			isset($option['isRewrite'])  && ($this->isRewrite = $option['isRewrite']);#开头
                        isset($option['isStatic'])  && ($this->isStatic = $option['isStatic']);#是否获取伪静态地址 默认false
			$page = isset($option['page']) ? (int)$option['page'] : 0;//当前页码
			$url = isset($option['url']) ? $option['url'] : $this->getCurUrl();//当前的连接

		} else {
			$this->Total = intval($option);
			$page = 0;
			$url = '';
		}

		if (!$this->Total) {
			return false;
		}
		$this->_setPage($page);
		$this->_setUrl($url);
		$this->TotalPage = ceil($this->Total/$this->RowNum);

		$this->startPageOver = ($page <= 1);
		$this->endPageOver = ($page >= $this->TotalPage);
	}

	/**
	* 设置样式
	* @return ZOL_Product_Lib_Page
	*/
	public function setStyle(array $style) {
		if (!$style) {
			return false;
		}
		$this->Style = array_merge($this->Style, $style);
		return $this;
	}

	/**
	* 设置标签显示模式
	* @return ZOL_Product_Lib_Page
	*/
	public function setSmartShow($smartShow) {
		$this->SmartShow = is_array($smartShow) ? array_merge($this->SmartShow, $smartShow) : $smartShow;
		return $this;
	}

	public function get($var)
	{
		if (property_exists($this, $var)) {
			return $this->$var;
		} else {
			//trow error
		}
	}

	/**
	* 设置属性值
	*/
	public function set($var, $value)
	{
		if (property_exists($this, $var)) {
			$this->$var = $value;
		} else {
			//trow error
		}
		return $this;
	}

	/**
	* 设置当前页
	*/
	protected function _setPage($page)
	{
		$page = (int)$page;

		if ($page>0) {
			$this->Page = $page;
		} else {
			$this->Page = (int)$_GET[$this->pageKey];
		}
	}

	/**
	* 设置URL头
	*/
	protected function _setUrl($url='')
	{

        if($this->isRewrite){
            $this->Url = $url;
            return true;
        }


        if (strpos($url, $this->placeHd) !== false) {
            $this->Url = $url;
            return true;
        }

		$queryString = '';

		if (!empty($url)) {
			if (($offset = strpos($url, '?')) !== false) {
				$queryString = substr($url, $offset+1);
				$url = substr($url, 0, $offset);
			}
		} else {
			$queryString = $_SERVER['QUERY_STRING'];
			$url = $_SERVER['PHP_SELF'];
		}

		if ($queryString) {
			parse_str($queryString, $query);
            unset($query[$this->pageKey]);

			if ($query) {
				$this->Url = $url . '?' . str_replace('&', '&amp;', http_build_query($query)) . '&amp;' . $this->pageKey . '=';
			} else {
				$this->Url = $url . '?' . $this->pageKey . '=';
			}
		} else {
			$this->Url = $url;
		}
        return true;
	}

	/**
	* 解析模板
	* @param string $code 模板内容
	*/
	protected function parseTemplate($code='')
	{
		$existBar = false;
		$label = array();
		$code || ($code = $this->Template);
		$tags[] = $this->FirstTag;
		$tags[] = $this->PrevTag;
		$tags[] = $this->PrevHdTag;
		$tags[] = $this->BarTag;
		$tags[] = $this->NextTag;
		$tags[] = $this->NextHdTag;
		$tags[] = $this->LasttTag;
		$tags[] = $this->CurrentTag;
		$tags[] = $this->TotalTag;

		$regex = str_replace(array('{TAGS}', '{ATTRDELIMITER}'),
							array(join('|', $tags), $this->AttrDelimiter),
							$this->RegexTemplate);
		preg_match_all("/{$regex}/s", $code, $result, PREG_SET_ORDER);
		foreach ($result as $re) {
			$_tags[$re[2]] = $re[3];
			//处理中间分页条部分
			if ($re[3] && strpos($re[3], $this->AttrDelimiter) && $re[2] == $this->BarTag) {
				$_result = explode($this->AttrDelimiter, $re[3]);
				$_tags[$re[2]]   = $_result[0];

				$this->BarLength || ($this->BarLength = $_result[1]);
				$this->OffsetNum || ($this->OffsetNum = $_result[2]);
				$existBar = true;
			}
			$label[$re[2]] = $re[0];
		}
		$this->existBar = $existBar;
		$this->Label = $label;
		$this->TagsVal = $_tags;

		$this->interval();
		return $_tags;
	}

	/**
	* 页码条区间
	*/
	private function interval()
	{
		if (!$this->existBar || $this->BarLength < 1) {
			return false;
		}

		if ($this->BarLength >= $this->TotalPage) {
			$start = 1;
			$end   = $this->TotalPage;
		} else {
			$start = $this->Page - $this->OffsetNum;
			$end   = $start + $this->BarLength;
			$end   = $start < 1 ? $this->BarLength : ($end - 1);

			$start = ($end > $this->TotalPage) ? ($start - ($end - $this->TotalPage)) : $start;

			$start = max($start, 1);
			$end   = min($end, $this->TotalPage);
		}

		$this->startPage = $start;
		$this->endPage   = $end;

		$this->startOver = ($start == 1);
		$this->endOver   = ($end == $this->TotalPage);
	}

	/**
	* 第一页
	*/
	public function firstTag($style='')
	{
		if (empty($this->TagsVal[$this->FirstTag])) {
			return false;
		}

		if ((!empty($this->SmartShow['FIRST']) || $this->SmartShow === true) && $this->startOver) {
			return false;
		}

		if ($this->startPageOver) {
			return false;
		}

		$style = isset($this->Style['FIRST']) ? $this->Style['FIRST'] : $style;


		$num = 1;
		if ($this->TotalPage==1) {
			return false;
		}
		$btn = $this->_getText($this->FirstTag, $num);

		if ($this->Page > 1) {
			return $this->_getLink($this->_getUrl($num), $btn, $style);
		}
		return $this->_getNoLink($btn, $style);
	}

	/**
	* 上一页
	*/
	public function prevTag($style='')
	{
		if (empty($this->TagsVal[$this->PrevTag])) {
			return false;
		}

		if ((!empty($this->SmartShow['PREV']) || $this->SmartShow === true) && $this->startOver) {

			return false;
		}

		if ($this->startPageOver) {
			return false;
		}

		$style = isset($this->Style['PREV']) ? $this->Style['PREV'] : $style;
		$num = $this->Page-1;
		$btn = $this->_getText($this->PrevTag, $num);
		if ($this->Page > 1) {
			return $this->_getLink($this->_getUrl($num), $btn, $style);
		}
		return $this->_getNoLink($btn, $style);
	}

	/**
	* 上一页引导符
	*/
	public function prevhdTag($style='')
	{
		if (empty($this->TagsVal[$this->PrevHdTag])) {
			return false;
		}

		if ((!empty($this->SmartShow['PREVHD']) || $this->SmartShow === true) && $this->startOver) {
			return false;
		}

		if ($this->startPageOver) {
			return false;
		}

		if ($this->startPage < 3) {
			return false;
		}

		$style = isset($this->Style['PREVHD']) ? $this->Style['PREVHD'] : $style;

		$num = $this->Page-1;
		$btn = $this->_getText($this->PrevHdTag, $num);
		return $this->_getNoLink($btn, $style);
	}

	/**
	* 当前分页条
	*/
	public function barTag($style='')
	{
		if (!$this->existBar || $this->BarLength < 1) {
			return false;
		}

		$style = isset($this->Style['BAR']) ? $this->Style['BAR'] : $style;

		$this->interval();

		$bar = '';

		for ($i = $this->startPage; $i <= $this->endPage; $i++) {
			if ($i > $this->TotalPage) {
				break;
			} elseif ($i < 1) {
				continue;
			}
			$btn = $this->_getText($this->BarTag, $i);
			if ($i == $this->Page) {
				$btn =  isset($this->Style['CURRENTSTR'])?str_replace("{{CURNUM}}",$btn,$this->Style['CURRENTSTR']):$btn;
				$bar .= $this->_getNoLink($btn, $this->Style['CURRENT'] ? $this->Style['CURRENT'] : 'sel');
			} else {
				$bar .= $this->_getLink($this->_getUrl($i), $btn, $style);
			}


		}
		return $bar;
	}
	/**
        * 获得当前URL
        */
	public function getCurUrl(){
		$curUrlArr  = parse_url($_SERVER['REQUEST_URI']);

		$queryUrl   = $curUrlArr['query'];
		$list       = explode('&', $queryUrl);
		$tempUrl    = array();
		if(!empty($list)){
			foreach ($list as $value){
				if(strstr($value,'page') || strstr($value,'PAGE')){
					continue;
				}
				$tempUrl[] = $value;
			}
		}
		$param_str = implode('&',$tempUrl);

        $tempUrl  = !empty($_SERVER['SCRIPT_URI']) ? $_SERVER['SCRIPT_URI'] : "index.php";

		if($param_str){
                        $curUrl = "{$tempUrl}?".$param_str.'&page={PAGE}';
		}else{
                        $curUrl = "{$tempUrl}?page={PAGE}";
		}
        return $curUrl;
	}
       /**
        * 获得当前Limit
        */
	public function getSqlLimit($page=1){

            $limit   = '';

            if($page > $this->TotalPage){
                $page=$this->TotalPage;
            }
            if((int)$page<=0){
                $page=1;
            }
            return array($this->RowNum*($page-1),$this->RowNum);
	}
	/**
	* 下一页
	*/
	public function nextTag($style='')
	{
		if (empty($this->TagsVal[$this->NextTag])) {
			return false;
		}

		$num = $this->Page+1;
		if ((!empty($this->SmartShow['NEXT']) || $this->SmartShow === true) && $this->endOver) {
			return false;
		}
		if ($this->endPageOver) {
			return false;
		}

		$style = isset($this->Style['NEXT']) ? $this->Style['NEXT'] : $style;
		$btn = $this->_getText($this->NextTag, $num);
		if ($this->Page < $this->TotalPage) {
			return $this->_getLink($this->_getUrl($num), $btn, $style);
		}
		return $this->_getNoLink($btn, $style);
	}

	/**
	* 下一页引导符
	*/
	public function nexthdTag($style='')
	{
		if (empty($this->TagsVal[$this->NextHdTag])) {
			return false;
		}

		$num = $this->Page+1;

		if ((!empty($this->SmartShow['NEXTHD']) || $this->SmartShow === true) && $this->endOver) {
			return false;
		}

		if ($this->endPageOver) {
			return false;
		}

		if ($this->endPage + 2 > $this->TotalPage) {
			return false;
		}

		$style = isset($this->Style['NEXTHD']) ? $this->Style['NEXTHD'] : $style;

		$btn = $this->_getText($this->NextHdTag, $num);
		return $this->_getNoLink($btn, $style);
	}


	/**
	* 最后一页
	*/
	public function lastTag($style='')
	{
		if (empty($this->TagsVal[$this->LasttTag])) {
			return false;
		}

		$num = $this->TotalPage;

		if ((!empty($this->SmartShow['LAST']) || $this->SmartShow === true) && $this->endOver) {
			return false;
		}

		if ($this->endPageOver) {
			return false;
		}

		$style = isset($this->Style['LAST']) ? $this->Style['LAST'] : $style;

		$btn = $this->_getText($this->LasttTag, $num);
		if ($this->Page < $this->TotalPage) {
			return $this->_getLink($this->_getUrl($num), $btn, $style);
		}
		return $this->_getNoLink($btn, $style);
	}

	/**
	* 当前页
	*/
	public function currentTag($style='')
	{
		if (empty($this->TagsVal[$this->CurrentTag])) {
			return false;
		}

		$style = isset($this->Style['CURRENT']) ? $this->Style['CURRENT'] : $style;

		$num = $this->Page;
		$btn = $this->_getText($this->TotalTag, $num);
		if ($style) {
			$btn = $this->_getNoLink($btn, $style);
		}

		return $btn;
	}

	/**
	* 总页
	*/
	public function totalTag($style='')
	{
		if (empty($this->TagsVal[$this->TotalTag])) {
			return false;
		}

		$style = isset($this->Style['TOTAL']) ? $this->Style['TOTAL'] : $style;

		$num = $this->TotalPage;
		$btn = $this->_getText($this->TotalTag, $num);
		if ($style) {
			$btn = $this->_getNoLink($btn, $style);
		}

		return $btn;
	}


	public function pageInfo($style='')
	{
		return '共<span class="' . $style . '">' . $this->Total . '</span>条 第<span class="' . $style . '">' . $this->Page . '</span>/' . $this->TotalPage . '页';
	}

	/**
	* 获取链接文字
	* @param string $tag 链接字符串
	* @param integer $page 页码值
	*/
	public function _getText($tag, $page='')
	{
		return isset($this->TagsVal[$tag])
				? str_replace($this->NumTag, $page, $this->TagsVal[$tag])
				: $page;
	}
	/**
	* 获取URL链接
	*/
	public function _getUrl($page=1)
	{
                if($this->isStatic) { //伪静态的情况
                    $pattern = '/(\d+)\.{1}/';
                    return preg_replace($pattern, $page.".", $this->Url);
                }

		if (strpos($this->Url, '{PAGE}') !== false) {
			return str_replace('{PAGE}', $page, $this->Url);
		}

		return $this->Url . $page;
	}
	/**
	* 获取链接按钮
	*/
	public function _getLink($url, $text, $style='')
	{
		$style = empty($style) ? '' : "class=\"{$style}\"";
		return '<a href="' . $url . '" ' . $style
			. ($this->target ? (' target="' . $this->target . '"') : '')
			. '>' . $text . '</a>';
	}

	public function _getNoLink($text, $style='')
	{
		$style = empty($style) ? '' : "class=\"{$style}\"";
		return '<span ' . $style . '>' . $text . '</span>';
	}


	public function display($mixed='')
	{
		$i = 0;
		if (is_string($mixed) && $mixed) {
			$code = $mixed;
		} elseif (is_integer($mixed)) {
			$i = $mixed;
		} else {
			$i = 1;
		}
		//默认几个
		if ($i || !$code) {
			switch ($i) {
				case 1:
					$code = '{FIRST:[NUM]...}{PREV:<}{BAR:[NUM]:10:3}{NEXT:>}{LAST:...[NUM]}';//DZ的
					break;
				case 2:
					$code = '{FIRST:[首页]}{PREV:[上页]}{NEXT:[下页]}{LAST:[尾页]}';//自定义的
					break;
				case 3:
					$code = '{PREV:[上一页]}{BAR:[[NUM]]:20:10}{NEXT:[下一页]}';//百度的
					break;
				case 4 :
					$code = '{PREV:&lt; 上一页}{PREVHD:...}{BAR:[NUM]:10:3}{NEXTHD:...}{NEXT:下一页 &gt;}{LAST:尾页}';//ZOL的
					break;
				case 5 :
					$code = '{PREV:上一页}{BAR:[NUM]:10:8}{NEXT:下一页}';//产品大全
					break;
                case 6 :    //列表页
					$code = '{PREV:<span class="pre">上一页</span>}{FIRST:<span>[NUM]</span>}{PREVHD:<span class="bgno">...</span>}{BAR:<span>[NUM]</span>:5:2}{NEXTHD:<span class="bgno">...</span>}{LAST:<span>[NUM]</span>}{NEXT:<span class="next">下一页&gt;</span>}';
					break;
                case 7 :    //列表页优化结构
					$code = '{PREV:&lt;上一页}{FIRST:[NUM]}{PREVHD:...}{BAR:[NUM]:5:2}{NEXTHD:...}{NEXT:下一页&gt;}';
					break;
                case 8 :    //最精简版本
					$code = "{PREV:上一页}<span class='pagenum'><b>{$this->Page}</b>/{TOTAL:[NUM]}</span>{NEXT:下一页&gt;}";
					break;
               case 9   :    //最精简版本
					$code = "<span class='pagenum'>{$this->Page}/{TOTAL:[NUM]}</span>{PREV:上一页}{NEXT:下一页&gt;}";
					break;
			}
		}
		$this->parseTemplate($code);

		foreach ($this->TagsVal as $tag=>$val) {
			$funckey = strtolower($tag) . 'Tag';

			$code = str_replace($this->Label[$tag], $this->$funckey(), $code);
		}
		return $code;
	}
}
