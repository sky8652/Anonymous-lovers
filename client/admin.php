<?php
/**
 * Created by PhpStorm.
 * User: https://pingxonline.com/
 * Date: 2018-03-30
 * Time: 12:48
 */
session_start();

$conn=mysqli_connect("localhost","root","liang1395..","minlove");

mysqli_query($conn,"set names utf8");

if(mysqli_connect_errno($conn)){
	
		die("数据库链接失败");
}


if (isset($_SESSION['login']) && $_SESSION['login'] == 1){}else{
    header("Location:login.php");
}


include_once "connect.php";
$db = new connectDataBase();

if (isset($_POST['api'])){

    $api = $_POST['api'];
    switch ($api){
        case 'upload':
            $boy = uniqid().$_FILES["boy"]["name"];
            $girl = uniqid().$_FILES["girl"]["name"];

            if (check_file_boy($_FILES["boy"]["name"])){
                move_uploaded_file($_FILES["boy"]["tmp_name"], "images/headshot/" .$boy);
            }

            if (check_file_girl($_FILES["girl"]["name"])){
                move_uploaded_file($_FILES["girl"]["tmp_name"], "images/headshot/" .$girl);
            }

            $sql = "INSERT INTO `headshot`(`girl`, `boy`) VALUES ('{$girl}','{$boy}')";
            mysqli_query($db->link, $sql);
            break;
        case 'add_topic':
            $topic = $db->test_input($_POST['topic']);
            $sql = "INSERT INTO `topic`(`topic`) VALUES ('{$topic}')";
            mysqli_query($db->link, $sql);
            return;
            break;
        case 'delete_topic':
            $topic = $db->test_input($_POST['id']);
            $sql = "DELETE FROM `topic` WHERE `id` = {$topic}";
            mysqli_query($db->link, $sql);
            return;
            break;
        case 'update_topic':
            $id = $db->test_input($_POST['id']);
            $topic = $db->test_input($_POST['topic']);
            $sql = "UPDATE `topic` SET `topic`= '$topic' WHERE `id` = {$id}";
            mysqli_query($db->link, $sql);
            return;
            break;
        case 'get_status':
            // 获取服务器状态
            $sql = "SELECT * FROM `status` WHERE `id` = 1";
            $status = mysqli_query($db->link, $sql);
            $statu = mysqli_fetch_assoc($status);

            $json = array();
            $json['boy_matching'] = $statu['boy'];
            $json['girl_matching'] = $statu['girl'];
            $json['rooms'] = $statu['total'];

            // 获取中匹配的房间数量
            $sql = "SELECT count(*) as total_rooms FROM `room` WHERE 1";
            $total_room = mysqli_fetch_assoc(mysqli_query($db->link, $sql))['total_rooms'];
            $json['total_room'] = $total_room;

            // 获取总聊天数
            $sql = "SELECT count(*) as total_chat FROM `chat` WHERE 1";
            $total_chat = mysqli_fetch_assoc(mysqli_query($db->link, $sql))['total_chat'];
            $json['total_chat'] = $total_chat;

            // 获取中匹配的次数
            $sql = "SELECT count(*) as total FROM `matching` WHERE 1";
            $total_matching = mysqli_fetch_assoc(mysqli_query($db->link, $sql))['total'];
            $json['total_matching'] = $total_matching;

            // 获取匹配的男生数
            $sql= "SELECT count(*) as total FROM `matching` WHERE `gender` = 'boy'";
            $total_boy = mysqli_fetch_assoc(mysqli_query($db->link, $sql))['total'];
            $json['total_boy'] = $total_boy;

            // 获取匹配的女生数
            $sql= "SELECT count(*) as total FROM `matching` WHERE `gender` = 'girl'";
            $total_girl = mysqli_fetch_assoc(mysqli_query($db->link, $sql))['total'];
            $json['total_girl'] = $total_girl;
			
			
			
/*
            // 获取IP数量
           $sql = "SELECT COUNT(*) as total FROM (SELECT * FROM `matching` WHERE 1 GROUP BY `ip`) as ips";
            $total_ip = mysqli_fetch_assoc(mysqli_query($db->link, $sql))['total'];
            $json['total_ip'] = $total_ip;
*/
            echo json_encode($json);
			
            return;
            break;
        case 'delete_headshot':
            $id = $db->test_input($_POST['id']);
            $sql = "DELETE FROM `headshot` WHERE `id` = {$id}";
            mysqli_query($db->link, $sql);
            break;
    }
}

