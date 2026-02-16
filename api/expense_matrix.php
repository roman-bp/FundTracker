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

$year = isset($_GET["year"]) ? (int)$_GET["year"] : (int)date("Y");
if ($year < 2000 || $year > 2100) out(["error"=>"bad year"], 400);

$months = array_fill(1, 12, 0);

$stmt = $conn->prepare("
  SELECT month, SUM(amount) AS s
  FROM expenses
  WHERE year = ?
  GROUP BY month
");
if (!$stmt) out(["error"=>"prepare failed"], 500);

$stmt->bind_param("i", $year);
$stmt->execute();
$res = $stmt->get_result();

$grand = 0;
while ($r = $res->fetch_assoc()) {
  $m = (int)$r["month"];
  $s = (int)$r["s"];
  if ($m >= 1 && $m <= 12) $months[$m] = $s;
  $grand += $s;
}

out([
  "ok" => true,
  "year" => $year,
  "months" => $months,
  "grand_total" => $grand
]);

