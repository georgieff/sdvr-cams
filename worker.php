<?php

// https://www.facebook.com/feeds/page.php?id=388707501198026&format=json
// is the URL that should return json data : )
// if the worker is not working check the URL in your browser
// if it's not working in the browser then we should research another way to fetch feed from facebook page : )
// useful link may be this one: https://developers.facebook.com/tools/explorer/?method=GET&path=sdvr.stolichnapolicia%3Ffields%3Did%2Cname
// play & enjoy! : )

// ====
// Set the string needed for the camera posts on the news feed
$cameras_string = 'График на средствата за контрол на пътното движение';
$page_feed_url_string = 'https://www.facebook.com/feeds/page.php?id=388707501198026&format=json';
//some random user agent : D
$curl_user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/536.30.1 (KHTML, like Gecko) Version/6.0.5 Safari/536.30.1';

//cURLing
// Get cURL resource
$curl = curl_init();
// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
  CURLOPT_RETURNTRANSFER => 1,
  CURLOPT_URL => $page_feed_url_string,
  CURLOPT_USERAGENT => $curl_user_agent
  ));
// Send the request & save response to $resp
$resp = curl_exec($curl);
// Close request to clear up some resources
curl_close($curl);


$decoded_response = json_decode($resp);

echo "<meta charset='utf-8'>";

//news count
//echo count($decoded_response->entries). " news";

foreach ($decoded_response->entries as $key => $entry) {
  if(strpos($entry->content, $cameras_string) !== false) {
    $date_published = strtotime($entry->updated);
    echo "<h3>" . "(" . date('H:m:s d/m/Y',$date_published) . ") - " . $entry->title . "</h3>";
    echo "<br><div style='border:1px solid black; padding: 20px;'>";
    echo "<b>ID:</b> sp" . $entry->id ."<br>";
    echo "<b>CONTENT:</b> <br>" . $entry->content;
    echo "</div>";
  }
}

echo "<br><br><br><br><br><br><hr><br>";
echo "<pre>";

var_dump($decoded_response->entries);




