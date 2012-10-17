<?php

// Securité
if (!defined("_ECRIRE_INC_VERSION")) return;

# API mailchimp
include_spip('inc/1.3/MCAPI.class');

#pour ecrire_config 
include_spip('inc/config');



 
/**
 * Formulaire de configuration du plugin Mailchimp
 * On vérifie juste que l'on peut se connecter à l'API mailchimp   
 * ( et on stocke les 5 premiers abonnés de la liste pour passer à traiter) 
 */
 function formulaires_configurer_squirrel_chimp_charger_dist($objets,$part){

	
	$valeurs = array('objets'=>$objets,'part'=>$part);	
	
	// On filtre les valeurs de l'objet courant
	$objet_courant=$objets[$part];
	
	// On détermine le prefixe pour le fichier de langue
	$valeurs['fichier_langue']=$objet_courant['fichier_langue']?$objet_courant['fichier_langue']:'squirrel_chimp';
	
	$objet_courant_champ=$objet_courant['config'];
	
	// Les valeurs des différents champs du formulaire
	if(!is_array($objet_courant_champ))
		$valeurs[$objet_courant_champ] = _request($objet_courant_champ)? _request($objet_courant_champ) : lire_config('squirrel_chimp/'.$objet_courant_champ);
	else{
		foreach($objet_courant_champ AS $objet2){
			$valeurs[$objet2] = _request($objet2)? _request($objet2) : lire_config('squirrel_chimp/'.$objet2);
			}
		
		}
	
	
	/* D'autres plugins peuvent y ajouter du contenu à conditionn d'avoir ajouter ses valeurs de défintion via la pipeline squirrel_chimp_definitions et de disposer d'un fichier /formulaires/NOM_OBJET_DECLARE_charger.php qui contient une fonction
	 * 
	 * function formulaires_NOM_OBJET_DECLARE_config_charger_dist($valeurs){
	 * 
	 * return $valeurs,
	 * }
	 * 
	 * 
	*/
	
	if(find_in_path('formulaires/'.$part.'_config_charger.php')){
		$extra=charger_fonction($part.'_config_charger','formulaires');
		$valeurs=$extra($valeurs);
		}
	
	return $valeurs;
}
 
 
function formulaires_configurer_squirrel_chimp_verifier_dist($objets,$part)
{
	$valeurs = array();
	
	/* D'autres plugins peuvent y ajouter du contenu à conditionn d'avoir ajouter ses valeurs de défintion via la pipeline squirrel_chimp_definitions de disposer d'un fichier /formulaires/NOM_OBJET_DECLARE_verifier.php qui contient une fonction
	 * 
	 * function formulaires_NOM_OBJET_DECLARE_config_verifier_dist($valeurs){
	 * 
	 * return $valeurs,
	 * }
	 * 
	 * */
	
	if(find_in_path('formulaires/'.$part.'_config_verifier.php')){
		$extra=charger_fonction($part.'_config_verifier','formulaires');
		$valeurs=$extra($valeurs);
		}

	return $valeurs;
}



/**
 * Formulaire de configuration du plugin Mailchimp
 * On traite l'information : sauvegarde dans une meta et 
 * affichage du succes dans une belle boite .     
 * 
 */


function formulaires_configurer_squirrel_chimp_traiter_dist($objets,$part){
	
	$valeurs = array();

	$valeurs['objets']=$objets;	
	
	$objet_courant=$objets[$part];
	
	$objet_courant_champ=$objet_courant['config'];
	
	
	//Ecriture des parametres dans META 

	if(!is_array($objet_courant_champ)){
		ecrire_config("squirrel_chimp/$objet_courant_champ",_request($objet_courant_champ));
		if(_request('apiKey'))$log .= $objet_courant_champ.'='._request($objet_courant_champ)."\n";
		}
	else{
		foreach($objet_courant_champ AS $objet2){
			ecrire_config("squirrel_chimp/$objet2", _request($objet2) );
			if(_request($objet2))$log .= $objet2.'='._request($objet2)."\n";
			}
		
		}

	spip_log ("Plugin mailchimp/ sauvegarde de la configuartion meta: $log. $objet_courant_champ",'squirrel_chimp');


	#Retour succes 
	
	$valeurs['message_ok'] = _T('squirrelchimp:retour_test_api');
	
	/*D'autres plugins peuvent y ajouter du contenu à conditionn d'avoir ajouter ses valeurs de défintion via la pipeline squirrel_chimp_definitions de disposer d'un fichier /formulaires/NOM_OBJET_DECLARE_traiter.php qui contient une fonction
	 * 
	 * function formulaires_NOM_OBJET_DECLARE_config_traiter_dist($valeurs){
	 * 
	 * return $valeurs,
	 * }
	 * 
	 * */
	
	if(find_in_path('formulaires/'.$part.'_config_traiter.php')){
		$extra=charger_fonction($part.'_config_traiter','formulaires');
		$valeurs=$extra($valeurs);
		}

	return $valeurs;
}

?>
