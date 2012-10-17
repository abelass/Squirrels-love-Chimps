<?php

function formulaires_squirrel_chimp_lists_config_traiter_dist($valeurs){
	include_spip('squirrel_chimp_lists_fonctions');
	include_spip('inc/config');
	
	
	$valeurs['message_ok'] = _T('scl:enregistrement_ok');
	if(_request('sync_auteurs')){
		
		#recuperation de la config
		$apiKey = _request("apiKey");
	
	
		spip_log("Plugin mailchimp form vérifier : $apiKey ",'squirrel_chimp_lists') ;
		
		// Composition du batch
		
		$batch=donnees_sync('','','','spip_auteurs.statut!="5poubelle"');
		
		// initialisation d'un objet mailchimp
		$apiKey=lire_config('squirrel_chimp/apiKey');	
		
		$api = new MCAPI($apiKey);
		
		// les variables
		$optin = lire_config('squirrel_chimp/ml_opt_in')?false:true; //yes, send optin emails
		$up_exist = true; // yes, update currently subscribed users
		$replace_int = false; // no, add interest, don't replace
		
		// les listes à actualiser
		$lists_sync=lire_config('squirrel_chimp/mailinglists');
		
		foreach($lists_sync AS $listId=>$value){
	
		
		$vals = $api->listBatchSubscribe($listId,$batch,$optin, $up_exist, $replace_int); 
			if ($api->errorCode){
				$log .=_T('scl:sync_error')." : \n";
			    $log .= _T('scl:sync_echec_batch')."\n";
				$log .= _T('scl:sync_code').$api->errorCode."\n";
				$log .= _T('scl:sync_message').$api->errorMessage."\n";
			} else {
				$log .=_T('scl:sync_ok')."<br/>\n";
				$log .= _T('scl:sync_ajoute').$vals['add_count']."<br/>\n";
				$log .= _T('scl:sync_actualise').$vals['update_count']."<br/>\n";
				$log .= _T('scl:sync_erreurs').$vals['error_count']."<br/>\n";
				if(is_array($vals['errors'])){
					foreach($vals['errors'] as $val){
						$log .= $val['email_address']._T('scl:sync_echec')."<br/>\n";
						$log .= _T('scl:sync_code').$val['code']." <br/>\n";
						$log .= _T('scl:sync_message').$val['message']." \n";
					}
					}
				}
			$valeurs['message_ok'] = $log;	
			}
		}		
		// Pipeline pour intervenir dans le traitement de ce formulaire	
		$pipeline= pipeline('squirrel_chimp_lists_config_traiter',array(
			'data'=>$valeurs)
			);
	
		spip_log($vals,'squirrel_chimp_lists') ;
		if($pipeline)$objets=$pipeline['data'];	
		

	
return $valeurs;
}
?>
