<?php
error_reporting(0);
header("Content-Type: text/html;charset=utf-8");

// 定义一些辅助函数

function c($url, $ua)
{
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
    } else if ($ua == "PC") {
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36 Edg/107.0.1418.35");
    } else if ($ua == "Spider") {
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)");
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    }
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function zhengze($text, $regex)
{
    preg_match_all($regex, $text, $somatches, PREG_SET_ORDER, 0);
    return $somatches;
}

function unicode2Chinese($str)
{
    return preg_replace_callback("#\\\u([0-9a-f]{4})#i",
        function ($r) {
            return iconv('UCS-2BE', 'UTF-8', pack('H4', $r[1]));
        },
        $str);
}

class HuiApi {
    // 获取百度热搜榜
    public function baidu() {
        $data = c("https://www.baidu.com", "PC");
        if (!$data) {
            return array(
                array(
                    'index' => 1,
                    'title' => 'Error!',
                    'url' => 'JavaScript:;'
                )
            );
        }
        
        $hot = zhengze($data, '/card_title": "(.*?)"/m');
        $index = zhengze($data, '/index": "(.*?)"/m');
        $result = array();
        for ($i = 0; $i <= 100; $i++) {
            if ($hot[$i][1] != null) {
                $result[$i] = array(
                    'index' => $i + 1,
                    'title' => $hot[$i][1],
                    'url' => "https://www.baidu.com/s?wd=" . $hot[$i][1]
                );
            }
        }
        return $result;
    }
    
    // 获取Bilibili热搜榜
    public function bilibili() {
        $data = c("https://api.bilibili.com/x/web-interface/search/square?limit=50&platform=web", "PC");
        if (!$data) {
            return array(
                array(
                    'index' => 1,
                    'title' => 'Error!',
                    'url' => 'JavaScript:;'
                )
            );
        }
        
        $data = json_decode($data, true);
        $result = array();
        for ($i = 0; $i <= 100; $i++) {
            if ($data['data']['trending']['list'][$i]['keyword'] != null) {
                $result[$i] = array(
                    'index' => $i + 1,
                    'title' => $data['data']['trending']['list'][$i]['keyword'],
                    'url' => 'https://search.bilibili.com/all?keyword=' . $data['data']['trending']['list'][$i]['keyword']
                );
            }
        }
        return $result;
    }
    
    // 获取今日头条热搜榜
    public function toutiao() {
        $data = c("https://www.toutiao.com/hot-event/hot-board/?origin=toutiao_pc", "PC");
        if (!$data) {
            return array(
                array(
                    'index' => 1,
                    'title' => 'Error!',
                    'url' => 'JavaScript:;'
                )
            );
        }
        
        $data = json_decode($data, true);
        $result = array();
        for ($i = 0; $i <= 29; $i++) {
            if ($data['data'][$i]['Title'] != null) {
                $url = str_replace("\u0026", "&", $data['data'][$i]['Url']);
                $result[$i] = array(
                    'index' => $i + 1,
                    'title' => $data['data'][$i]['Title'],
                    'url' => $url
                );
            }
        }
        return $result;
    }
    
    // 获取知乎热榜
    public function zhihu() {
        $data = c("https://www.zhihu.com/api/v3/feed/topstory/hot-lists/total?limit=50&desktop=true", "PC");
        if (!$data) {
            return array(
                array(
                    'index' => 1,
                    'title' => 'Error!',
                    'url' => 'JavaScript:;'
                )
            );
        }
        
        $data = json_decode($data, true);
        $result = array();
        $items = $data['data'];
        foreach ($items as $index => $item) {
            if (isset($item['target']['title'])) {
                $questionId = $item['target']['id'];
                $title = $item['target']['title'];
                $url = $item['target']['url'];
                $result[] = array(
                    'index' => $index + 1,
                    'title' => $title,
                    'url' => $url
                );
            }
        }
        return $result;
    }
}




// 更新并保存数据的函数
function updateAndSaveData($type, $filename)
{
    $API = new HuiApi();

    // 根据类型参数调用相应的方法
    switch ($type) {
        case 'baidu':
            $data = $API->baidu();
            break;
        case 'bilibili':
            $data = $API->bilibili();
            break;
        case 'zhihu':
            $data = $API->zhihu();
            break;
        case 'toutiao':
            $data = $API->toutiao();
            break;
        default:
            // 处理不支持的类型
            $data = array(
                array(
                    'index' => 1,
                    'title' => 'Error!',
                    'url' => 'JavaScript:;'
                )
            );
            break;
    }

    // 将数据转换为 JSON 格式
    $jsonData = json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // 将数据保存到指定的文件
    file_put_contents($filename, $jsonData);
}

// 更新并保存各类型的数据
updateAndSaveData('baidu', 'baidu.json');
updateAndSaveData('bilibili', 'bilibili.json');
updateAndSaveData('toutiao', 'toutiao.json');
updateAndSaveData('zhihu', 'zhihu.json');
?>