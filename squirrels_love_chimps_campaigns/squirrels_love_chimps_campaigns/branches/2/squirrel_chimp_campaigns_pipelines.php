<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

// ajouter les objets à l'api de configuration
function squirrel_chimp_campaigns_squirrel_chimp_definitions($flux){
	
	$valeurs=array(
			'config'=>array(
				0=>'article_campagne',
				1=>'rubrique_campagne',
				2=>'campagne_envoyer_directe',
				3=>'campagne_creation_unique',
				),
			'fichier_langue'=>'scc'
			);
		
	$flux['data']['squirrel_chimp_campaigns']=$valeurs;
	
	
	return $flux;

	}


// Création de campagnes lors de la modification d'un article
function squirrel_chimp_campaigns_formulaire_traiter($flux)
{

	// on recupere d'abord le nom du formulaire .
	// car c'est un pipeline donc tout formulaire passe dedans ( prive ou public)
	$formulaire = $flux['args']['form'];
	spip_log(__LINE__,'squirrel_chimp');

	//dans notre cas c'est le formulaire mesabonnes (du plugin mes_abonnes) qui nous interesse
	if ($formulaire=="editer_article"){
		$id_article= $flux['data']['id_article'];
		
		// Préparation et envoi de la campagne
		$campagne=charger_fonction('article_to_campagne','inc');
		$flux=$campagne($flux,$id_article);
	}
	spip_log(__LINE__);
	return $flux ;
	
}

// Création de campagne lors de la publication d'un article

function squirrel_chimp_campaigns_pre_edition($flux){
	
	//Préparation des données
	$statut_ancien=$flux['args']['statut_ancien'];
	$id_article=$flux['args']['id_objet'];
	$table=$flux['args']['table'];
	$action=$flux['args']['action'];	
	$statut_actuel=$flux['data']['statut'];

	
	// Préparation et envoi de la campagne
	if($statut_ancien!='publie' AND $statut_actuel=='publie' AND $table=='spip_articles' AND $action=='instituer'){
		spip_log("statut ancien : $statut_ancien - statut nouveau: $statut_actuel - id_article: $id_article",'squirrel_chimp');
		$campagne=charger_fonction('article_to_campagne','inc');
		$flux=$campagne($flux,$id_article,false);
		}
	return $flux;
	}



?>
