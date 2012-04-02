<?php

if (!defined("_ECRIRE_INC_VERSION")) ;

// Crée una campagne à partir d'un article
function inc_article_to_campagne_dist($flux='',$id_article='',$unique=true){
		// Necessaire pour utiliser les autorisations
		include_spip('inc/autoriser');

		//API mailchimp
		include_spip('inc/1.3/MCAPI.class');

		// Necessaire pour utiliser lire_config
		include_spip('inc/config');
		
		//Les fonctions
		include_spip('squirrel_chimp_campagnes_fonctions');

		//Récuperation de la config
		$apiKey = lire_config("squirrel_chimp/apiKey");
		$lists_sync = lire_config("squirrel_chimp/mailinglists");
		$article_campagne = lire_config("squirrel_chimp/article_campagne");
		$campagne_envoyer_directe = lire_config("squirrel_chimp/campagne_envoyer_directe");
		if($unique)$campagne_creation_unique = lire_config("squirrel_chimp/campagne_creation_unique");
		$rubrique_campagne = lire_config("squirrel_chimp/rubrique_campagne");
		
		// On ne fait rien si la création automatique est désactivé
		
		if($article_campagne){
			$form_statut=' AND statut="publie"';
			
			if($flux['args']['statut_ancien']){
				$statut_ancien=$flux['args']['statut_ancien'];
				$form_statut=' AND statut="publie"';
				}
			
			
			//Décoder ce que saisies a enregistré
			$rubriques=array();
			if(is_array($rubrique_campagne)){
			foreach($rubrique_campagne AS $value){
				list($objet,$rubriques[])=explode('|',$value);			
				}
			}
	
			//on verifie que les parametres du plugin mailchimp sont initialisées
			if ($apiKey){
				spip_log(__LINE__,'squirrel_chimp');
				//spip_log($apiKey,'squirrel_chimp');
	
				// initialisation d'un objet mailchimp
				$api = new MCAPI($apiKey);
	
	
				// Données article
				if(!$id_article)$id_article= $flux['data']['id_article'];
				$article=sql_fetsel('statut,titre,texte,descriptif,id_rubrique','spip_articles','id_article='.$id_article.$statut);
				
	
				if(!$statut=$flux['data']['statut'])$statut=$article['statut'];
				if(_request('new')=='new')	$new=true;
				$message_ok = $flux['data']['message_ok'];
				
				$options=array(
					'subject'=>$article['titre'],
					'title'=>$article['titre'],
					
					);
					
				$contexte=array('id_article'=>$id_article,'statut'=>$statut_ancien?$statut_ancien:$statut);	
	
				$html=recuperer_fond('prive/campaign/texte',$contexte);
				//Affichage en texte, débugger d'abord
				/*$t=charger_fonction('html2plain','inc');
				$text=$t($html);*/
				
				
				
				$content = array(
							'html'=>$html, 
							//'text' => $text.'*|UNSUB|*'
							);
				// Création d'une nouvelle campagne lors de l'actualisation d'un article
				if ($statut=='publie' AND !$campagne_creation_unique ){
					
						// Vérifier si on se trouve dans la bonne rubrique
						$actualiser=true;
						if (count($rubriques)>0){
							if (!in_array($article['id_rubrique'],$rubriques))$actualiser=false;
							}
						spip_log('Actualiser :'.($actualiser?'oui':'non'),'squirrel_chimp');
						if($actualiser){
							spip_log('Créer une campagne','squirrel_chimp');
							include_spip('squirrel_chimp_lists_fonctions');
							foreach($lists_sync AS $listId=>$value){
								spip_log("liste : $listId",'squirrel_chimp');
								//$donnees_list=
								$options['list_id'] = $listId;
								
								//Recuperer les infos de la liste
								$liste=recuperer_listes($apiKey,array('filters'=>$listId));
								
								spip_log($liste,'squirrel_chimp');
								
								$type='regular';
								$options['from_email'] = $liste['data'][0]['default_from_email']; 
								$options['from_name'] = $liste['data'][0]['default_from_name'];
								$creer_liste=charger_fonction('creer_campagne','inc');
								$retour=$creer_liste($apiKey,$type,$options,$content,$segment_opts,$type_opts);
								
								spip_log($retour,'squirrel_chimp');
								
								//Envoyer la campagne si demandé
								if($campagne_envoyer_directe AND $retour['id_campagne']){
									$envoyer_campagne=charger_fonction('envoyer_campagne','inc');
									$retour_envoyer=$envoyer_campagne($apiKey,$retour['id_campagne']);
									spip_log($retour_envoyer,'squirrel_chimp');
									}
								
								/*L'affichage des erreurs provoque "Cannot redeclare instituer_article()" . je ne vois pas ce qui cloche, donc désactive pour lemoment
								 * $flux['data']=array('message_erreur'=>$retour['message_erreur']);
								 */
								return $flux;
								}
		
							} 
	
						}
	
					} //($apiKey and $listId)
			else {
				// n'effrayons pas l utilisateur classique
				spip_log(__LINE__);
				if (autoriser("configurer", "mailchimp")){
					spip_log(__LINE__);
					//erreur il faut configurer le plugin mailchimp
					$flux['data'] = array('message_erreur' => _T('mailchimp:config_erreur'));
					spip_log("Admin"._T('squirrelchimp:config_erreur'));
					return $flux;
				}
				else {
					spip_log(__LINE__);
					// que le spiplog si on est juste un user
					spip_log(_T('squirrelchimp:config_erreur'));
					return $flux;
				} // autoriser
	
				spip_log(__LINE__);
			} // if ( $apiKey and $listId )	{
	
			spip_log(__LINE__);
		}
		
	return $flux;	
	}
?>