function check_file_boy($filename){
    // 允许上传的图片后缀
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    $temp = $filename;
    $temp = explode(".", $filename);
    $extension = end($temp);        // 获取文件后缀名
    if ((($_FILES["boy"]["type"] == "image/gif")
            || ($_FILES["boy"]["type"] == "image/jpeg")
            || ($_FILES["boy"]["type"] == "image/jpg")
            || ($_FILES["boy"]["type"] == "image/pjpeg")
            || ($_FILES["boy"]["type"] == "image/x-png")
            || ($_FILES["boy"]["type"] == "image/png"))
        && ($_FILES["boy"]["size"] < 204800)    // 小于 200 kb
        && in_array($extension, $allowedExts))
    {
        if ($_FILES["boy"]["error"] > 0)
        {
            echo "错误：: " . $_FILES["boy"]["error"] . "<br>";
            return false;
        }else{
            return true;
        }
    }
    else
    {
        echo "非法的文件格式";
        return false;
    }
}

function check_file_girl($filename){
    // 允许上传的图片后缀
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    $temp = $filename;
    $temp = explode(".", $filename);
    $extension = end($temp);        // 获取文件后缀名
    if ((($_FILES["girl"]["type"] == "image/gif")
            || ($_FILES["girl"]["type"] == "image/jpeg")
            || ($_FILES["girl"]["type"] == "image/jpg")
            || ($_FILES["girl"]["type"] == "image/pjpeg")
            || ($_FILES["girl"]["type"] == "image/x-png")
            || ($_FILES["girl"]["type"] == "image/png"))
        && ($_FILES["girl"]["size"] < 204800)    // 小于 200 kb
        && in_array($extension, $allowedExts))
    {
        if ($_FILES["girl"]["error"] > 0)
        {
            echo "错误：: " . $_FILES["girl"]["error"] . "<br>";
            return false;
        }else{
            return true;
        }
    }
    else
    {
        echo "非法的文件格式";
        return false;
    }
}

// 获取话题列表
$sql = "SELECT * FROM `topic` WHERE 1";
$topics_result = mysqli_query($db->link, $sql);

// 获取头像列表
$sql = "SELECT * FROM `headshot` WHERE 1";
$headshot_result = mysqli_query($db->link, $sql);

?>

<!doctype html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>假装情侣后台管理</title>

    <!-- Set render engine for 360 browser -->
    <meta name="renderer" content="webkit">

    <!-- No Baidu Siteapp-->
    <meta http-equiv="Cache-Control" content="no-siteapp"/>

    <link rel="icon" type="image/png" href="assets/i/favicon.png">

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" sizes="192x192" href="assets/i/app-icon72x72@2x.png">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Amaze UI"/>
    <link rel="apple-touch-icon-precomposed" href="assets/i/app-icon72x72@2x.png">

    <!-- Tile icon for Win8 (144x144 + tile color) -->
    <meta name="msapplication-TileImage" content="assets/i/app-icon72x72@2x.png">
    <meta name="msapplication-TileColor" content="#0e90d2">

    <link rel="stylesheet" href="assets/css/amazeui.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
	<link rel="stylesheet" href="http://47.95.208.16/bs/css/bootstrap.min.css">
	<script src="http://47.95.208.16/bs/js/jquery.min.js"></script>
	<script src="http://47.95.208.16/bs/js/bootstrap.js"></script>
    <style>
        .headshot-list img{
            height: 50px;
            width: 50px;
        }
        .status-box{
            max-width: 100px;
            text-align: center;
            border: 2px solid #607D8B;
            margin: 0 auto;
        }
        .status-box p{
            margin: 0;
            padding: 4px;
        }
        .status-box p:nth-child(2){
            background: #607D8B;
            color: #fff;
        }
		
		.msg img{
			
			height:200px;
			
			width:200px;
			
			
		}
		
		
    </style>
</head>
<body>
<header data-am-widget="header"
        class="am-header am-header-default">
    <div class="am-header-left am-header-nav"  style="visibility: hidden">
        <a href="#left-link" class="">

            <i class="am-header-icon am-icon-home"></i>
        </a>
    </div>

    <h1 class="am-header-title">
        <a href="#title-link" class="">
            假装情侣后台管理
        </a>
    </h1>

    <div class="am-header-right am-header-nav" style="visibility: hidden">
        <a href="#right-link" class="">

            <i class="am-header-icon am-icon-bars"></i>
        </a>
    </div>
