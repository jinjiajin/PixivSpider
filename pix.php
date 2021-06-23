<?php
/*
 * @Author:      i@jinjiajin.net
 * @DateTime:    2018-03-02 14:21:50
 * @Description: p站日榜前1-50下载
 * @param:      id：日期  n：排行数 
 http://air.me/pix/index.php?id=20180225&n=20
*/
/* end */
set_time_limit(120);
// 根目录
define('SITE_PATH', dirname(__FILE__));
$id = $_GET['id'];
$num = $_GET['n'];
$url = "https://www.pixiv.net/ranking.php?mode=daily&date=" . $id;

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);  

$file = file_get_contents($url, false, stream_context_create($arrContextOptions));

$regex = "/data-title.*?data-user-name/ism";
preg_match_all($regex, $file, $match);

$regex = "/https\:\/\/[a-z,0-9,-]+\.[a-z,-]+\.[a-z]+\/c\/240x480\/img-master\/img.*?_p0_master1200.jpg/ism";

preg_match_all($regex, $file, $match_image);

$work_path = SITE_PATH . "/${id}";

dir_mkdir($work_path);

foreach ($match[0] as $k => $v) {
	$match[0][$k] = str_replace("data-title=\"","",$match[0][$k]);
	$match[0][$k] = str_replace("\" data-user-name","",$match[0][$k]);
    $match[0][$k] = json_encode($match[0][$k]);
    $sp  =  '/uff[0-9][0-9]/ism' ;
    $match[0][$k]  = preg_replace( $sp ,  '' ,  $match[0][$k] );
    $match[0][$k] = json_decode($match[0][$k]);

    $match[0][$k] = removeEmoji($match[0][$k]);
    $match[0][$k] = replace_specialChar($match[0][$k]);
    if ($k >= $num) {
        unset($match[0][$k]);
    }
}

foreach ($match_image[0] as $k => $v) {
    $match_image[0][$k] = str_ireplace('c/240x480/img-master', 'img-master', $match_image[0][$k]);
    if ($k >= $num) {
        unset($match_image[0][$k]);
    }else {
        $adds = $work_path . '/(' .$k.')'. $match[0][$k].'.jpg';
        download($adds,$match_image[0][$k]);
    }
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
//去除表情
function  removeEmoji($text) {
         $text = preg_replace("/<a.*?_blank\">/ism", '', $text);
         $text = preg_replace("/\<\/a\>/ism", '', $text);
         $text = str_replace(array(" ","　","\t","\n","\r"),array("","","","",""), $text); 

         $clean_text  =  "" ;
         // Match Emoticons
         $regexEmoticons  =  '/[\x{1F600}-\x{1F64F}]/u' ;
         $clean_text  = preg_replace( $regexEmoticons ,  '' ,  $text );
         // Match Miscellaneous Symbols and Pictographs
         $regexSymbols  =  '/[\x{1F300}-\x{1F5FF}]/u' ;
         $clean_text  = preg_replace( $regexSymbols ,  '' ,  $clean_text );
         // Match Transport And Map Symbols
         $regexTransport  =  '/[\x{1F680}-\x{1F6FF}]/u' ;
         $clean_text  = preg_replace( $regexTransport ,  '' ,  $clean_text );
         // Match Miscellaneous Symbols
         $regexMisc  =  '/[\x{2600}-\x{26FF}]/u' ;
         $clean_text  = preg_replace( $regexMisc ,  '' ,  $clean_text );
         // Match Dingbats
         $regexDingbats  =  '/[\x{2700}-\x{27BF}]/u' ;
         $clean_text  = preg_replace( $regexDingbats ,  '' ,  $clean_text );
         return $clean_text;
     }
function replace_specialChar($strParam){
   $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
   $a = preg_replace($regex,"",$strParam);
   return $a;
}
