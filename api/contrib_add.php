<?php
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', '0');

function out($arr, $code = 200) {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo "API працює";
  exit;
}

$host = "127.0.0.1";
$user = "rudycc";
$pass = "GHJnjrjk24";
$db   = "rudycc_3";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) out(["error"=>"DB connection failed"], 500);
$conn->set_charset("utf8mb4");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$person_id = isset($data["person_id"]) ? (int)$data["person_id"] : 0;
$pay_date  = isset($data["pay_date"]) ? trim($data["pay_date"]) : "";
$amount    = isset($data["amount"]) ? (int)$data["amount"] : 0;
$note      = isset($data["note"]) ? trim($data["note"]) : "";

if ($person_id <= 0) out(["error"=>"person_id required"], 400);
if ($amount <= 0) out(["error"=>"amount must be > 0"], 400);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $pay_date)) {
  out(["error"=>"pay_date must be YYYY-MM-DD"], 400);
}

$dt = DateTime::createFromFormat('Y-m-d', $pay_date);
if (!$dt) out(["error"=>"Invalid pay_date"], 400);

$year  = (int)$dt->format('Y');
$month = (int)$dt->format('n');

$stmt = $conn->prepare("
  INSERT INTO contributions (person_id, pay_date, year, month, amount, note)
  VALUES (?, ?, ?, ?, ?, ?)
");

if (!$stmt) out(["error"=>"Prepare failed"], 500);

$stmt->bind_param("isiiis", $person_id, $pay_date, $year, $month, $amount, $note);

if (!$stmt->execute()) out(["error"=>"Insert failed"], 500);

out([
  "ok" => true,
  "id" => $stmt->insert_id,
  "person_id" => $person_id,
  "pay_date" => $pay_date,
  "year" => $year,
  "month" => $month,
  "amount" => $amount,
  "note" => $note
]);
