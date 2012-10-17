<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


	    
function sclp_upgrade($nom_meta_base_version, $version_cible){
	$current_version = 0.0;
	if ((!isset($GLOBALS['meta'][$nom_meta_base_version]))
	|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){
		if ($current_version==0.0){
            include_spip('base/create');
            creer_base();
            maj_tables('spip_auteurs');	 
            maj_tables('spip_listes_syncro');	 
            maj_tables('spip_listes');	 
            maj_tables('spip_auteurs_listes');	              
            sql_updateq('spip_listes',array('statut'=>'prive'),"statut=''");	
			sql_updateq('spip_listes',array('statut'=>'poubelle'),"statut='inact'");
			sql_updateq('spip_listes',array('statut'=>'prive'),"statut='liste'");
			ecrire_meta($nom_meta_base_version, $current_version=$version_cible);	
            }
		
		

	}

}	    
?>
