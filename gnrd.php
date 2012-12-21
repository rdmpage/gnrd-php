<?php

// Global Names Recognition and Discovery API functions
// see http://gnrd.globalnames.org/api

require_once(dirname(__FILE__) . '/config.inc.php');

//--------------------------------------------------------------------------------------------------
/**
* @brief Test whether HTTP code is valid
*
* HTTP codes 200 and 302 are OK.
*
* For JSTOR we also accept 403
*
* @param HTTP code
*
* @result True if HTTP code is valid
*/
function HttpCodeValid($http_code)
{
	if ( ($http_code == '200') || ($http_code == '302') || ($http_code == '403'))
	{
		return true;
	}
	else{
		return false;
	}
}


//--------------------------------------------------------------------------------------------------
/**
* @brief GET a resource
*
* Make the HTTP GET call to retrieve the record pointed to by the URL.
*
* @param url URL of resource
*
* @result Contents of resource
*/
function get($url, $userAgent = '', $timeout = 0)
{
	global $config;
	
	$data = '';
	
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1);
	//curl_setopt ($ch, CURLOPT_HEADER,		  1);

	if ($userAgent != '')
	{
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	}	
	
	if ($timeout != 0)
	{
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	}
	
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' .$config['proxy_port']);
	}
	
			
	$curl_result = curl_exec ($ch);
	
	//echo $curl_result;
	
	if (curl_errno ($ch) != 0 )
	{
		echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
	}
	else
	{
		$info = curl_getinfo($ch);
		
		 //$header = substr($curl_result, 0, $info['header_size']);
		//echo $header;
		
		
		$http_code = $info['http_code'];
		
		//echo "<p><b>HTTP code=$http_code</b></p>";
		
		if (HttpCodeValid ($http_code))
		{
			$data = $curl_result;
		}
	}
	return $data;
}


//--------------------------------------------------------------------------------------------------
// Call GNRD serviuce to extract names from content at end of URL
function get_names_from_url($url, $detect_language = false)
{
	$response = null;
	
	if (!$detect_language)
	{
		$url .= '&detect_language=false';
	}
	
	$json = get($url);
	
	$response = json_decode($json);
	
	// Takes a while to process, keep polling into we don't get HTTP 303
	if ($response->status = 303)
	{
	   $status = $response->status;
	   $url = $response->token_url;
	
	   while ($status == 303)
	   {
		   $json = get($url);
		   $response = json_decode($json);
		   $status = $response->status;
		   echo '.';
		   sleep(1);
	   }
	
		//print_r($response);
	
		// If HTTP 200 then success
		if ($status == 200)
		{
			// success
		}
	
	
	}
	
	return $response;
}


//--------------------------------------------------------------------------------------------------
// Call GNRD serviuce to extract names from text (send text using POST)
function get_names_from_text($text, $detect_language = false)
{
	global $config;
	
	$response = null;
	
	$url = 'http://gnrd.globalnames.org/name_finder.json';
	
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1);	

	// Set HTTP headers
	$headers = array();
	
	// Override Expect: 100-continue header (may cause problems with HTTP proxies
	// http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
	$headers[] = 'Expect:'; 
	curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
	
	// HTTP proxy
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' .$config['proxy_port']);
	}
			
	// POST
	curl_setopt($ch, CURLOPT_POST, TRUE);
	
	$post_data = array(
		'text' => $text,
		'detect_language' => $detect_language
		);
		
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

	$json = curl_exec ($ch);
	
	$response = json_decode($json);
	
	// Takes a while to process, keep polling into we don't get HTTP 303
	if ($response->status = 303)
	{
	   $status = $response->status;
	   $url = $response->token_url;
	
	   while ($status == 303)
	   {
		   $json = get($url);
		   $response = json_decode($json);
		   $status = $response->status;
		   echo '.';
		   sleep(1);
	   }
	
		//print_r($response);
	
		// If HTTP 200 then success
		if ($status == 200)
		{
			// success
		}
	
	
	}
	
	return $response;
}


//--------------------------------------------------------------------------------------------------
// Return a list of unique name strings from GNRD response
function get_unique_names($response)
{
	$names = array();
  	if (isset($response->names))
   	{
   		$names = array();
   		foreach ($response->names as $name)
   		{
   			$names[] = $name->scientificName;
   		}
   	}
   	$names = array_unique($names);
   	return $names;
}

?>