
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
	/*
This file is used only once - to import all the known places
where eventually could be placed camera : )
Using third party plugin (see below)
Rate: 5✪ : )

Script Name: Read excel file in php with example
Script URI: http://allitstuff.com/?p=1303
Website URI: http://allitstuff.com/
*/
/** Include path **/
set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');

/** PHPExcel_IOFactory */
include 'PHPExcel/IOFactory.php';


$inputFileName = './all_cameras.xls';  // File to read
//echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';
try {
	$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
} catch(Exception $e) {
	die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
}

echo "<pre>";
$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
//print_r($sheetData);

include_once('../katie-files/DbLib.php');
$db = new DbLib();
$db->setTable('all_cams');

$cam_district = "";

foreach ($sheetData as $key => $row) {

  if(!empty($row['A']) && empty($row['B']) && empty($row['C']) && empty($row['D']) && empty($row['E']) && empty($row['F'])) {
    $cam_district = $row['A'];
  } else {
    $insert_data['cam_district'] = $cam_district;
    $insert_data['road_name'] = $row['A'] ? $row['A'] : '';
    $insert_data['location_km'] = $row['B'] ? $row['B'] : '';
    $insert_data['place_name'] = $row['C'] ? $row['C'] : '';
    $insert_data['cam_type'] = $row['D'] ? $row['D'] : '';
    $insert_data['location_direction'] = $row['E'] ? $row['E'] : '';
    $insert_data['speed_limit'] = intval($row['F']) ? intval($row['F']) : '';
    print_r($insert_data);
    //$db->insert('all_cams', $insert_data);
    //echo $db->getError(). '<br>';
  }
}