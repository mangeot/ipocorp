<?php
	session_start();
	require_once('../init.php');
	
	if (empty($_REQUEST['Dirname']) || empty($_REQUEST['Name'])) {
		header('Location:index.php');
	}
	$metadataFile = CORPUS_SITE.'/'.$_REQUEST['Dirname']."/".$_REQUEST['Name'].'-metadata.xml';
	}
	$Params = parseCorpus($metadataFile);
  	$Params['Dirname'] = $_REQUEST['Dirname'];
	
	$ongoing_analysis = 0;
	$modif = false;
	$user=!empty($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:DEFAULT_TEST_USER;
	if (!empty($Params['Administrators'])) {
		$admins = preg_split("/[\s,;]+/", $Params['Administrators']);
		$modif = in_array($user, $admins);
	}
	
	
	$sr = $ISO6392TO1[$Params['Source']];
	if (!empty($Params['Target'])) {
		$tr = $ISO6392TO1[$Params['Target']];
	}
	if (!empty($_REQUEST['Analysis']) && $modif) {
	// TODO supprimer le logFile qd c'est terminé => avec un cron
		$logFile = tempnam(RACINE_SITE . 'data', 'analysis');
		$ongoing_analysis = 1;
		exec(RACINE_SITE . 'pl/analyse_textes.pl ' . $Params['Source'] . ' ' . $sr . ' ' . CORPUS_SITE . $Params['Dirname']. " > /dev/null 2> $logFile &");
		if (!empty($_Params['Target'])) {
			exec(RACINE_SITE . 'pl/analyse_textes.pl ' . $Params['Target'] . ' ' . $tr . ' ' . CORPUS_SITE . $Params['Dirname'] . " > /dev/null 2>> $logFile &");
		}
	}
	if (!empty($_REQUEST['Alignment']) && !empty($_Params['Target']) && $modif) {
		$logFile = tempnam(RACINE_SITE . 'data', 'alignment');
		$ongoing_analysis = 1;
		exec(RACINE_SITE . 'pl/aligne_textes.pl ' . $Params['Source'] . ' ' . $Params['Target'] . ' ' . $sr . ' ' . $tr . ' ' . CORPUS_SITE . $Params['Dirname'] . '/' . DIRXML. " > /dev/null 2> $logFile &");
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
	
		$adresseDonnees = $modif?gettext('Adresse WebDAV pour ajouter des textes'):gettext('Adresse WebDAV pour accéder aux textes');
		echo '<p>',$adresseDonnees,gettext(' : '),'<a href="',CORPUS_DAV,'/',$Params['Dirname'],'">',CORPUS_DAV,'/',$Params['Dirname'],'</a></p>';
		if (!empty($Params['Access']) && $Params['Access'] == 'public' && file_exists(CORPUS_SITE_WEB_PUBLIC.'/'.$Params['Dirname'])) {
			echo '<p>',gettext('Adresse Web pour accès public aux données'),gettext(' : '),'<a href="',CORPUS_WEB_PUBLIC,'/',$Params['Dirname'],'">',CORPUS_WEB_PUBLIC,'/',$Params['Dirname'],'</a></p>';
		}
		else {
			echo '<p>',gettext('Adresse Web pour accès protégé aux données'),gettext(' : '),'<a href="',CORPUS_WEB,'/',$Params['Dirname'],'">',CORPUS_WEB,'/',$Params['Dirname'],'</a></p>';
		}
	 if ($modif) { ?>
<form action="?" method="post">
<fieldset name="<?php echo gettext('Gestion des textes');?>">
<legend><?php echo gettext('Gestion des textes');?></legend>
			<label class="collapse" for="_1"><?php echo gettext('Format des données'),gettext(' : ');?></label>
				<input id="_1" type="checkbox" /> 
				<div><p>Le format des données doit être le suivant :</p>
				<ul><li>Les textes doivent être encodés en Unicode UTF-8 dans des fichiers comportant l'extension ".txt".</li>
				<li>Les noms de fichiers ne doivent pas comporter d'espaces, d'accents, de guillemets ou d'apostrophes.</li>
				<li>Il est possible d'avoir une hiérarchie de sous-dossiers de fichiers pour chaque langue mais celle-ci doit être la même pour 
				toutes les langues.</li>
				<li>Les fichiers de chaque langue doivent être regroupés séparément dans un dossier portant le nom
				du code langue à 3 lettres ISO-639-2 (fra pour français).</li>
				<li>Les dossiers portant le nom du code langue doivent être regroupés dans le sous-dossier "TXT" du corpus.</li>
				</ul>
				<p>Voir les exemples suivants : </p>
				<div style="display:table; margin:auto">
				<figure style="display:table-cell; padding: 20px; text-align: center;"><img src="<?php echo RACINE_WEB;?>images/tree-1q84.png" alt="Hiérarchie de fichiers du corpus 1Q84"/>
				<figcaption>Hiérarchie de fichiers du corpus 1Q84</figcaption>
				</figure>
				<figure style="display:table-cell; padding: 20px; text-align: center;"><img src="<?php echo RACINE_WEB;?>images/tree-lmd.png" alt="Hiérarchie de fichiers du corpus du monde diplomatique"/>
				<figcaption>Hiérarchie de fichiers du corpus du monde diplomatique</figcaption>
				</figure>
				</div>
				</div>
<div>
<input type="hidden" name="Dirname" value="<?php echo $Params['Dirname']; ?>" />
<input type="hidden" name="Name" value="<?php echo $Params['Name']; ?>" />
<input type="hidden" name="Language1" value="<?php echo $Params['Language1']; ?>" />
<input type="hidden" name="Language2" value="<?php echo $Params['Language2']; ?>" />
<input type="hidden" name="Administrators" value="<?php echo $Params['Administrators']; ?>" />
<input type="submit" name="Analysis" value="<?php echo gettext('Analyse'); ?>" /> des textes : ajout de balises XML et analyse syntaxique<br/>
<?php if (!empty($Params['Language2']) && $modif) { 
	echo '<input type="submit" name="Alignment" value="', gettext('Alignement'),'" /> des fichiers XML source et cible avec hunalign';
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
   	<iframe id="analyze_frame" style="height:400px;width:100%" src="',RACINE_WEB,'include/analyze_frame.php?filename=',$logFile,'" name="analyze_frame" frameborder="0" border="0" src=""> </iframe>';
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
