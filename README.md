# 匿名聊天
三人匿名群聊；
假装情侣匿名聊天

# 使用方法
1. 导入sql数据库  
2. 更改Applications/chat/Events.php的$db = new \Workerman\MySQL\Connection("数据库地址", 3306, "数据库账号", "密码", "数据库名字"); 为你的数据库连接信息。  
3. 运行start_for_win.bat 运行服务器。系统环境变量要有PHP的环境变量，否则会出错。 框架：http://doc2.workerman.net/326102
4. 修改client文件夹中的clinet.js文件的第一行，var wsUri = "ws://120.79.53.156:8282";，改为你服务器的地址和端口8282
5. 修改client文件夹中的connect.php，配置好客户端的服务器连接