<?php

function inc_editer_auteur_traiter_listes_dist($flux=''){		
		// necessaire pour utiliser les autorisations
		include_spip('inc/autoriser');


		// API mailchimp
		include_spip('inc/1.3/MCAPI.class');

		// necessaire pour utiliser lire_config
		include_spip('inc/config');
		
		//les fonctions
		
		include_spip('squirrel_chimp_lists_fonctions');

		//recuperation de la config
		$apiKey = lire_config("squirrel_chimp/apiKey");
		$lists_sync = lire_config("squirrel_chimp/mailinglists");
		$ajouter = lire_config("squirrel_chimp/ml_act_ajout");
		$enlever = lire_config("squirrel_chimp/ml_act_enleve");
		$actualiser = lire_config("squirrel_chimp/ml_act_actualise");
		$optin = lire_config('squirrel_chimp/ml_opt_in')?false:true; //yes, send optin emails

		//on verifie que les parametres du plugin mailchimp sont initialisées
		if ($apiKey){

			spip_log('actualisation_auteur','squirrel_chimp_lists');

			// initialisation d'un objet mailchimp
			$api = new MCAPI($apiKey);


			// les donnés de l'auteur
			$id_auteur = $flux['data']['id_auteur'];
			$statut=sql_getfetsel('statut','spip_auteurs','id_auteur='.$id_auteur);
			
			//déterminer s'ilm s'agir d'un nouveau auteur
			if(_request('new')=='new')	$new=true;
			$message_ok = $flux['data']['message_ok'];

			// compilation des informations à envoyer à MailChimp
			$donnees_auteur=donnees_sync('','',$id_auteur);
			
			spip_log($donnees_auteur,'squirrel_chimp_lists');	
			$donnees_auteur=$donnees_auteur[1];
			$email=$donnees_auteur['EMAIL'];

			
			// Actualisation de la liste avec un nouvel auteur ou si activé actualisation des données si modification du profil
			if (($new AND $ajouter) OR (!$new AND $actualiser AND $statut!='5poubelle')){
				spip_log('ajouter ou actualiser','squirrel_chimp_lists');
				
				// By default this sends a confirmation email - you will not see new members
				// until the link contained in it is clicked!
				// listSubscribe(string apikey, string id, string email_address, array merge_vars, string email_type, bool double_optin, bool update_existing, bool replace_interests, bool send_welcome)
				
				// Inscription pour chaque liste
				foreach($lists_sync AS $listId=>$value){
					inscription_liste_mc($flux,$api,$listId,$email,$donnees_auteur,$email_type,$optin,$new?false:true);
				}

			} 
			// Se le statut est mis à "poubelle", on le désinscrit
			if ($statut=='5poubelle' AND $enlever){
				foreach($lists_sync AS $listId=>$value){
					desinscription_liste_mc($flux,$api,$listId,$email);
				}
			}


		} //($apiKey and $listId)
		else {
			// n'effrayons pas l utilisateur classique
			spip_log(__LINE__);
			if (autoriser("configurer", "mailchimp")){
				spip_log(__LINE__);
				//erreur il faut configurer le plugin mailchimp
				$flux['data'] = array('message_erreur' => _T('squirrelchimp:config_erreur'));
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
		return $flux;
	}
?>
