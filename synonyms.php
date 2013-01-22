<?php

require_once(dirname(__FILE__) . '/gnrd.php');

// Grab text from BHL page, extract names, look for combinations that are likely to be synonyms

$PageID = 2858128;

$PageID = 40874129;

$PageID = 2766281;

// Check list of birds of the world
$PageID = 14482542;

$url = 'http://gnrd.globalnames.org/name_finder.json?url=http://biostor.org/bhlapi_page_text.php?PageID=' . $PageID;

$response = get_names_from_url($url);

// Result
echo "GNRD response\n";
print_r($response);

// Unique names
$names = get_unique_names($response);
echo "Unique names\n";
print_r($names);


// Possible synonyms are names that are different but have the last part the same, e.g.
// Hipposideros bicolor erigens
// Hipposideros erigens
// to do: specific epithet spelling may change with gender of genus (sigh)

$synonyms = array();
foreach ($names as $name)
{
	// species
	if (preg_match('/^(?<genus>\w+)\s+(?<species>\w+)$/', $name, $m))
	{
		if (!isset($synonyms[$m['species']]))
		{
			$synonyms[$m['species']] = array();
		}
		$synonyms[$m['species']][] = $m['genus'];
	}
	
	// subspecies
	if (preg_match('/^(?<genus>\w+)\s+(?<species>\w+)\s+(?<subspecies>\w+)$/', $name, $m))
	{
		if (!isset($synonyms[$m['subspecies']]))
		{
			$synonyms[$m['subspecies']] = array();
		}
		$synonyms[$m['subspecies']][] = $m['genus'] . ' ' . $m['species'];
	}
	
	// subgenus
	if (preg_match('/^(?<genus>\w+)\s+\((?<subgenus>\w+)\)\s+(?<species>\w+)$/', $name, $m))
	{
		if (!isset($synonyms[$m['species']]))
		{
			$synonyms[$m['species']] = array();
		}
		$synonyms[$m['species']][] = $m['genus'] . ' (' . $m['subgenus'] . ')';
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