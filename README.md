# 匿名聊天
两人匿名聊天、
假装情侣匿名聊天

# 演示地址
网址：http://39.105.21.105:888/

# 开发遵循
聊天室是基于workerman框架开发的，遵循着workerman的开发规则。

# 使用方法
1. 导入sql数据库  
2. 更改Applications/chat/Events.php的$db = new \Workerman\MySQL\Connection("数据库地址", 3306, "数据库账号", "密码", "数据库名字"); 为你的数据库连接信息。  
3. 运行start_for_win.bat 运行服务器。系统环境变量要有PHP的环境变量，否则会出错。 框架：http://doc2.workerman.net/326102
4. 修改client文件夹中的clinet.js文件的第一行，var wsUri = "ws://120.79.53.156:8282";，改为你服务器的地址和端口8282
5. 修改client文件夹中的connect.php，配置好客户端的服务器连接


# 联系方式
1. 联系邮箱：1476982312@qq.com

# 开发者
1. 开发者：广科大平兄
平兄博客地址：https://pingxonline.com/
2. 二次开发者：犯二青年
犯二青年博客：https://fanerblog.xin/
