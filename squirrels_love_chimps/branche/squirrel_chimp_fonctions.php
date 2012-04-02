<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

// filtre pour obtenir les objets gérées par le plugin
function objets_config($flux){
	
	// charge la définition
	$api_objets=charger_fonction('squirrel_chimp_definitions','inc');
	$objets= $api_objets();
	
	return $objets;
	}

// Construction de la chaine de langue pour spip 2

function recuperer_chaine($prefixe='',$chaine){
	
	if($prefixe)$prefixe=$prefixe.':';	
	$texte=_T($prefixe.$chaine);	
	
	return $texte;
	}	
	
?>
