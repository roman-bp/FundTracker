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

$year  = isset($_GET["year"]) ? (int)$_GET["year"] : (int)date("Y");
$month = isset($_GET["month"]) ? (int)$_GET["month"] : 0;

if ($year < 2000 || $year > 2100) out(["ok"=>false,"error"=>"bad year"], 400);
if ($month < 1 || $month > 12) out(["ok"=>false,"error"=>"month 1..12 required"], 400);

$sql = "SELECT id, spend_date, year, month, amount, category, note
        FROM expenses
        WHERE year=? AND month=?
        ORDER BY spend_date ASC, id ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) out(["ok"=>false,"error"=>"Prepare failed (expense_list)"], 500);

$stmt->bind_param("ii", $year, $month);
if (!$stmt->execute()) out(["ok"=>false,"error"=>"Execute failed (expense_list)"], 500);

$res = $stmt->get_result();

$items = [];
$total = 0;

while ($r = $res->fetch_assoc()) {
  $r["id"] = (int)$r["id"];
  $r["year"] = (int)$r["year"];
  $r["month"] = (int)$r["month"];
  $r["amount"] = (int)$r["amount"];
  $total += (int)$r["amount"];
  $items[] = $r;
}

out([
  "ok" => true,
  "year" => $year,
  "month" => $month,
  "total" => $total,
  "items" => $items
]);