</header>
<div data-am-widget="tabs" class="am-tabs am-tabs-default">
    <ul class="am-tabs-nav am-cf">
        <li class="am-active"><a href="[data-tab-panel-0]">房间</a></li>
        <li class=""><a href="[data-tab-panel-1]">话题</a></li>
        <li class=""><a href="[data-tab-panel-2]">情头</a></li>
        <li class=""><a href="[data-tab-panel-3]">管理记录</a></li>
        <li class=""><a href="[data-tab-panel-4]">IP管理</a></li>
    </ul>
    <div class="am-tabs-bd">
        <div data-tab-panel-0 class="am-tab-panel am-active">
            <p style="text-align: center;font-size: 12px;color: #777777;margin: 0">数据实时更新</p>
            <h2 style="margin-top: 4px">正在匹配</h2>
            <ul class="am-avg-sm-3">
                <li>
                    <div class="status-box">
                        <p id="room"></p>
                        <p>房间</p>
                    </div>
                </li>
                <li>
                    <div class="status-box">
                        <p id="matching_boy"></p>
                        <p>男生</p>
                    </div>
                </li>
                <li>
                    <div class="status-box">
                        <p id="matching_girl"></p>
                        <p>女生</p>
                    </div>
                </li>
            </ul>
            <h2>匹配统计</h2>
            <ul class="am-avg-sm-3">
                <li>
                    <div class="status-box">
                        <p id="total_matching"></p>
                        <p>匹配总数</p>
                    </div>
                </li>
                <li>
                    <div class="status-box">
                        <p id="total_boy"></p>
                        <p>男生</p>
                    </div>
                </li>
                <li>
                    <div class="status-box">
                        <p id="total_girl"></p>
                        <p>女生</p>
                    </div>
                </li>
            </ul>
            <h2>其他统计</h2>
            <ul class="am-avg-sm-3">
                <li>
                    <div class="status-box">
                        <p id="total_chat"></p>
                        <p>聊天条数</p>
                    </div>
                </li>
                <li>
                    <div class="status-box">
                        <p id="total_room"></p>
                        <p>总房间数</p>
                    </div>
                </li>
                <li>
                    <div class="status-box">
                        <p id="total_ip"></p>
                        <p>总IP数</p>
                    </div>
                </li>
            </ul>
        </div>
        <div data-tab-panel-1 class="am-tab-panel">
            <button type="button" class="am-btn am-btn-default am-round" style="    margin: 0 auto;
    text-align: center;
    display: block;" onclick="open_add_topic()">添加话题</button>
            <ul class="am-list am-list-static">
                <?php
                    while ($row = mysqli_fetch_assoc($topics_result)){
                        echo <<<topic
                        <li>
                            {$row['topic']}
                            <div class="am-btn-group">
                                <button type="button" class="am-btn am-btn-primary am-radius" onclick="open_edit_topic({$row['id']},'{$row['topic']}')">编辑</button>
                                <button type="button" class="am-btn am-btn-warning am-radius" onclick="delete_topic({$row['id']})">删除</button>
                            </div>
                        </li>
topic;

                    }
                ?>
            </ul>

        </div>
		
        <div data-tab-panel-2 class="am-tab-panel ">
            <form class="am-form" action="admin.php" method="post" enctype="multipart/form-data" style="text-align: center;">
                <h2>上传情侣头像</h2>
                <p>文件大小必须小于200k</p>
                <div class="am-form-group am-form-file">
                    <label for="doc-ipt-file-1">男生头像上传</label>
                    <div>
                        <button type="button" class="am-btn am-btn-default am-btn-sm">
                            <i class="am-icon-cloud-upload"></i> 选择要上传的文件</button>
                    </div>
                    <input type="file" name="boy" id="doc-ipt-file-1">
                    <div id="file-list"></div>
                </div>
                <div class="am-form-group am-form-file">
                    <label for="doc-ipt-file-2">女生头像上传</label>
                    <div>
                        <button type="button" class="am-btn am-btn-default am-btn-sm">
                            <i class="am-icon-cloud-upload"></i> 选择要上传的文件</button>
                    </div>
                    <input type="file" name="girl" id="doc-ipt-file-2">
                    <div id="file-list2"></div>
                </div>
                <input type="hidden" name="api" value="upload">
                <p><button type="submit" class="am-btn am-btn-primary">提交</button></p>
            </form>
            <br>
            <div>
                <ul class="am-list am-list-static headshot-list">
                <?php
                    while ($headshot_row = mysqli_fetch_assoc($headshot_result)){

                        echo <<<topic
                        <li>
                            <img src="images/headshot/{$headshot_row['boy']}" alt="">
                            <img src="images/headshot/{$headshot_row['girl']}" alt="">
                            <div class="am-btn-group">
                                <button type="button" class="am-btn am-btn-warning am-radius" onclick="delete_headshot({$headshot_row['id']})">删除</button>
                            </div>
                        </li>
topic;
                    }
                ?>
                </ul>

            </div>
        </div>
		
		<!--	管理记录		-->
	
		 <div data-tab-panel-3 class="am-tab-panel ">
		 
		 
		 
		 
		 

		 
		 <table border="1px" width="100%" class="table table-bordered table-hover">
		 
		 <tr class="info">
			<td>房间号</td>
			<td>聊天记录</td>
			<td>时间</td>
			
			
		 
		 </tr>
		 
		 <?php
		 
		 $sql="select * from chat ";
		 
		 $query=mysqli_query($conn,$sql);
		 
		 foreach($query as $key=>$val){
			 
			 if($val['msg']=="<effects>heart</effects>"){
				 
				 
				 continue;
				 
			 }else{
				
				echo "<tr>";
				
				echo "<td>".$val['room_id']."</td>";
				echo "<td class='msg'>".$val['msg']."</td>";
				echo "<td>".$val['time']."</td>";
				
				
				echo "<tr>";
				
				 
			 }
			 
		 }
	
		 
		 ?>
		 
		 </table>	 
		 </div>
		 
		 <!--  iP管理  	-->
		 
		 
		 <div data-tab-panel-4 class="am-tab-panel ">
		 <table class="table table-bordered table-hover">
			 <tr class="active">
				<td>房间号</td>
				<td>男IP</td>
				<td>女IP</td>
				<td>创建时间</td>
			 </tr>
			

		
			<?php
				
			 $sql="select * from room ";
		 
			$query=mysqli_query($conn,$sql);
		 
			foreach($query as $key=>$val){
			 
			
				
				echo "<tr>";
				
				echo "<td>".$val['room_id']."</td>";
				echo "<td>".$val['boy']."</td>";
				echo "<td>".$val['girl']."</td>";
				echo "<td>".$val['time']."</td>";
				
				
				
				echo "<tr>";
				
				 
			 }
			 
	
			?>
		 
		 
		 
		 </table>
		 </div>
    </div>
