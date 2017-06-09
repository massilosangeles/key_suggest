<?
// Function for take content between two delimeter 
function extract_unit($string, $start, $end)
{
$pos = stripos($string, $start);

$str = substr($string, $pos);

$str_two = substr($str, strlen($start));

$second_pos = stripos($str_two, $end);

$str_three = substr($str_two, 0, $second_pos);

$unit = trim($str_three); // remove whitespaces

return $unit;
}

extract($_POST);
  // Verification of the captcha here ...
  $privatekey = "6LeLTSQUAAAAACX4j177jA6L6Nl5aPraDvyvzuSq";
  $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$privatekey."&response=".$captcha_response."&remoteip=".$_SERVER['REMOTE_ADDR']);
  if($response==false)
  {
    echo 'You are a robot!';

  }else
  {
    // Send information for newsletter ect.
    //TODO
    // Keyword string for test after from a post request :)
	$keyword = $keywords;
	$keyword_search = str_replace(" ", "+", $keyword);
	//echo $keyword_search;

	// The right url search
	$site_url = 'https://www.google.com/search?q='.$keyword_search;
	//echo $site_url;
	// Use curl to get the source page
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $site_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// Write the source page in resultat and close curl
	$resultat = curl_exec($ch);
	curl_close($ch);
	$resultat_for_first_algo = $resultat;
	$resultat_for_second_algo = $resultat;
	/*
	*First algo take all key word at the end of the page 
	*
	*/
	// Filter the source code -> first by take the back of the page
	$back_page = explode("Searches related to", $resultat_for_first_algo);
	// and just take the table of seraches related to
	$tab_keyword = explode('<div id="foot">', $back_page[1]);
	// For finish take all line of the table
	$line_keyword = explode('<a href="', $tab_keyword[0]);
	// Unset the first because is bad useless
	unset($line_keyword[0]);
	//print_r($line_keyword);
	// Create the tab resultat
	$tab_final_keyword_algo1 = array();
	// See in all case of the table the keyword
	foreach ($line_keyword as $value) {
	  $temp_word = extract_unit($value, '">', "</a>");
	  $temp_word = str_replace("<b>", "", $temp_word);
	  $temp_word = str_replace("</b>", "", $temp_word);
	  array_push($tab_final_keyword_algo1, $temp_word);
	}
	//print_r($tab_final_keyword_algo1);

	/*
	*Seconde algo take all key word in bold in the page
	*
	*/
	//echo $resultat_for_second_algo;
	// Take juste result
	$body_google_result = extract_unit($resultat_for_second_algo, '<div id="center_col">', 'earches related to');
	//take resultat in aary
	$element = explode('<div class="g">', $body_google_result);
	// Less element
	unset($element[0]);
	// Resultat tab algo init
	$tab_final_keyword_algo2 = array();
	// take just word
	foreach ($element as $value) {
	  $temp_data = extract_unit($value, '<span class=<span class="st">', '</span>');
	  $temp_data = extract_unit($temp_data, '">', '</a>');
	  $temp_data = extract_unit($temp_data, '<b>', '</b>');
	  array_push($tab_final_keyword_algo2, $temp_data);
	}
	// Delete doublon
	$tab_final_keyword_algo2 = array_unique($tab_final_keyword_algo2);


	// For finish merge 2 tab result, delete same value and encode at utf8 and print
	$all_result_keyword = array_merge($tab_final_keyword_algo2, $tab_final_keyword_algo1);
	$all_result_keyword = array_filter($all_result_keyword);
	foreach ($all_result_keyword as $key => $value) {
	  $all_result_keyword[$key] = utf8_encode($value);
	}
	print_r(json_encode($all_result_keyword));
  }   

?>