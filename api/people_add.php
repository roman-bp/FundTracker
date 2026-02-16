<?php
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', '0');

function out($arr, $code = 200) {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  out(["error"=>"POST required"], 405);
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

$full_name = isset($data["full_name"]) ? trim($data["full_name"]) : "";
$phone     = isset($data["phone"]) ? trim($data["phone"]) : "";
$status    = isset($data["status"]) ? (int)$data["status"] : 1;

if ($full_name === "") out(["error"=>"full_name required"], 400);
if ($status !== 0 && $status !== 1) $status = 1;

$stmt = $conn->prepare("INSERT INTO people (full_name, phone, status) VALUES (?, ?, ?)");
if (!$stmt) out(["error"=>"Prepare failed"], 500);

$stmt->bind_param("ssi", $full_name, $phone, $status);
if (!$stmt->execute()) out(["error"=>"Insert failed"], 500);

out([
  "ok" => true,
  "id" => $stmt->insert_id,
  "full_name" => $full_name,
  "phone" => $phone,
  "status" => $status
]);
