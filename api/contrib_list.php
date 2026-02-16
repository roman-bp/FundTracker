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
if ($conn->connect_error) out(["error"=>"DB connection failed"], 500);
$conn->set_charset("utf8mb4");

$person_id = isset($_GET["person_id"]) ? (int)$_GET["person_id"] : 0;
$year      = isset($_GET["year"]) ? (int)$_GET["year"] : (int)date("Y");
$month     = isset($_GET["month"]) ? (int)$_GET["month"] : 0;

if ($person_id <= 0) out(["error"=>"person_id required"], 400);
if ($year < 2000 || $year > 2100) out(["error"=>"bad year"], 400);
if ($month < 1 || $month > 12) out(["error"=>"month 1..12 required"], 400);

$stmt = $conn->prepare("
  SELECT id, person_id, pay_date, year, month, amount, note, created_at
  FROM contributions
  WHERE person_id = ? AND year = ? AND month = ?
  ORDER BY pay_date ASC, id ASC
");
if (!$stmt) out(["error"=>"prepare failed"], 500);

$stmt->bind_param("iii", $person_id, $year, $month);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$total = 0;
while ($r = $res->fetch_assoc()) {
  $r["id"] = (int)$r["id"];
  $r["person_id"] = (int)$r["person_id"];
  $r["year"] = (int)$r["year"];
  $r["month"] = (int)$r["month"];
  $r["amount"] = (int)$r["amount"];
  $total += (int)$r["amount"];
  $items[] = $r;
}

out([
  "ok" => true,
  "person_id" => $person_id,
  "year" => $year,
  "month" => $month,
  "total" => $total,
  "items" => $items
]);
