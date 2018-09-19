<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;

// 引入数据库类
require_once 'Connection.php';

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */

    public static function onWorkerStart($businessWorker)
    {
        global $db;
        $db = new \Workerman\MySQL\Connection("localhost", 3306, "username", "password", "dbname");

        // 初始化
        global $boy_waiting;
        global $girl_waiting;
        global $room_id;
        // 等待队列
        $boy_waiting = array();
        $girl_waiting = array();
        $room_id = 0;

        // 只在id编号为0的进程上设置定时器，其它1、2、3号进程不设置定时器
        if($businessWorker->id === 0)
        {
            Timer::add(1, function(){
                global $boy_waiting;
                global $girl_waiting;
                global $room_id;
                global $db;
                echo "\r\n";
                echo "boy:".count($boy_waiting).", girl:".count($girl_waiting).", total:".Gateway::getAllClientCount();
                echo "\r\n";
                $total_room = (int)((Gateway::getAllClientCount() - count($boy_waiting) - count($girl_waiting))/2);
                $row_count = $db->update('status')->cols(array('boy'=>count($boy_waiting),'girl'=>count($girl_waiting),'total'=>$total_room))->where('id=1')->query();
                // 每秒查看一次匹配队列，是否拥有匹配的异性，将他们加入组里面
                while (count($boy_waiting) > 0 && count($girl_waiting) > 0){
                    // 提取数组的第一个元素，进行匹配
                    $girl = array_shift($girl_waiting);
                    $boy = array_shift($boy_waiting);
                    if ($girl != null && $boy != null){
                        $room_id++;
                        // 操作他们的session， 添加房间号
                        Gateway::updateSession($girl, array('room'=>$room_id));
                        Gateway::updateSession($boy, array('room'=>$room_id));
                        // 加入分组
                        Gateway::joinGroup($girl, $room_id);
                        Gateway::joinGroup($boy, $room_id);
                        // 发送已成功匹配的信号到前端，进入聊天界面
                        // 随机生成时间，单位为秒，一分钟到七分钟之间
                        $time = mt_rand(180,600);
                        // 随机获取情侣头像
                        $result = $db->select('*')->from('headshot')->query();
                        $rand_num = mt_rand(0, count($result)-1);
                        // 随机获取话题
                        $topic_result = $db->select('*')->from('topic')->query();
                        $topic_rand_num = mt_rand(0, count($topic_result)-1);
                        // 向组发送消息
                        Gateway::sendToGroup($room_id, json_encode(array('type'=>'enter_room', 'status'=>'success', 'boy'=>"http://pingxonline.com/app/mylove/images/headshot/".$result[$rand_num]['boy'],'girl'=>"http://pingxonline.com/app/mylove/images/headshot/".$result[$rand_num]['girl'],'limit'=>$time, 'topic'=>$topic_result[$topic_rand_num]['topic'])));
                        // 获取IP，写入数据库
//                        var_dump(Gateway::getSession($girl));
                        $girl_ip = Gateway::getSession($girl)['ip'];
                        $boy_ip = Gateway::getSession($boy)['ip'];


                        if (empty($girl_ip) && empty($boy_ip)){

                        }else{
                            $db->insert('room')->cols(array(
                                'room_id'=>$room_id,
                                'girl'=>$girl_ip,
                                'boy'=>$boy_ip,
                                'timelimit'=>$time))->query();
                        }
                    }
                }
            });
        }

    }

    public static function onConnect($client_id)
    {
        // 记录IP
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
//        Gateway::updateSession($client_id, array('ip'=>$_SERVER['REMOTE_ADDR']));
        Gateway::sendToCurrentClient(json_encode(array('连接成功')));
    }

   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
       global $boy_waiting;
       global $girl_waiting;
       $msg = "";
	   echo $message;
        $data = json_decode($message, true);
        if (isset($data['message'])){
            $msg = $data['message'];
        }
        switch ($data['type']){
            case 'start':
                // 开始匹配，将该ID放入数组，等待匹配
                if ($data['gender'] == "girl"){
                    array_push($girl_waiting, $client_id);
                    $_SESSION['gender'] = $data['gender'];
                }else if ($data['gender'] == "boy"){
                    array_push($boy_waiting, $client_id);
                    $_SESSION['gender'] = $data['gender'];
                }
                global $db;
                $db->insert('matching')->cols(array(
                    'ip'=>$_SESSION['ip'],
                    'gender'=>$data['gender']))->query();
                break;
            case 'send':
//                var_dump($message);
                if (empty($_SESSION['room'])){

                }else{
                    $room_id = $_SESSION['room'];

                    $msg = array(
                        'type'=>'msg',
                        'msg'=>$msg
                    );

                    Gateway::sendToGroup($room_id, json_encode($msg), array($client_id));
                    // 把信息存储到数据库
                    global $db;
                    $db->insert('chat')->cols(array(
                        'room_id'=>$room_id,
                        'msg'=>$msg['msg']))->query();
                }
                break;
            case 'leave':
                $room_id = $_SESSION['room'];
                $msg = json_encode(array("type"=>'leave_room', 'status'=>'leave'));
                Gateway::sendToGroup($room_id, $msg);
                break;
        }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 从匹配数组移除链接
       global $girl_waiting;
       global $boy_waiting;
       for ($i=0;$i<count($girl_waiting);$i++){
           if ($girl_waiting[$i] == $client_id){
               array_splice($girl_waiting, $i);
           }

       }
       for ($i=0;$i<count($boy_waiting);$i++){
           if ($boy_waiting[$i] == $client_id){
               array_splice($boy_waiting, $i);
           }
       }
       // 如果在聊天室中离开，则关闭聊天室发送已离开的信息
       if (empty($_SESSION['room'])){
           // 无房间
       }else{
           // 有房间
           $room_id = $_SESSION['room'];
           $msg = json_encode(array("type"=>'leave_room', 'status'=>'leave'));
           Gateway::sendToGroup($room_id, $msg);
       }

   }
}
