<?php
error_reporting(0);
header("Content-Type: text/html;charset=utf-8");

// Define some helper functions
function c($url, $ua){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    $headers = array(
        "accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6"
    );
    if ($ua == "Mobile") {
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Android 4.4; Mobile; rv:70.0) Gecko/70.0 Firefox/70.0");
    } else if($ua == "PC"){
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36 Edg/107.0.1418.35");
    } else if($ua == "Spider"){
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)");
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    }
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
function zhengze($text , $regex){
    preg_match_all($regex, $text, $somatches , PREG_SET_ORDER, 0);
    return $somatches;
}
function unicode2Chinese($str)
{
    return preg_replace_callback("#\\\u([0-9a-f]{4})#i",
        function ($r) {return iconv('UCS-2BE', 'UTF-8', pack('H4', $r[1]));},
        $str);
}

// Define a class for different APIs
class VvhanApi
{
  // Define a method for baidu hot list
  public function baiduredian()
  {
    $_resHtml = str_replace(["\n", "\r", " "], '', c('https://top.baidu.com/board?tab=realtime', 'PC'));
    preg_match('/<!--s-data:(.*?)-->/', $_resHtml, $_resHtmlArr);
    $jsonRes = json_decode($_resHtmlArr[1], true);
    return $jsonRes;
    $tempArr = [];
    foreach ($jsonRes['data']['cards'] as $v) {
      foreach ($v['content'] as $k => $_v) {
        array_push($tempArr, [
          'index' => $k + 1,
          'title' => $_v['word'],
          'desc' => $_v['desc'],
          'pic' => $_v['img'],
          'url' => $_v['url'],
          'hot' => $_v['hotScore'] . 'W个内容',
          'mobilUrl' => $_v['appUrl']
        ]);
      }
    }
    return [
      'success' => true,
      'title' => '百度热点',
      'subtitle' => '指数',
      'update_time' => date('Y-m-d h:i:s', time()),
      'data' => $tempArr
    ];
  }

  // Define a method for zhihu hot list
  public function zhihuHot()
  {
    // 使用file_get_contents()函数发送HTTP请求，并获取知乎的网页内容
    $_resHtml = str_replace(["\n", "\r", " "], '', file_get_contents('https://www.zhihu.com/billboard')); // 修改了这一行
    preg_match('/<script id="js-initialData" type="text\/json">(.*?)<\/script>/', $_resHtml, $_resHtmlArr);
    $jsonRes = json_decode($_resHtmlArr[1], true);
    $tempArr = [];
    foreach ($jsonRes['initialState']['topstory']['hotList'] as $k => $v) {
      array_push($tempArr, [
        'index' => $k + 1,
        'title' => $v['target']['titleArea']['text'],
        'hot' => $v['target']['metricsArea']['text'],
        'url' => $v['target']['link']['url'],
      ]);
    }
    return [
      'success' => true,
      'title' => '知乎热榜',
      'subtitle' => '热度',
      'update_time' => date('Y-m-d h:i:s', time()),
      'data' => $tempArr
    ];
  }



  // Define a method for weibo hot list
  public function wbresou()
  {
    $_md5 = md5(time());
    $cookie = "Cookie: {$_md5}:FG=1";
    $jsonRes = json_decode(c('https://weibo.com/ajax/side/hotSearch', null, $cookie, "https://s.weibo.com"), true);
    $tempArr = [];
    foreach ($jsonRes['data']['realtime'] as $k => $v) {
      array_push($tempArr, [
        'index' => $k + 1,
        'title' => $v['note'],
        'hot' => $v['num'] . '万',
        'url' => "https://s.weibo.com/weibo?q={$v['word_scheme']}&t=31&band_rank=12&Refer=top",
        'mobilUrl' => "https://s.weibo.com/weibo?q={$v['word_scheme']}&t=31&band_rank=12&Refer=top"
      ]);
    }
    return [
      'success' => true,
      'title' => '微博',
      'subtitle' => '热搜榜',
      'update_time' => date('Y-m-d h:i:s', time()),
      'data' => $tempArr
    ];
  }

  // Define a method for bilibili hot list
  public function bilibili()
  {
    $data = c("https://api.bilibili.com/x/web-interface/search/square?limit=50&platform=web","PC");
    $data = json_decode($data,true);
    for($i=0;$i<=100;$i++){
        if($data['data']['trending']['list'][$i]['keyword'] != null){
            $result[$i] = array(
                'index' => $i+1,
                'title' => $data['data']['trending']['list'][$i]['keyword'],
                'url' => 'https://search.bilibili.com/all?keyword='.$data['data']['trending']['list'][$i]['keyword']
            );
        }
    }
    return $result;
  }



  // Define a method for sougou hot list
  public function sougou(){
    $data = c("https://hotlist.imtt.qq.com/Fetch","PC");
    $data = json_decode($data,true);
    for($i=0;$i<=100;$i++){
        if($data['main'][$i]['title'] != null){
            $result[$i] = array(
                'index' => $i+1,
                'title' => $data['main'][$i]['title'],
                'heat_score' => $data['main'][$i]['score'],
                'url' => $data['main'][$i]['url']
            );
        }
    }
    return $result;
  }

  // Define a method for txnews hot list
  public function txnews(){
    $data = c("https://hotlist.imtt.qq.com/Fetch","PC");
    $data = json_decode($data,true);
    for($i=0;$i<=100;$i++){
        if($data['tencent'][$i]['title'] != null){
            $result[$i] = array(
                'index' => $i+1,
                'title' => $data['tencent'][$i]['title'],
                'heat_score' => $data['tencent'][$i]['score'],
                'url' => $data['tencent'][$i]['url']
            );
        }
    }
    return $result;
  }

