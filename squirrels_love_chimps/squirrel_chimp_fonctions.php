<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

// filtre pour obtenir les objets gérées par le plugin
function objets_config($flux){
	
	// charge la définition
	$api_objets=charger_fonction('squirrel_chimp_definitions','inc');
	$objets= $api_objets();
	
	return $objets;
	}
?>
