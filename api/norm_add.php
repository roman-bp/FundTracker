<?php
header("Content-Type: application/json; charset=utf-8");

$host="127.0.0.1";
$user="rudycc";
$pass="GHJnjrjk24";
$db="rudycc_3";

$conn=new mysqli($host,$user,$pass,$db);
$conn->set_charset("utf8mb4");

$data=json_decode(file_get_contents("php://input"),true);

$year=(int)$data["year"];
$month=(int)$data["month_from"];
$amount=(int)$data["amount"];

if(!$year||!$month||!$amount){
  echo json_encode(["ok"=>false]);
  exit;
}

$stmt=$conn->prepare("
INSERT INTO norms (year,month_from,amount)
VALUES (?,?,?)
");

$stmt->bind_param("iii",$year,$month,$amount);
$stmt->execute();

echo json_encode(["ok"=>true]);
