<?php
/*
 * @Author:      admin@acgsan.com
 * @DateTime:    2018-03-02 14:21:50
 * @Description: p站日榜前1-50下载
 * @param:      id：日期 n： 排行数 
 http://air.demo/spider/pix.php?id=20180225&n=20
*/
/* end */
set_time_limit(120);
// 根目录
define('SITE_PATH', dirname(__FILE__));
$id = $_GET['id'];
$num = $_GET['n'];
$url = "https://www.pixiv.net/ranking.php?mode=daily&date=" . $id;
$optionget = array('http' => array('method' => "GET", 'header' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36"));
$file = file_get_contents($url, false, stream_context_create($optionget));
$regex = "/https\\:\\/\\/i.pximg.net\\/c\\/240x480\\/img-master\\/img\\/.*?_master1200\\.jpg/ism";
preg_match_all($regex, $file, $match);

$work_path = SITE_PATH . "/${id}";
dir_mkdir($work_path);

foreach ($match[0] as $k => $v) {
    $match[0][$k] = str_ireplace('c/240x480/img-master', 'img-master', $match[0][$k]);
    // $match[0][$k] = str_ireplace('_master1200', '', $match[0][$k]);
    if ($k >= $num) {
        unset($match[0][$k]);
    }
}
// echo '<pre>';var_dump($match[0]);exit;
foreach ($match[0] as $k => $v) {
    echo '<pre>';var_dump($k,$v);
    $adds = $work_path . '/' . $k.'.jpg';
    download($adds,$v);
}
function download($adds,$url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.pixiv.net/");
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36");
        $file = curl_exec($ch);

        curl_close($ch);
        $filename = pathinfo($url, PATHINFO_BASENAME);

        $resource = fopen($adds, 'a');
        fwrite($resource, $file);
        fclose($resource);
    
}
/**
 * 创建文件夹
 *
 * @param string $path      文件夹路径
 * @param int    $mode      访问权限
 * @param bool   $recursive 是否递归创建
 * @return bool
 */
function dir_mkdir($path = '', $mode = 0777, $recursive = true)
{
    clearstatcache();
    if (!is_dir($path))
    {
        mkdir($path, $mode, $recursive);
        return chmod($path, $mode);
    }
 
    return true;
}
