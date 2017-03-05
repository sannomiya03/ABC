<?php
require_once dirname(__FILE__)."/DAMS.DAI_advanced.class.php";

echo "--BEGIN--\n";
$DAI = new DAI_advanced();
echo count( $DAI->getDocuments( array("use"=>"貼紙") ) )."/".count( $DAI->getDocuments() )."\n";
echo count( $DAI->getDocuments( array("use"=>"生地") ) )."/".count( $DAI->getDocuments() )."\n";
echo count( $DAI->getInstances( array("use"=>"貼紙") ) )."/".count( $DAI->getInstances() )."\n";
echo count( $DAI->getInstances( array("use"=>"生地") ) )."/".count( $DAI->getInstances() )."\n";

$props = $DAI->getProperties();
foreach( $props as $prop ){
	echo $prop["property_id"]." : ".$prop["property"]."(".$prop["taxonomy"].")\n";
}

$taxs = $DAI->getTaxonomies();
foreach( $taxs as $tax ){
	echo $tax["taxonomy_id"]." ".$tax["taxonomy"]."\n";
}

echo count( $DAI->getInstances(array("designer"=>"本田このみ")) )."/".count( $DAI->getInstances() )."\n";
echo count( $DAI->getInstances(array("trader"=>"川島商事")) )."/".count( $DAI->getInstances() )."\n";

echo "--END--\n";