</div>


<div id="add_topic_box" style="display: none">
    <div class="am-form-group">
        <textarea style="width: 100%;" class="new_topic" rows="5" id="new_topic"></textarea>
    </div>
    <p><button type="submit" class="am-btn am-btn-default" onclick="add_topic()">提交</button></p>
</div>
<div id="update_topic_box" style="display: none">
    <div class="am-form-group">
        <textarea style="width: 100%;" class="update_topic" rows="5"></textarea>
    </div>
    <p><button type="submit" class="am-btn am-btn-default" onclick="update_topic()">提交</button></p>
</div>


<!--在这里编写你的代码-->

<!--[if (gte IE 9)|!(IE)]><!-->
<script src="jquery-3.2.1.min.js"></script>
<!--<![endif]-->
<!--[if lte IE 8 ]>
<script src="http://libs.baidu.com/jquery/1.11.3/jquery.min.js"></script>
<script src="http://cdn.staticfile.org/modernizr/2.8.3/modernizr.js"></script>
<script src="assets/js/amazeui.ie8polyfill.min.js"></script>
<![endif]-->
<script src="assets/js/amazeui.min.js"></script>
<script src="layer_mobile/layer.js"></script>
<script src="admin.js?v=0521"></script>

<script>
    $(function() {
        $('#doc-ipt-file-1').on('change', function() {
            var fileNames = '';
            $.each(this.files, function() {
                fileNames += '<span class="am-badge">' + this.name + '</span> ';
            });
            $('#file-list').html(fileNames);
        });

        $('#doc-ipt-file-2').on('change', function() {
            var fileNames = '';
            $.each(this.files, function() {
                fileNames += '<span class="am-badge">' + this.name + '</span> ';
            });
            $('#file-list2').html(fileNames);
        });
    });
	
	
	

		$(function(){
		
		
		
	function total(){
		$.ajax({
			
			
			type:"get",
			
			
			url:"total.php",
			
			data:{"total":"1"},
			
			success:function(data){
				$("#total_ip").text(data);
			}
			
		})
	}
		
		setInterval(function(){
			total();
		},1000);
		
	})
	
	
	
	
	
	
	
	
	
	
</script>
</body>
</html>
