<?php

function sclp_upgrade($nom_meta_base_version, $version_cible){
	$maj = array();

	$maj['create'] = array(
		array('maj_tables',array('spip_listes','spip_auteurs_listes','spip_auteurs','spip_listes_syncro')),	
	);
	$maj['0.0.3'] = array(
		array('maj_tables',array('spip_listes','spip_auteurs_listes','spip_auteurs','spip_listes_syncro')),	
	);
	$maj['0.2.14'] = array(
		array('maj_tables',array('spip_listes','spip_auteurs_listes','spip_auteurs','spip_listes_syncro')),	
	);

                
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/**
 * Fonction de désinstallation du plugin.
**/
function sclp_vider_tables($nom_meta_base_version) {

	sql_drop_table("spip_produits");

	# Nettoyer les versionnages et forums
	sql_delete("spip_listes", sql_in("objet", array('produit')));
	sql_delete("spip_auteurs_listes",sql_in("objet", array('produit')));
	sql_delete("spip_listes_syncro",sql_in("objet", array('produit')));

	effacer_meta($nom_meta_base_version);
}

?>
