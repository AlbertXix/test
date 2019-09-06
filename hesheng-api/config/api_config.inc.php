<?php
/*
 * 按批次搜索的配置
 */

return [
    // IP PV比, 1:2.5
    'pv_ip_ratio' => 2.5,
    'group_pv_ip' => false,

    // 关键词API
    'keyword_api' => [
        'url' => 'http://ssw.yiimai.com/api/_sw.php?wn=',
    ],

    'search_batch.default' => 'search_fixed',

    // 固定比例搜索
    'search_fixed' => [
        // 待搜索站点执行比例分配
        'site' => [
            'hao123' => 0.55,
            'baidu' => 0.45,
        ],

        //　批次比例
        'batch' => [
            1 => 0.50,
            2 => 0.35,
            3 => 0.10,
            4 => 0.05,
        ]
    ],

    // 浮动比例搜索
    'search_float' => [
        'site' => [
            'hao123' => 0.36,
            'baidu' => 0.64,
        ],

        // 批次和比例随机化
        'batch_random' => [
            // 是否开启，开启的话就不会用到后面[batch]手动分配的比例
            'enable' => false,
            'min' => 0.05,
            'max' => 0.45,
        ],

        'batch' => [
            1 => 0.45,
            2 => 0.35,
            3 => 0.15,
            4 => 0.05,
        ]
    ],

    /*
     * v表示抽中概率。注意其中的v必须为整数，你可以将对应的 奖项的v设置成0，即意味着该奖项抽中的几率是0，
     * 数组中v的总和（基数），基数越大越能体现概率的准确性。
     * 如果v的总和为100，那么v设置为1 抽中概率就是1%，
     * 如果v的总和是10000，那中奖概率就是万分之一了。
     */
    'hao123_page_area' => [
        [ "id"=> 1, "declare" => "顶条LOGO:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "indexLogo", "v"=>0.02 ],
        [ "id"=> 2, "declare" => "logo区域天气:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "weatherInfo", "v"=>0.3 ],
        [ "id"=> 3, "declare" => "设首页:", "titlename"=>"", "tagname"=>"A", "attr"=>"id", "value"=> "sethome", "v"=>0.01 ],
        [ "id"=> 4, "declare" => "logo右广告:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"class", "value"=> "headjoke", "v"=>0.01 ],
        [ "id"=> 5, "declare" => "日历:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "calendar", "v"=>0.01 ],

        [ "id"=> 6, "declare" => "baidu_logo:", "titlename"=>"", "tagname"=>"A", "attr"=>"id", "value"=> "search_logolink", "v"=>0.4 ],
        [ "id"=> 7, "declare" => "baidu_input:", "titlename"=>"", "tagname"=>"INPUT", "attr"=>"name", "value"=> "word", "v"=>8.72 ],
        [ "id"=> 8, "declare" => "baidu_button:", "titlename"=>"", "tagname"=>"INPUT", "attr"=>"type", "value"=> "submit", "v"=>4.74 ],
        [ "id"=> 9, "declare" => "baidu_word:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"monkey", "value"=> "hotsearchShow1", "v"=>0.97 ],
        [ "id"=> 10, "declare" => "baidu_ad_news:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "noticeslider", "v"=>2 ],

        [ "id"=> 11, "declare" => "官媒:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "hao123-govsite", "v"=>2.14 ],

        [ "id"=> 12, "declare" => "左侧1区域:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "box-toplist0", "v"=>3 ],
        [ "id"=> 13, "declare" => "左顶1标题:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"monkey", "value"=> "tab", "v"=>3.92 ],
        [ "id"=> 14, "declare" => "左侧1内容:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"monkey", "value"=> "toutiao", "v"=>10.7 ],

        [ "id"=> 15, "declare" => "名站中:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"class", "value"=> "common-sites", "v"=>7.58 ],
        [ "id"=> 16, "declare" => "名站酷站DP:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "coolsites_wrapper", "v"=>7.8 ],
        [ "id"=> 17, "declare" => "名站区域上:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "userCommonSites", "v"=>24.95 ],
        [ "id"=> 18, "declare" => "名站上+中:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "sites2_wrapper", "v"=>2 ],

        [ "id"=> 19, "declare" => "左侧视频:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "box-toplist1", "v"=>0.02 ],
        [ "id"=> 20, "declare" => "左侧视频内容:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "topyingshi-over", "v"=>1.25 ],
        [ "id"=> 21, "declare" => "左侧搜索热点:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"class", "value"=> "hotsearch-box", "v"=>0.25 ],

        [ "id"=> 22, "declare" => "搜索热点:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"class", "value"=> "hotsearch-box-top", "v"=>0.01 ],
        [ "id"=> 23, "declare" => "搜索热点内容:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"monkey", "value"=> "searchhot", "v"=>0.02 ],
        [ "id"=> 24, "declare" => "主内容框子:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "feed_wrap", "v"=>2 ],
        [ "id"=> 25, "declare" => "主内容标题:", "titlename"=>"", "tagname"=>"H3", "attr"=>"class", "value"=> "layout-left-title", "v"=>3 ],
        [ "id"=> 26, "declare" => "主内容内容:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "feed_news_wrap", "v"=>8.43 ],
        [ "id"=> 27, "declare" => "BOTTOM:", "titlename"=>"", "tagname"=>"DIV", "attr"=>"id", "value"=> "footer", "v"=>0.01 ],
        [ "id"=>28, "declare"=>"补充", "titlename"=>"", "tagname"=>"A", "attr"=>"href", "value"=>"hot_", 'v'=>1 ],
        [ "id"=>29, "declare"=>"热点", "titlename"=>"", "tagname"=>"A", "attr"=>"href", "value"=>"top_", 'v'=>5 ],

    ],

    'hot_sites' => [
        'http://www.baidu.com/?tn=sitehao123_15',
        'http://www.baidu.com/?tn=sitehao123&H123Tmp=nunew11',
        'https://weibo.com/',
        'http://www.youku.com/',
        'http://game.hao123.com/',
        'https://www.ifeng.com/',
    ],

    'top_sites' => [
        'http://www.baidu.com/?tn=sitehao123_15',
        'http://www.sina.com.cn/',
        'http://www.sohu.com/',
        'http://www.qq.com/',
        'http://www.163.com/',
        'http://map.baidu.com/',
        'http://v.hao123.baidu.com/',
        'http://game.hao123.com/',
        'https://www.ifeng.com/',
        'https://s.click.taobao.com/t?e=m%3D2%26s%3D7WCg0%2BpzKwgcQipKwQzePCperVdZeJviK7Vc7tFgwiFRAdhuF14FMa2XsNgKZ%2BOxt4hWD5k2kjP%2FTrTNBNETjAtOHPHN0vssKO4N%2F%2F7xLcVZMTj583r1vqUuZxIcp9pfUIgVEmFmgnaR4ypTBJBwtC8UTyjdhQwHJPwiig1bxLMnyi1UQ%2F17I10hO9fBPG8oXH%2BQH9e66Y4%3D',
        'https://union-click.jd.com/jdc?d=iEZf6v',
        'https://www.suning.com/?utm_source=hao123&amp;utm_medium=mingzhan',
        'http://clickc.admaster.com.cn/c/a126883,b3410715,c1165,i0,m101,8a1,8b3,h',
        'https://www.taobao.com/',
        'http://www.iqiyi.com/',
        'https://s.click.taobao.com/JREwLKw',
        'http://www.youku.com/',
        'http://www.12306.cn/',
        'http://redirect.simba.taobao.com/rd?c=un&w=bd&f=https%3A%2F%2Fmos.m.taobao.com%2Funion%2FxjkPC%3Fpid%3Dmm_26632322_6858406_107180550345&k=552579a399777ebd&p=mm_26632322_6858406_107180550345',
        'http://jump.luna.58.com/s?spm=b-31580022738699-me-f-862&amp;ch=mingzhan',
        'http://u.ctrip.com/union/CtripRedirect.aspx?TypeID=2&amp;Allianceid=1630&amp;sid=1911&amp;OUID=&amp;jumpUrl=http://www.ctrip.com/',
        'http://music.163.com/',
        'https://www.booking.com/index.html?aid=1337411',
        'http://moe.hao123.com/',
        'http://tuijian.hao123.com/?type=rec',
        'http://www.eastmoney.com/',
        'http://go.hao123.com/?tn=mz',
        'http://www.cnki.net/',
        'https://www.bilibili.com/',
        'https://qiang.suning.com/?utm_source=hao123&amp;utm_medium=kuzhan',
        'https://v.6.cn/?src=z9weij1159',
        'http://www.huya.com/',
        'https://www.baidu.com/s?word=%E5%8F%8C%E8%89%B2%E7%90%83&amp;tn=50000204_hao_pg&amp;ie=utf-8',
        'https://b.faloo.com',
        'https://tuijian.hao123.com/sports',
        'https://www.baidu.com/s?word=NBA&amp;tn=50000203_hao_pg&amp;ie=utf-8',
        'http://www.chsi.com.cn/',
        'https://www.zhibo8.cc/',
        'http://flights.ctrip.com/?allianceid=1630&amp;sid=1723524',
        'https://s.click.taobao.com/mGPArIw',
        'https://www.douban.com/',
        'http://www.icbc.com.cn/icbc/',
        'https://www.tianyancha.com/',
        'https://mail.qq.com/',
        'https://pan.baidu.com/',
        'http://www.tianya.cn/',
        'http://www.zhihu.com/',
    ],
];
