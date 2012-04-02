<?php

// Securité
if (!defined("_ECRIRE_INC_VERSION")) return;

// Déclaration des objets pris en compte
function inc_squirrel_chimp_definitions(){

	$objets=array('squirrel_chimp'=>array(
		'config'=>'apiKey',
		'fichier_langue'=>'squirrelchimp'));
	
	/*Des plugins peuvent déclarer son objet afin de pouvoir profiter des différents automatismes proposé par le plugin
	 * exemple du plugin squirrel_chimp_campaigns
	 function squirrel_chimp_campaigns_squirrel_chimp_definitions($flux){
	  $valeurs=array(
			'config'=>array( //les variables pour le formulaire configuration
				0=>'article_campagne',
				1=>'rubrique_campagne',
				2=>'campagne_envoyer_directe',
				3=>'campagne_creation_unique',
				),
			'fichier_langue'=>'scc' //le préfixe du fichier langue si différent du nom de l'objet déclaré, sino rien mettre.
			);
		
	$flux['data']['squirrel_chimp_campaigns']=$valeurs; // constitution de l'array avec le nom de l'objet comme clé
	
	
	return $flux;

	}
	*/
	$pipeline= pipeline('squirrel_chimp_definitions',array(
		'data'=>$objets)
		);


	if($pipeline)$objets=$pipeline['data'];

	return $objets;	
	}

?>
