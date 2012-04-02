<?php

// Securité
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('squirrel_chimp_lists_fonctions');



 function formulaires_editer_liste_charger_dist($id_liste='',$listes_mailchimp=''){
	 
	 if(!$listes_mailchimp){
	 
	# API mailchimp
	include_spip('inc/1.3/MCAPI.class');
	
	#pour ecrire_config 
	include_spip('inc/config');
	
	#les fonctions de squirrel_chimp_lists
	include_spip('squirrel_chimp_lists_fonction');
		
		$apikey=lire_config('squirrel_chimp/apiKey');
		
		$listes=recuperer_listes($apikey);
		
		
	if($listes['error']){
		$erreur=$listes['error'];
		$listes_mailchimp=array();
		}
	else $listes_mailchimp=$listes['data'];
	
	}
	
	
	$valeurs = array(
		'titre'=>'',
		'lang'=>'',
		'errors'=>$erreur,		
		'listes_mailchimp'=>$listes_mailchimp,
		'id_liste_mailchimp'=>'',
		'statut'=>'',
		);
		
	if($id_liste){
		$donnees_liste=sql_fetsel('titre,lang,id_liste_mailchimp,statut','spip_listes','id_liste='.$id_liste);
		$valeurs=array_merge($valeurs,$donnees_liste);
		}
	

	
	return $valeurs;
}


function formulaires_editer_liste_verifier_dist($id_liste='',$listes_mailchimp=''){
	$erreurs = array();
	

	// verifier que les champs obligatoires sont bien la :

    foreach(array('titre','lang') as $champ) {
        	if (!_request($champ)) {
            		$erreurs [$champ] = _T('spip:info_obligatoire');
        		}
    	}
	return $erreurs;
}

 

function formulaires_editer_liste_traiter_dist($id_liste='',$listes_mailchimp=''){

	// necessaire pour utiliser lire_config	
	include_spip('inc/config');
	$mailinglists=lire_config('squirrel_chimp/mailinglists');
	$id_liste_mailchimp=_request('id_liste_mailchimp');
	$date=date('y-m-d G:i:s');
	$statut=_request('statut');
	
	if(!$statut)$statut='prive';
	
	$valeurs = array(
		'titre'=>_request('titre'),
		'lang'=>_request('lang'),
		'statut'=>$statut,		
		'id_liste_mailchimp'=>$id_liste_mailchimp,	
		'maj'=>$date	
		);	
	
	
	// Actualisation de la bd
	if(!$id_liste){
		$valeurs['date_creation']=$date;
		$id_liste=sql_insertq('spip_listes',$valeurs);
		$valeurs['message_ok'] = _T('sclp:liste_cree');
		}
	else{
		sql_updateq('spip_listes',$valeurs,'id_liste='.$id_liste);
		$valeurs['message_ok'] = _T('sclp:liste_modifie');
	}
	
	// Actualisation  des metas
	if($id_liste_mailchimp)$mailinglists[$id_liste]=$id_liste_mailchimp;
	else $mailinglists[$id_liste]='';	
	
	ecrire_config("squirrel_chimp/mailinglists",$mailinglists);
	
	$valeurs['editable'] = false;

	#Retour succes 
	
	


	return $valeurs;
}

?>
