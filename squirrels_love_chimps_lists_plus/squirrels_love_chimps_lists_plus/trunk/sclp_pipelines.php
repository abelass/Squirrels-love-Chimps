<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


// Ajoute le foormulaire abonnement à la ficher auteurs
function sclp_affiche_milieu($flux){
	$exec=$flux['args']['exec'];
	
	if($exec=='auteur_infos' OR $exec=='auteur'){
			$flux['args']['editer_abonnements']=_request('editer_abonnements');
			include_spip('inc/filtres_images');	
			include_spip('inc/layer');	
			include_spip('inc/presentation');	
			$deplier=_request('editer_abonnements')?true:false;
			$image=extraire_attribut(image_reduire(find_in_path('images/letter_64.png'),24),'src');		
			$contenu= recuperer_fond('prive/squelettes/affiche_milieu/auteur_listes',$flux['args'],array('ajax'=>true));
			$flux["data"] .= cadre_depliable($image,strtoupper(_T('sclp:gerer_abonnements_listes')),$deplier,$contenu,'gerer_abonnements_listes','e');    	
		}	
	return $flux;
}
	
	
// ajouter les objets à l'api de configuration
function sclp_squirrel_chimp_definitions($flux){
	

	$flux['data']['sclp']=$valeurs;
	
	//On rajoute un champ à la config
	$flux['data']['squirrel_chimp_lists']['config'][100]='editer_champs';

	
	//$flux['data']['sclp']['config'][6]='sync_auteurs';
	
	
	return $flux;

	}

// Ajouter un traitement au formulaire de configuration , partie listes
function sclp_squirrel_chimp_lists_config_traiter($flux){
	
	include_spip('squirrel_chimp_lists_fonctions');
	
	$mailinglists=_request('mailinglists');
	
	foreach($mailinglists AS $id_liste=>$id_liste_mailchimp){
	
		$valeurs = array(	
			'id_liste_mailchimp'=>$id_liste_mailchimp,	
			'maj'=>$date	
			);	
		sql_updateq('spip_listes',$valeurs,'id_liste='.$id_liste);
	}
	
	return $flux;
	}
// Les tâches cron

function sclp_taches_generales_cron($taches){
    $taches['syncro_listes'] = 60*10; // toutes les 10 minutes
    
    return $taches;
}

// Insertion de la feuille de style dans l'espace privé
function sclp_header_prive($flux){

	$flux .= '<link rel="stylesheet" href="'.find_in_path('css/sclp.css').'" type="text/css" media="all" />';
	
	return $flux;
}
	
?>
