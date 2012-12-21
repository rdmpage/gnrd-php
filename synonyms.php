<?php

require_once(dirname(__FILE__) . '/gnrd.php');

// Grab text from BHL page, extract names, look for combinations that are likely to be synonyms

$url = 'http://gnrd.globalnames.org/name_finder.json?url=http://biostor.org/bhlapi_page_text.php?PageID=2340880';

$response = get_names_from_url($url);

// Result
echo "GNRD response\n";
print_r($response);

// Unique names
$names = get_unique_names($response);
echo "Unique names\n";
print_r($names);


// Possible synonyms are names that are difefrent but have the last part the same, e.g.
// Hipposideros bicolor erigens
// Hipposideros erigens

$synonyms = array();
foreach ($names as $name)
{
	if (preg_match('/^(?<genus>\w+)\s+(?<species>\w+)$/', $name, $m))
	{
		if (!isset($synonyms[$m['species']]))
		{
			$synonyms[$m['species']] = array();
		}
		$synonyms[$m['species']][] = $m['genus'];
	}
	if (preg_match('/^(?<genus>\w+)\s+(?<species>\w+)\s+(?<subspecies>\w+)$/', $name, $m))
	{
		if (!isset($synonyms[$m['subspecies']]))
		{
			$synonyms[$m['subspecies']] = array();
		}
		$synonyms[$m['subspecies']][] = $m['genus'] . ' ' . $m['species'];
	}
	
}

foreach ($synonyms as $species => $genus)
{
	if (count($genus) < 2)
	{
		unset($synonyms[$species]);
	}
}

echo "Possible synonyms\n";
print_r($synonyms);


?>