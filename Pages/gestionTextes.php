<?php
	session_start();
	require_once('../init.php');
	
	if (empty($_REQUEST['Dirname']) || empty($_REQUEST['Name'])) {
		header('Location:index.php');
	}
	$Params = $_REQUEST;
	
	$ongoing_analysis = 0;
	$modif = false;
	$user=!empty($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:DEFAULT_TEST_USER;
	if (!empty($Params['Administrators'])) {
		$admins = preg_split("/[\s,;]+/", $Params['Administrators']);
		$modif = in_array($user, $admins);
	}
	

	if (!empty($_REQUEST['Analysis']) && $modif) {
	// TODO supprimer le logFile qd c'est terminé
		$logFile = tempnam(RACINE_SITE . 'data', 'analysis');
		$ongoing_analysis = 1;
		//exec(RACINE_SITE . 'pl/analyse_textes.pl ' . $Params['Language1'] . ' ' . CORPUS_SITE . $Params['Dirname']. ' > /dev/null &');
		//exec(RACINE_SITE . 'pl/ajoute_texte_id.pl ' . CORPUS_SITE . $Params['Dirname'] . '/' . DIRXML . '/' . $Params['Language1']. ' > /dev/null &');
		if (!empty($_REQUEST['Language2'])) {
		//	exec(RACINE_SITE . 'pl/analyse_textes.pl ' . $Params['Language2'] . ' ' . CORPUS_SITE . $Params['Dirname'] . ' 2>&1 &', $retArr);
			exec(RACINE_SITE . 'pl/analyse_textes.pl ' . $Params['Language2'] . ' ' . CORPUS_SITE . $Params['Dirname'] . " > /dev/null 2> $logFile &");
		//	exec(RACINE_SITE . 'pl/analyse_textes.pl ' . $Params['Language2'] . ' ' . CORPUS_SITE . $Params['Dirname'] . ' > /dev/null &');
		// TODO attention, il faut ajouter les id qd c'est terminé !
		//	exec(RACINE_SITE . 'pl/ajoute_texte_id.pl ' . CORPUS_SITE . $Params['Dirname'] . '/' . DIRXML . '/' . $Params['Language2']. ' > /dev/null &');
		}
	}
	if (!empty($_REQUEST['Alignment']) && !empty($_REQUEST['Language2']) && $modif) {
		exec(RACINE_SITE . 'pl/aligne_textes.pl ' . $Params['Language1'] . ' ' . $Params['Language2'] . ' ' . CORPUS_SITE . $Params['Dirname'] . '/' . DIRXML. ' > /dev/null &');
	}
	
	function affichep ($param, $default='') {
		global $Params;
		echo !empty($Params[$param])?stripslashes($Params[$param]):$default;
	}
	

	include(RACINE_SITE.'include/header.php');
?>
<header id="enTete">
	<?php print_lang_menu();?>
	<h1><?php echo gettext('iPoCorp : entrepôt de corpus');?></h1>
	<h2><?php echo gettext('Gestion des textes du corpus ');?> <?php affichep('Name');?></h2>
	<hr />
</header>
<section id="partieCentrale">
<?php
	
		$adresseDonnees = $modif?gettext('Adresse WebDAV pour modification des données'):gettext('Adresse WebDAV pour accès aux données');
		echo '<p>',$adresseDonnees,gettext(' : '),'<a href="',CORPUS_DAV,'/',$Params['Dirname'],'">',CORPUS_DAV,'/',$Params['Dirname'],'</a></p>';
		if (!empty($Params['Access']) && $Params['Access'] == 'public' && file_exists(CORPUS_SITE_PUBLIC.'/'.$Params['Dirname'])) {
			echo '<p>',gettext('Adresse Web pour accès public aux données'),gettext(' : '),'<a href="',CORPUS_WEB_PUBLIC,'/',$Params['Dirname'],'">',CORPUS_WEB_PUBLIC,'/',$Params['Dirname'],'</a></p>';
		}
?>
<?php if ($modif) { ?>
<form action="?" method="post">
<fieldset name="<?php echo gettext('Gestion des textes');?>">
<legend><?php echo gettext('Gestion des textes');?></legend>
<div>
<input type="hidden" name="Dirname" value="<?php echo $Params['Dirname']; ?>" />
<input type="hidden" name="Name" value="<?php echo $Params['Name']; ?>" />
<input type="hidden" name="Language1" value="<?php echo $Params['Language1']; ?>" />
<input type="hidden" name="Language2" value="<?php echo $Params['Language2']; ?>" />
<input type="hidden" name="Administrators" value="<?php echo $Params['Administrators']; ?>" />
<input type="submit" name="Analysis" value="<?php echo gettext('Analyse'); ?>" /><br/>
<?php if (!empty($Params['Language2']) && $modif) { 
	echo '<input type="submit" name="Alignment" value="', gettext('Alignement'),'" />';
}
?>
</div>
</fieldset>
</form>
<?php 
}
// TODO afficher correctement le nombre de mots parsés.
   				if ($ongoing_analysis) {
   				echo '<br/>
   	<iframe id="analyze_frame" style="height:400px;width:100%" src="',RACINE_WEB,'include/analyze_frame.php?filename=',$logFile,'" name="analyze_frame" frameborder="0" border="0" src="" scrolling="no" scrollbar="no" > </iframe>';
    			}
 ?>
</section>
<section>
<pre>
<?php
$dir =  CORPUS_SITE . $Params['Dirname'];
$tree = `/usr/local/bin/tree $dir`; 
echo $tree;
?>
</pre>
</section>
<?php 
$footerMenu = ' | <a href="modifCorpus.php?Consulter=on&Dirname='.$Params['Dirname'].'&Name='.$Params['Name'].'">Corpus</a>';
include(RACINE_SITE.'include/footer.php');?>