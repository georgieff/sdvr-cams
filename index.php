<?php

include_once('fetch_worker.php');
include_once('db_worker.php');

echo "<meta charset='utf-8'>";
echo "<pre>";
$worker = new SDVRWorker();
if($worker->try_fetch_feed()) {
  $prepared_data = $worker->prepare_data_for_insert();
} else {
  $prepared_data = null;
}

$db_worker = new DBWorker();
$db_worker->import_new_data($prepared_data);
