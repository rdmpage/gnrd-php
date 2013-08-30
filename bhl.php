<?php

// Index a page from BHL

require_once(dirname(__FILE__) . '/gnrd.php');

// Grab text from BHL page, extract names, look for combinations that are likely to be synonyms

$PageID = 4311475;

$url = 'http://gnrd.globalnames.org/name_finder.json?url=http://biostor.org/bhlapi_page_text.php?PageID=' . $PageID;

$response = get_names_from_url($url);

// Result
echo "GNRD response\n";
print_r($response);

// Unique names
$names = get_unique_names($response);
echo "Unique names\n";
print_r($names);


?>