  // Define a method for toutiao hot list
  public function toutiao(){
    $data = c("https://www.toutiao.com/hot-event/hot-board/?origin=toutiao_pc","PC");
    $data = json_decode($data,true);
    for($i=0;$i<=100;$i++){
        if($data['data'][$i]['Title'] != null){
            $url = str_replace("\u0026","&",$data['data'][$i]['Url']);
            $result[$i] = array(
                'index' => $i+1,
                'title' => $data['data'][$i]['Title'],
                'heat_score' => $data['data'][$i]['HotValue'],
                'url' => $url
            );
        }
    }
    return $result;
  }

  // Define a method for thepaper hot list
  public function thepaper(){
    $data = c("https://cache.thepaper.cn/contentapi/wwwIndex/rightSidebar","PC");
    $data = json_decode($data,true);
    for($i=0;$i<=100;$i++){
        if($data['data']['hotNews'][$i]['name'] != null){
            $result[$i] = array(
                'index' => $i+1,
                'title' => $data['data']['hotNews'][$i]['name'],
                'url' => 'https://www.thepaper.cn/newsDetail_forward_'.$data['data']['hotNews'][$i]['contId']
            );
        }
    }
    return $result;
  }


  // Define a method for pearvideo hot list
  public function pearvideo(){
    $data = c("https://www.pearvideo.com/popular","PC");
    $hot = zhengze($data, '/<h2 class="popularem-title">(.*)<\/h2>/m');
    $id = zhengze($data, '/<a href="(.*)" class="popularembd actplay"/m');
    for($i=0;$i<=100;$i++){
        if($hot[$i][1] != null){
            $result[$i] = array(
                'index' => $i+1,
                'title' => $hot[$i][1],
                'url' => "https://www.pearvideo.com/".$id[$i][1]
            );
        }
    }
    return $result;
  }

  // Define a method for haokan video hot list
  public function haokanvideo(){
    $data = c("https://haokan.baidu.com/videoui/page/pc/toplist?type=hotvideo&sfrom=","PC");
    $hot = zhengze($data, '/"vid":"\w+","title":"(.*?)"/m');
    $url = zhengze($data, '/"pageUrl":"(.*?)"/m');
    $video_url = zhengze($data, '/"videoUrl":"(.*?)"/m');
    $hot_score = zhengze($data, '/"hot":"(.*?)"/m');
    for($i=0;$i<=100;$i++){
        if($hot[$i][1] != null){
            $result[$i] = array(
                'index' => $i+1,
                'title' => unicode2Chinese($hot[$i][1]),
                'heat_score' => $hot_score[$i][1],
                'url' => $url[$i][1],
                'video_url' => $video_url[$i][1]
            );
        }
    }
    return $result;
  }


  // Define a method for five2pojie hot list
  public function five2pojie(){
    $data = c("https://www.52pojie.cn/misc.php?mod=ranklist&type=thread&view=heats&orderby=today","PC");
    $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
    $url = zhengze($data, '/<th><a href="(.*)" target="_blank">.*<\/a><\/th>/m');
    $hot = zhengze($data, '/<th><a href=".*" target="_blank">(.*)<\/a><\/th>/m');
    $category = zhengze($data, '/<td class="frm"><a href=".*" class="xg1" target="_blank">(.*)<\/a><\/td>/m');
    $author = zhengze($data, '/<cite><a href="home\.php\?mod=space&uid=.*" target="_blank">(.*)<\/a><\/cite>/m');
    $time = zhengze($data, '/<cite><a href="home\.php\?mod=space&uid=.*" target="_blank">.*<\/a><\/cite>[\W\w]<em>(.*)<\/em>/m');
    for($i=0;$i<=15;$i++){
        if($hot[$i][1] != null){
            $result[$i] = array(
                'index' => $i+1,
                'title' => $hot[$i][1],
                'category' => $category[$i][1],
                'author' => $author[$i][1],
                'url' => 'https://www.52pojie.cn/'.$url[$i][1],
                'time' => $time[$i][1]
            );
        }
    }
    return $result;
  }
}

// Main
header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');
$type = isset($_GET['type']) ? $_GET['type'] : NULL;
if($type == NULL){
    $json = array(
        "code" => 201, 
        "msg" => '没有填写类型'
    );
}
else{
  // Create an instance of the class
  $API = new VvhanApi;
  // Use a switch statement to call different methods based on the type parameter
  switch ($type) {
    case 'baidu':
      $_res = $API->baiduredian();
      break;
    case 'zhihu':
      $_res = $API->zhihuHot();
      break;
    case 'weibo':
      $_res = $API->wbresou();
      break;
    case 'bilibili':
      $_res = $API->bilibili();
      break;
    case 'sougou':
      $_res = $API->sougou();
      break;
    case 'txnews':
      $_res = $API->txnews();
      break;
    case 'toutiao':
      $_res = $API->toutiao();
      break;
    case 'thepaper':
      $_res = $API->thepaper();
      break;
    case 'pearvideo':
      $_res = $API->pearvideo();
      break;
    case 'haokanvideo':
      $_res = $API->haokanvideo();
      break;
    case '52pojie':
      $_res = $API->five2pojie();
      break;
    default:
      $_res = ['success' => false, 'message' => '参数不完整'];
      break;
  }
  // Return the result as a JSON object
  $json = array(
        "code" => 200, 
        "msg" => '解析成功',
        "data" => $_res
  );
}
echo json_encode($json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
