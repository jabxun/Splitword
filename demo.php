<?php
ini_set('display_errors', 'On');
ini_set('memory_limit', '512M');
error_reporting(E_ALL);

spl_autoload_register(function ($class) {

    $file = str_replace('\\','/', $class).'.php';
    $file = stream_resolve_include_path($file);
    include($file);
});
use Split\Word;
use Split\Dict;
$startTime = $nowTime = microtime(true);
$endTime = '';
$inputStr = (isset($_POST['source']) ? $_POST['source'] : '');
$testStr = "安卓手机用户都发现这样一个问题，那就是新手机刚买的时候快如闪电，但是用着用着发现会越来越卡越来越慢，甚至经常提醒内存不够用，但是手机里却并没有安装多少东西。有时候打开网页加载的速度想砸手机的心都有，有木有？
其实关于这个问题并不是手机系统的问题，而是个人使用习惯的问题。根据猎豹最新发布的报告显示，85%的消费者选择安卓手机，在日常使用过程中常有“手机运行速度慢，存储空间不足，电池不够耐用”等问题，主要是由于没有及时清理手机垃圾。仅以淘宝客户端为例，平均每台手机每月产生的垃圾就高达674M，大约相当于5集标清版电视剧所需要的空间。";
$memoryTimeInfo = '';
$stringLen = 0;
$merge = true;

function MemoryAndTimeInfo($title)
{
    global $nowTime,$memoryTimeInfo;
    $nTime = microtime(true);
    $etime = number_format($nTime - $nowTime,4);
    $memberUseMb = number_format(memory_get_usage()/1024/1024,2);
    $memoryTimeInfo .= "{$title}: &nbsp;{$memberUseMb} MB time：{$etime} S<br />\n";
    $nowTime = $nTime;
}
header('Content-Type: text/html; charset=utf-8');
MemoryAndTimeInfo('no action');

if(isset($_GET['dict']) && $_GET['dict'] == 'update'){
    if(Dict::MakeDict(SP_DIR.'dict\baseNotBuild')){
        echo ' make dict success';
    }else{
        echo ' error';
    }
    
    exit(0);
}
if($inputStr != ''){
    $tryMerge = isset($_POST['merge']) && $_POST['merge'] ? true : false;
    $merge = $tryMerge;
    
    //init
    $do = new Word();
    MemoryAndTimeInfo('Instance obj');    
    //segment
    $do->tryMergeWord = $tryMerge;
    $do->toSplit($inputStr);
    MemoryAndTimeInfo('split word');
    //获取分词结果
    $result = $do->GetFinallyResult(' ');
    MemoryAndTimeInfo('output result');
    //获取组成的新词结果
    $newWordsStr = $do->newWordsStr;
    
    $endTime = number_format(microtime(true) - $startTime,4);
    $stringLen = number_format(strlen($inputStr)/1024,2);
    unset($do);  
}else{
    $endTime =   microtime(true) -   $startTime;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>demo</title>
    </head>
<body>
    <table width='100%' align='center'>
        <tr>
            <td>    
                <form method="post" action="">
                    <b>source :</b><a href="?dict=update">update dict</a> <br/>
                    <textarea name="source" style="width:98%;height:150px;"><?php echo (isset($_POST['source']) && $_POST['source'] ? trim($_POST['source']) : trim($testStr)); ?></textarea>
                    <br/>
                    <input type='checkbox' name='merge' value='1' <?php echo ($merge ? "checked='1'" : ''); ?>/>merge word
                    <br/>
                    <input type="submit" name="Submit" value="submit" />
                </form>
                <br />
                <hr />
                <b>result</b>
                
                <textarea style="width:98%;height:150px;"><?php echo (isset($result) ? $result : ''); ?></textarea>
                <br />
                info
                <hr />
                <span>string length:</span><?php echo $stringLen; ?>K 
                <span>new words:</span><?php echo (isset($newWordsStr)) ? $newWordsStr : ''; ?><br />
                <hr />
                <span>memory usage(MB) && execute time(s)</span><hr />
                <?php echo $memoryTimeInfo; ?>
                all time：<?php echo $endTime; ?>
            </td>
        </tr>
    </table>
</body>
</html>

