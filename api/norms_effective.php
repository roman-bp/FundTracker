<?php
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', '0');

function out($arr, $code = 200) {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

$host = "127.0.0.1";
$user = "rudycc";
$pass = "GHJnjrjk24";
$db   = "rudycc_3";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) out(["ok"=>false,"error"=>"DB connection failed"], 500);
$conn->set_charset("utf8mb4");

$year = isset($_GET["year"]) ? (int)$_GET["year"] : (int)date("Y");
if ($year < 2000 || $year > 2100) out(["ok"=>false,"error"=>"bad year"], 400);

// беремо правила норм
$stmt = $conn->prepare("SELECT month_from, amount FROM norms WHERE year = ? ORDER BY month_from ASC");
if (!$stmt) out(["ok"=>false,"error"=>"Prepare failed"], 500);
$stmt->bind_param("i", $year);
if (!$stmt->execute()) out(["ok"=>false,"error"=>"Execute failed"], 500);

$res = $stmt->get_result();

$rules = [];
while ($r = $res->fetch_assoc()) {
  $m = (int)$r["month_from"];
  $a = (int)$r["amount"];
  if ($m >= 1 && $m <= 12 && $a > 0) $rules[] = ["month_from"=>$m, "amount"=>$a];
}

// якщо правил нема — дефолт
$default = 400;
$by = [];
for ($m=1;$m<=12;$m++) $by[$m] = $default;

// застосовуємо правила (останнє по часу бере верх)
$current = $default;
$idx = 0;
for ($m=1;$m<=12;$m++) {
  while ($idx < count($rules) && $rules[$idx]["month_from"] == $m) {
    $current = (int)$rules[$idx]["amount"];
    $idx++;
  }
  $by[$m] = $current;
}

out([
  "ok" => true,
  "year" => $year,
  "default" => $default,
  "rules" => $rules,
  "norm_by_month" => $by
]);
