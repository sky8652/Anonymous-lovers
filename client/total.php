<?php



$conn=mysqli_connect("localhost","root","liang1395..","minlove");

mysqli_query($conn,"set names utf8");





$sql="select distinct ip from  matching";

$query=mysqli_query($conn,$sql);

echo mysqli_num_rows($query);




?>