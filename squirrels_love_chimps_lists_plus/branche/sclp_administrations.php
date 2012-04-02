<?php

function sclp_upgrade($nom_meta_base_version, $version_cible){
	$maj = array();

	$maj['create'] = array(
		array('maj_tables',array('spip_listes','spip_auteurs_listes','spip_auteurs')),	
	);
	$maj['0.0.3'] = array(
		array('maj_tables',array('spip_listes','spip_auteurs_listes','spip_auteurs')),	
	);

                
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}
?>
