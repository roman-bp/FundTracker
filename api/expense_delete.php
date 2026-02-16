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

$id = isset($data["id"]) ? (int)$data["id"] : 0;
if ($id <= 0) out(["error"=>"id required"], 400);

$chk = $conn->prepare("SELECT id FROM expenses WHERE id = ? LIMIT 1");
if (!$chk) out(["error"=>"Prepare failed"], 500);
$chk->bind_param("i", $id);
$chk->execute();
$r = $chk->get_result();
if (!$r || $r->num_rows === 0) out(["error"=>"not found"], 404);

$stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? LIMIT 1");
if (!$stmt) out(["error"=>"Prepare failed"], 500);
$stmt->bind_param("i", $id);
if (!$stmt->execute()) out(["error"=>"Delete failed"], 500);

out(["ok"=>true, "deleted_id"=>$id]);
