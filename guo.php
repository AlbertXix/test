<?php
    //二级域名站群程序
    error_reporting(E_ALL^E_NOTICE);
    require_once 'config.php';//数据库配置文件
    
    //自动加载类
    function __autoload($className) {
        require_once './include/'.strtolower($className).'.class.php';
    }
    //常量定义
    define("ARTICLEURL", "./data/article.txt");//文章源
    define("KEYWORDSURL", "./data/keywords.txt");//关键词表
    define("TPLURL", "./templets/");//模板源
    define("HTMLPATH", "./html/");//html文件存放文件夹
    define("IPTXT", "./data/ip.txt");//IP
    define("PORTTXT", "./data/port.txt");//端口
    define("NEWPORT", "./data/newport.txt");//新增端口
    define("IMGURL", "./images/dedecms/pic/");//图片地址
    define("ARTICLETITLE", "./data/article_title.txt");//文章标题
    define("WEBTITLE", "./data/webtitle.txt");//网站标题
    
    //$host = $_SERVER[HTTP_HOST];//获取当前主机名
    $host = "18.do1.com";
	$kwd_dir = file(KEYWORDSURL);//读取关键词
	$gjcnum = count($kwd_dir);//关键词总数
	$article_title_arr = file(ARTICLETITLE);//文章标题
	$arcnum = count($article_title_arr);//标题总数
	$a = new Article(ARTICLEURL);//获取文章
	$contents = $a->getArticle(5);//随机获取文章，其中的[数字参数]为需要获取的段落数
	if(!isset($_GET['page']) && !isset($_GET['html'])){
		$b = new Article(ARTICLEURL);//获取文章
		$contents2 = $b->getArticle2();
    	$contents = $contents2;
    }
    
    $db = new mysqli($fz_dbhost, $fz_dbuser, $fz_dbpwd, $fz_dbname);//创建数据库对象
    if(mysqli_connect_errno()){
    	echo "<font color='#FF0000'>数据库连接失败</font>";
    	exit;
    }
    $db->select_db($fz_dbname);//选择数据库
    $db->query("set names $fz_charset");//设置字符编码
    $sql_host = "SELECT * FROM `zq_listurl` WHERE `host`='".$host."'";//查询域名是否访问过
    $result = $db->query($sql_host);
    if(($result->num_rows > 0) && !isset($_GET['page']) && !isset($_GET['html'])){
        $row = $result->fetch_assoc();//资源数组化
    	if(file_exists($row['url'])){
        	$newpage = @file_get_contents($row['url']);
        	$change_zz = "/{changelink}/i";
        	preg_match_all($change_zz, $newpage, $change_arr);
        	$change_count = count($change_arr[0]);//锚文本个数
        	for($i=0; $i<$change_count; $i++){
        		$rand_key = mt_rand(0, $arcnum-1);
        		$arc_kwd = trim($article_title_arr[$rand_key]);
        		$change_link = "<a href='/views-".$arc_kwd."-xBD".$rand_key.".html' target='_blank'>".$arc_kwd."</a>";
        		$newpage = preg_replace($change_zz, $change_link, $newpage, 1);//锚文本替换
        	}
        	echo $newpage;
        }else{
        	$sql_del = "DELETE FROM `zq_listurl` WHERE `url`='".$row['url']."'";
        	$result = $db->query($sql_del);
        	goto a;
        }
    }else{
    	a:
        //获取模板
        if(isset($_GET['page'])){
        	$page = $_GET['page'];//Create short name
        	$sql_tpl = "SELECT * FROM `zq_listurl` WHERE `host`='".$host."'";
        	$tpl_result = $db->query($sql_tpl);
        	$re_arr = $tpl_result->fetch_assoc();
        	define("TEMPLETPATH", "./templets/".$re_arr['tpl']);//主题模板
        	$tpl = @file_get_contents(TEMPLETPATH."/list_article.htm");
        	$title = trim($re_arr['keywords']);
        	$dhone = $re_arr['dhone'];
    		$dhtwo = $re_arr['dhtwo'];
    		$dhthree = $re_arr['dhthree'];
    		$querylist = "SELECT * FROM `zq_keywords` WHERE `pinyin`='".$page."'";
    		$resultlist = $db->query($querylist);
    		if($resultlist->num_rows > 0){
    			$listrow = $resultlist->fetch_assoc();
    			$listtitle = $listrow['keywords'];
    		}else{
    			$listtitle = '{keywords}';
    		}
        }elseif(isset($_GET['html'])){
        	$html = $_GET['html'];
        	$query = "SELECT * FROM `".$fz_dbname."`.`zq_page` WHERE `domain`='".$host."' AND `html`='html".$html.".html'";
        	$result = $db->query($query);
        	if($result->num_rows > 0){
        		$row = $result->fetch_assoc();//资源数组化
        		$host_folder = str_replace(":", "-", $host);
        		if(file_exists(HTMLPATH.$row['date']."/".$host_folder."/".$row['html'])){
        			$tpl = @file_get_contents(HTMLPATH.$row['date']."/".$host_folder."/".$row['html']);
        		}else{
        			$query = "DELETE FROM `zq_page` WHERE `id`=".$row['id'];
        			$db->query($query);
        			goto b;
        		}
        	}else{
        		b:
	        	$sql_tpl = "SELECT * FROM `zq_listurl` WHERE `host`='".$host."'";
	        	$tpl_result = $db->query($sql_tpl);
	        	$re_arr = $tpl_result->fetch_assoc();
	        	$title = trim($re_arr['keywords']);
	        	define("TEMPLETPATH", "./templets/".$re_arr['tpl']);//主题模板
	        	$tpl = @file_get_contents(TEMPLETPATH."/article_article.htm");
	        	$dhone = $re_arr['dhone'];
	        	$dhtwo = $re_arr['dhtwo'];
	        	$dhthree = $re_arr['dhthree'];
        	}
        	
        	$article_title = trim($article_title_arr[$html]);
        	//评论
        	$pinglun = '';
        	for($p=0; $p<mt_rand(5, 12); $p++){
	        	$pinglun .= "<li><span style='color:blue;'>{keywords}</span> 发表于 {article_title} {date} <br> ";
    	    	$pinglun .= $a->getComment();
    	    	$pinglun .= "</li>\n";
        	}
        }else{
		    //随机模板
		    $tpl_dir = scandir("./templets/");
		    $tpl_key = mt_rand(2, count($tpl_dir)-1);
		    $fz_templat = $tpl_dir[$tpl_key];
		    define("TEMPLETPATH", "./templets/".$fz_templat);//主题模板
            $tpl = @file_get_contents(TEMPLETPATH."/index.htm");
        	
        	/*$titlenum = 1;//标题关键词数量
        	if($titlenum == 1){
	            $tit_key = mt_rand(0, $gjcnum-1);
	            $title = trim($kwd_dir[$tit_key]);
        	}else{
        		$tit_key1 = mt_rand(0, $gjcnum-1);
        		$tit_key2 = mt_rand(0, $gjcnum-1);
        		$title = trim($kwd_dir[$tit_key1])."_".trim($kwd_dir[$tit_key2]);
        	}*/
	        $webtitle_file_arr = file(WEBTITLE);//网站标题列表
	        foreach($webtitle_file_arr as $v1){
	        	$webtitle_arr1[] = trim($v1);
	        }
        	$query = "SELECT `webtitle` FROM `zq_listurl`";
			$results = $db->query($query);
			if($results->num_rows > 0){
				while($rows = $results->fetch_assoc()){
					$webtitle_arr2[] = $rows['webtitle'];
				}
			}else{
				$webtitle_arr2 = array();
			}
			$webtitle_arr = array_diff($webtitle_arr1, $webtitle_arr2);
			if(!empty($webtitle_arr)){
				foreach($webtitle_arr as $v){
					$webarr[] = $v;
				}
				$webtitle_num = count($webarr);
				$webtitle = trim($webarr[mt_rand(0, $webtitle_num-1)]);
				$title_arr = explode("-", $webtitle);
				$title_arr_num = count($title_arr);
				$title = $title_arr[0];
				@$dhone = $title_arr[0];
				@$dhtwo = $title_arr[1];
	        	@$dhthree = $title_arr[2];
			}else{
				$tit_key = mt_rand(0, $gjcnum-1);
	            $title = trim($kwd_dir[$tit_key]);
	            //$dhone = trim($kwd_dir[mt_rand(0, $gjcnum-1)]);
	            $dhone = $title;
	       		$dhtwo = trim($kwd_dir[mt_rand(0, $gjcnum-1)]);
	        	$dhthree = trim($kwd_dir[mt_rand(0, $gjcnum-1)]);
	        	$webtitle = $dhone."-".$dhtwo."-".$dhthree;
			}
	        //$webtitle_num = count($webtitle_arr);
	        //$webtitle = trim($webtitle_arr[mt_rand(0, $webtitle_num-1)]);
	        //$dhone = trim($kwd_dir[mt_rand(0, $gjcnum-1)]);
	        //$dhtwo = trim($kwd_dir[mt_rand(0, $gjcnum-1)]);
	        //$dhthree = trim($kwd_dir[mt_rand(0, $gjcnum-1)]);
			
        }
        //导航链接
        $dhlinkone = "/".SpGetPinyin($dhone)."/";
        $dhlinktwo = "/".SpGetPinyin($dhtwo)."/";
        $dhlinkthree = "/".SpGetPinyin($dhthree)."/";
        
        //调用当前日期
        $date = date("m-d");
        
        //网页标题{title}、文章内容{contents}以及日期{date}替换
        $str_arr = array('{pinglun}',
        				'{list_title}',
        				'{changetitle}',
		        		'{contents}',
		        		'{title}',
		        		'{webtitle}',
		        		'{date}',
		        		'{templets_path}',
		        		'{dhone}',
		        		'{dhtwo}',
		        		'{dhthree}',
        				'{dhlinkone}',
		        		'{dhlinktwo}',
		        		'{dhlinkthree}',
        				'{article_title}'
        			);
        $replace_arr = array($pinglun,
        				$listtitle,
        				$changetitle,
		        		$contents,
		        		$title,
		    			$webtitle,
		        		$date,
		        		TEMPLETPATH,
		        		$dhone,
		        		$dhtwo,
		        		$dhthree,
		        		$dhlinkone,
		        		$dhlinktwo,
		        		$dhlinkthree,
        				$article_title
        		);
        $tpl_html = str_replace($str_arr, $replace_arr, $tpl);//执行替换

        //友情链接
        $ip = file(IPTXT);//获取IP
        $newport = file(NEWPORT);//新增端口
        $port = file(PORTTXT);//端口
        if(count($newport)>0){
        	$domain = $ip[array_rand($ip)].":".$newport[array_rand($newport)];
        }else{
        	$domain = $ip[array_rand($ip)].":".$port[array_rand($port)];
        }
        
        $flink_zz = "/{flink}/i";
        preg_match_all($flink_zz, $tpl_html, $flink_arr);
        $flink_count = count($flink_arr[0]);//友情链接个数
        for($i=0; $i<$flink_count; $i++){
        	if(count($newport)>0){
        		$domain = trim($ip[array_rand($ip)]).":".trim($newport[array_rand($newport)]);
        	}else{
        		$domain = trim($ip[array_rand($ip)]).":".trim($port[array_rand($port)]);
        	}
            $rand_key = mt_rand(0, $gjcnum-1);
            $keywords_rand = trim($kwd_dir[$rand_key]); //随机关键字
        	$link = "<a href='http://".$domain."' target='_blank'>".$keywords_rand."</a>";
        	$tpl_html = preg_replace($flink_zz, $link, $tpl_html, 1);//链接替换
        }
        
        //关键词替换
        $keywords_zz = "/{keywords}/i";//关键词标签
        preg_match_all($keywords_zz, $tpl_html, $keywords_arr);
        $keywords_count = count($keywords_arr[0]);
        for($i=0; $i<$keywords_count; $i++){
        	$rand_num = mt_rand(0, $gjcnum-1);
        	$rand_keywords = trim($kwd_dir[$rand_num]);
        	$tpl_html = preg_replace($keywords_zz, $rand_keywords, $tpl_html, 1);//关键词替换
        }

        //链接替换
        $link_zz = "/{link}/i";//链接标签
        preg_match_all($link_zz, $tpl, $link_arr);
        $link_count = count($link_arr[0]);//链接个数
        for($i=0; $i<$link_count; $i++){
        	$rand_key = mt_rand(0, $arcnum-1);
        	$keywords_rand = trim($article_title_arr[$rand_key]); //随机关键字
        	$link = "/views-".$keywords_rand."-xBD".$rand_key.".html";
        	$tpl_html = preg_replace($link_zz, $link, $tpl_html, 1);//链接替换
        }
        
        //{host}标签替换
        $host_zz = "/{host}/i";//链接标签
        $tpl_html = preg_replace($host_zz, $host, $tpl_html);//链接替换

        //图片替换
        $img_zz = "/{image}/i";//链接标签
        preg_match_all($img_zz, $tpl, $img_arr);
        $img_count = count($img_arr[0]);//链接个数
        $imgdir = scandir(IMGURL);
        for($i=0; $i<$img_count; $i++){
        	$image = "<img src='.".IMGURL.$imgdir[mt_rand(2, count($imgdir)-3)]."' />";
        	$tpl_html = preg_replace($img_zz, $image, $tpl_html, 1);//链接替换
        }
        
        //锚文本替换
        $keylink_zz = "/{keyword_link}/i";
        preg_match_all($keylink_zz, $tpl_html, $keylink_arr);
        $keylink_count = count($keylink_arr[0]);//锚文本个数
        for($i=0; $i<$keylink_count; $i++){
        	$rand_key = mt_rand(0, $gjcnum-1);
        	$keywords_rand = trim($kwd_dir[$rand_key]); //随机关键字
        	$keyword_link = "<a href='/".SpGetPinyin($keywords_rand)."/'>".$keywords_rand."</a>";
        	$tpl_html = preg_replace($keylink_zz, $keyword_link, $tpl_html, 1);//锚文本替换
        }
        
        //文章链接
        $article_link_zz = "/{article_link}/i";
        preg_match_all($article_link_zz, $tpl_html, $arclink_arr);
        $arclink_count = count($arclink_arr[0]);//锚文本个数
        for($i=0; $i<$arclink_count; $i++){
        	$rand_key = mt_rand(0, $arcnum-1);
        	$keywords_rand = trim($article_title_arr[$rand_key]); //随机关键字
        	$article_link = "<a href='/views-".$keywords_rand."-xBD".$rand_key.".html' title='".$keywords_rand."' target='_blank'>".$keywords_rand."</a>";
        	$tpl_html = preg_replace($article_link_zz, $article_link, $tpl_html, 1);//锚文本替换
        }
        
        $insertpage = $tpl_html;
        
        //最新文章等栏目替换
        $change_zz = "/{changelink}/i";
        preg_match_all($change_zz, $tpl, $change_arr);
        $change_count = count($change_arr[0]);//锚文本个数
        for($i=0; $i<$change_count; $i++){
        	$rand_key = mt_rand(0, $gjcnum-1);
        	$keywords_rand = trim($kwd_dir[$rand_key]); //随机关键字
        	$change_link = "<a href='/".SpGetPinyin($keywords_rand)."/' target='_blank'>".$keywords_rand."</a>";
        	$tpl_html = preg_replace($change_zz, $change_link, $tpl_html, 1);//锚文本替换
        }

        echo $tpl_html;
        $datepath = date("Ymd");//20130622时间格式
        if(!file_exists(HTMLPATH.$datepath)){
        	@mkdir(HTMLPATH.$datepath);//创建以当天日期为名文件夹
        }
        if(isset($_GET['page'])){
        	/* */
        }elseif(isset($_GET['html'])){
        	$html = $_GET['html'];
        	$host_folder = str_replace(":", "-", $host);
        	$filename = HTMLPATH.$datepath."/".$host_folder."/html".$html.".html";
        	file_put_contents($filename, $insertpage);
        	$query = "SELECT * FROM `".$fz_dbname."`.`zq_page` WHERE `domain`='".$host."' AND `html`='html".$html.".html'";
        	$result = $db->query($query);
        	if($result->num_rows==0){
        		$query = "INSERT INTO `".$fz_dbname."`.`zq_page`(`domain`, `html`, `date`) VALUES('".$host."', 'html".$html.".html', '".$datepath."')";
        		$db->query($query);//将访问记录写入数据库
        		$db->close();
        	}
        }else{
        	$host_folder = str_replace(":", "-", $host);
            $date_dir = HTMLPATH.$datepath."/".$host_folder;
            if(!@fopen($date_dir)){
            	@mkdir($date_dir);//以当前日期创建文件夹
            }
            $htmlfile = $date_dir."/index.html";
            file_put_contents($htmlfile, $insertpage);
            $query = "SELECT * FROM `".$fz_dbname."`.`zq_listurl` WHERE `host`='".$host."'";
        	$result = $db->query($query);
        	if($result->num_rows==0){
	            $insert_listurl = "INSERT INTO `".$fz_dbname."`.`zq_listurl`(`host`, `url`, `tpl`, `keywords`, `dhone`, `dhtwo`, `dhthree`, `date`, `webtitle`) VALUES('".$host."', '".$htmlfile."', '".$fz_templat."', '".trim($title)."', '".$dhone."', '".$dhtwo."', '".$dhthree."', '".$datepath."', '".$webtitle."')";
	            $db->query($insert_listurl);//将访问记录写入数据库
	            $db->close();
	    	}
        }
    }
    
    //汉字转换成拼音
    function SpGetPinyin($str, $ishead=0, $isclose=1)
    {
    	global $pinyins;
    	$restr = '';
    	$str = trim($str);
    	$slen = strlen($str);
    	if($slen < 2){
    		return $str;
    	}
    	if(count($pinyins) == 0){
    		$fp = fopen('./data/pinyin.dat', 'r');
    		while(!feof($fp)){
    			$line = trim(fgets($fp));
    			$pinyins[$line[0].$line[1]] = substr($line, 3, strlen($line)-3);
    		}
    		fclose($fp);
    	}
    	for($i=0; $i<$slen; $i++){
    		if(ord($str[$i])>0x80){
    			$c = $str[$i].$str[$i+1];
    			$i++;
    			if(isset($pinyins[$c])){
    				if($ishead==0){
    					$restr .= $pinyins[$c];
    				}else{
    					$restr .= $pinyins[$c][0];
    				}
    			}else{
    				$restr .= "";
    			}
    		}else if( preg_match("/[a-z0-9]/i", $str[$i]) ){
    			$restr .= $str[$i];
    		}else{
    			$restr .= "";
    		}
    	}
    	if($isclose==0){
    		unset($pinyins);
    	}
    	return $restr;
    }
    