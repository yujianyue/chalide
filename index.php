<?php
//不压缩6KB不到单文件,150行PHP+html+js+css代码实现网页版chatgpt打字效果(sse流式消息)
//error_reporting(0); //打开报错：去行首双斜杠
$webtitle = "PHP+sse ChatgptAPI 流式信息问答系统";	//网站标题
$tiaojian = "问题";		//查询条件填列标题
$dd = date("YmdHis");
//apiKey: https://platform.openai.com/account/api-keys API获取地址
 $apiKey = "sk-1FzRAWMpaJ8888888888888888888888888888886"; //修改为你的API—KEY
 $URL = "https://api.openai.com/v1/chat/completions"; //接口网址无需修改
//经验技巧:使用第三国腾讯阿里云服务器,比如日韩

//接受问题并存储
if($_GET["x"]=="cha"){
session_start();
$context = (isset($_POST['xid']))?Trim($_POST['xid']):exit("你的问题传递失败!");
$context = str_replace("\n", "\\n", $context);
$postData=array();
$postData['model'] = "gpt-3.5-turbo";
$postData['temperature'] = 0.7;
$postData['messages'][] = ['role' => 'user', 'content' => $context];
$postData['stream'] = true;
$postData = json_encode($postData);
$_SESSION['data'] = $postData;
file_put_contents("chatgpt.ssee.log","\r\n\r\n[ $dd ]=>".$postData,FILE_APPEND);
exit("<p>你的问题已接收!</p>");
}

if($_GET["x"]=="sse"){
header("Content-Type: text/event-stream");
header("X-Accel-Buffering: no");
session_start();
$postData = $_SESSION['data']; //sse无法POST则读取COOKIE
if(!stristr($postData,"messages")) exit("传递内容有误!");
$OPENAI_API_KEY = "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";
$headers  = [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $OPENAI_API_KEY
];
$ch = curl_init();
$callback = function ($ch, $data) {
    $complete = json_decode($data);
    if (isset($complete->error)) {
$err = json_encode($complete->error);
file_put_contents("chatgpt.ssee.log","\r\n===>".$err,FILE_APPEND);
     } else {
//$line = json_decode($data, true)['choices'][0]['delta']['content'];
     echo "{$data}\n\n";
     ob_flush();
     flush();
    }
    return strlen($data);
};
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, $callback);
curl_exec($ch);
curl_close($ch);
exit();
}else{
header('Content-type:text/html;charset = utf-8');
header('Cache-Control: no-cache');
}
?><!DOCTYPE html><html><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $webtitle;?></title>
<style>
*{margin:0;font-size:16px;}
body{font-family:Arial,sans-serif;background-color:#f2f2f2;}
.container{width:97.5%;margin:10px auto;padding:20px;background-color:#fff;border-radius:5px;box-shadow:0 0 10px rgba(0,0,0,0.3);max-width:1200px;min-width:300px;}
result{margin:0px auto;padding-top:30px;}
h1{margin:10px auto;font-size:20px;text-align:center;}
p{margin-bottom:20px;text-align:left;}
input,textarea{display:block;position:relative;background:none;border:2px solid #acacac;border-radius:5px;width:calc(99% - 20px);padding:0 10px;height:95px;z-index:1;}
label{display:inline-block;position:relative;top:-32px;left:10px;color:#acacac;z-index:2;transition:all 0.2s ease-out;}
textarea:focus,textarea:valid{outline:none;border:2px solid #4CAF50;}
textarea:focus + label,textarea:valid + label{top:-115px;color:#4CAF50;background-color:#fff;}
button{display:inline;padding:8px 15px;background-color:#4CAF50;color:#fff;border:none;border-radius:5px;cursor:pointer;}
button:hover{background-color:#3e8e41;}
table {border-top:3px solid #4CAF50;width:99.9%; margin:10px auto;}
.r{font-weight:bold;text-align:center;} 
table tr:nth-child(2n){background:#FAFAFA;}
</style>
<script>
function $(objId){return document.getElementById(objId);}
function loadcha(ttt) {
var xmlhttp; var txt; var text;
$("result").innerHTML = "<p>正在提交你的问题(稍等)...</p>";
if(ttt=="xxx"){$("result").innerHTML = "<p>输入参考：php+mysql 增删改查</p>"; return false;}
if($("xid").value=="" ){$("result").innerHTML = "<p>输入参考：php+mysql 增删改查</p>"; return false;}
if (window.XMLHttpRequest) {
xmlhttp = new XMLHttpRequest();
} else {
xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
}
xmlhttp.onreadystatechange = function() {
if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
$("result").innerHTML = xmlhttp.response;
}
}
var utf8_str = encodeURIComponent($("xid").value); 
xmlhttp.open("POST", "?x=cha&t="+Math.random(), true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("xid=" + utf8_str);

const source = new EventSource('?x=sse&t='+Math.random());
source.onopen = function() {
 $('result').innerHTML = "开始回答<br>\n";
};
source.onmessage = function(event) {
if(event.data == "[DONE]"){ source.close(); return false;}
try {
 txt = JSON.parse(event.data);
 text = txt['choices'][0]['delta']['content'];
console.log('ok:',text);
} catch(e) {
 text = event.data;
console.log('error:',text);
}
if(text && text != ""){
 text = text.replace('<',"&lt;");
 text = text.replace('>',"&gt;");
 text = text.replace(/(\n|\r)+/g,"<br>\n");
}
 $('result').innerHTML += text;
};
source.onerror = function() {
 source.close();  return false;
};
}
</script>
</head>
<body>
<h1><?php echo $webtitle;?></h1>
<div class="container">
<textarea type="text" name="xid" id="xid" required></textarea>
<label for="xid"><?php echo $tiaojian;?></label>
<p><button onclick="loadcha('cha');">提问</button>
<button onclick="loadcha('xxx');">帮助</button></p>
<div id="result"></div>
</div>
</body>
</html>
