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

/*
  Повертає:
  - people: список людей
  - rows: по кожній людині суми по місяцях 1..12
  - totals: суми по місяцях загалом + загальна сума
*/

$people = [];
$res = $conn->query("SELECT id, full_name, status FROM people ORDER BY full_name ASC");
if (!$res) out(["error"=>"people query failed"], 500);
while ($r = $res->fetch_assoc()) $people[] = $r;

$rows = [];
foreach ($people as $p) {
  $rows[$p["id"]] = array_fill(1, 12, 0);
}

$sql = "
SELECT person_id, month, SUM(amount) AS s
FROM contributions
WHERE year = ?
GROUP BY person_id, month
";
$stmt = $conn->prepare($sql);
if (!$stmt) out(["error"=>"prepare failed"], 500);
$stmt->bind_param("i", $year);
$stmt->execute();
$q = $stmt->get_result();
while ($r = $q->fetch_assoc()) {
  $pid = (int)$r["person_id"];
  $m   = (int)$r["month"];
  $s   = (int)$r["s"];
  if ($m >= 1 && $m <= 12 && isset($rows[$pid])) $rows[$pid][$m] = $s;
}

$totals = array_fill(1, 12, 0);
$grand = 0;
foreach ($rows as $pid => $months) {
  foreach ($months as $m => $s) {
    $totals[$m] += $s;
    $grand += $s;
  }
}

out([
  "ok" => true,
  "year" => $year,
  "people" => $people,
  "rows" => $rows,
  "totals" => $totals,
  "grand_total" => $grand
]);
