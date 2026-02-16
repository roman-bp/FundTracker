<?php
header("Content-Type: application/json; charset=utf-8");

$host="127.0.0.1";
$user="rudycc";
$pass="GHJnjrjk24";
$db="rudycc_3";

$conn=new mysqli($host,$user,$pass,$db);
$conn->set_charset("utf8mb4");

$year=(int)($_GET["year"]??date("Y"));

$res=$conn->query("SELECT SUM(amount) s FROM expenses WHERE year=$year");
$row=$res->fetch_assoc();

echo json_encode([
  "ok"=>true,
  "total"=>(int)$row["s"]
]);
