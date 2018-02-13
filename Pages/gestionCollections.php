<?php
	require_once('../init.php');

	$user = !empty($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:DEFAULT_TEST_USER;
	$collectiondir = CORPUS_SITE.DIRCOLLECTIONS;
	`mkdir -p $collectiondir`;
	$ongoing_analysis = 0;
	$generatePid = '';
	$tri = !empty($_REQUEST['tri'])?$_REQUEST['tri']:'Nom';
	

	$Collection = array();
	if (!empty($_REQUEST['Corpus'])) {
		$Collection = $_REQUEST['Corpus'];
	}

# initialise la liste des corpus
	$corpora = getCorpora(); 
	
# supprimer la collection
	if (!empty($_REQUEST['SupprimerCollection']) && 
		!empty($_REQUEST['Name'])) {
		@unlink($collectiondir . '/'. $_REQUEST['Name'] . '-metadata.xml');	
		$registre = DIRCWB . '/registry/'.$_REQUEST['Name'];
		`rm -rf $registre\-fr`;
		`rm -rf $registre\-ja`;
		$data = DIRCWB . '/data/'.$_REQUEST['Name'];
		`rm -rf $data`;
	}

# crée puis enregistre la collection 
	if (!empty($_REQUEST['EnregistrerCollection']) && 
		!empty($_REQUEST['Name']) && 
		!empty($_REQUEST['Source']) &&
		!empty($_REQUEST['Target'])) {
		$collecMetadata = creerCollectionMetadata($_REQUEST['Name'],$_REQUEST['Source'],$_REQUEST['Target'],$Collection, $user);
		$myFile = $collectiondir ."/".$_REQUEST['Name'].'-metadata.xml';
		$fh = fopen($myFile, 'w') or die("impossible d'ouvrir le fichier ".$myFile);
		fwrite($fh, $collecMetadata);
		fclose($fh);
		// echo 'Collection créée !';
	}
	
# initialise la liste des collections (après avoir créé la nouvelle collection)
		$collections = getCollections();
		

				
# génère le registre CWB de la collection sélectionnée
	if (!empty($_REQUEST['GenererCollection']) && 
		!empty($_REQUEST['Name'])) {
		$colname = $_REQUEST['Name'];
		$collection = $collections[$colname];
		// TODO supprimer le logFile qd c'est terminé => avec un cron
		$logFile = tempnam(RACINE_SITE . 'data', 'generation');
		$ongoing_analysis = 1;
		$linksarray = array();
		
		foreach ($collection['Corpora'] as $corpusname) {
			$corpus = $corpora[$corpusname];
			$dirpath = CORPUS_SITE . $corpus['Dirname'] . '/' . DIRXML . '/' . DIRLINKS;
			if (REF_SITE != '') {
				$dirref = CORPUS_SITE . $corpus['Dirname'] . '/' . DIRREF;
				$refsite = REF_SITE;
				`cp $dirref/*.html $refsite/.`;
			}
			
			$corpuslinks = select_files($dirpath,'/\.xml$/');
			//$corpuslinks = $dirpath . '/*.xml';
			$linksarray = array_merge($linksarray,$corpuslinks);
			//$linksarray[] = $corpuslinks;
		}
		$linksfiles = implode(' ',$linksarray);
		$command = RACINE_SITE . 'pl/cree_corpus_cwb.pl ' . $colname . ' ' . $collection['sr'] . ' ' . $collection['tr']. ' ' . $linksfiles;
		//echo 'Commande : ',$command;
$generatePid = `nohup $command > /dev/null 2>&1 & echo $!`;
//echo $generatePid;
	}

# modifierCollection
	$Params = array();
	if (!empty($_REQUEST['ModifierCollection']) && !empty($_REQUEST['Name'])) {
		$metadataFile = $collectiondir ."/".$_REQUEST['Name'].'-metadata.xml';
		
		if (file_exists($metadataFile)) {
			$doc = new DOMDocument();
			$doc->load($metadataFile);
			$dicts = $doc->getElementsByTagName("collection-metadata");
			$dict = $dicts->item(0);
			$Params['Name'] = $dict->getAttribute('name');
			$Params['Source'] = $dict->getElementsByTagName('source-language')->item(0)->getAttribute('d:lang');
			$Params['Target'] = $dict->getElementsByTagName('target-language')->item(0)->getAttribute('d:lang');
			$Params['CreationDate'] = $dict->getAttribute('creation-date');
			$Params['InstallationDate'] = $dict->getAttribute('installation-date');
			$administrators = $dict->getElementsByTagName('user-ref');
			$adminString = '';
			foreach ($administrators as $user) {
				$adminString .= $user->getAttribute('name') . ',';
			}
			$Params['Administrators'] = trim($adminString,',');
			$lescorpus = $dict->getElementsByTagName('corpus');
			foreach ($lescorpus as $corpus) {
				$corpusString = $corpus->getAttribute('name');
				$Collection[] = $corpusString;
			}
		}
	}
	else {
		$Params = $_REQUEST;
	}

# CreerCollection
	if (empty($Params['Source'])) $Params['Source'] = '';
	if (empty($Params['Target'])) $Params['Target'] = '';
	if (empty($Params['Name'])) $Params['Name'] = '';
	
	include(RACINE_SITE.'include/header.php');
?>
<header id="enTete">
	<?php print_lang_menu();?>
    <h1><?php echo gettext('iPoCorp : entrepôt de corpus');?></h1>
	<h2><?php echo gettext('Gestion des collections');?></h2>
	<hr />
</header>
<section id="partieCentrale">
<?php if (empty($_REQUEST['GenererCollection']) && empty($_REQUEST['SupprimerCollection'])) { ?>
	<h3  style="text-align:center;"><?php echo gettext('Création/modification d\'une collection');?></h3>
   <form action="?" style="text-align:center;" method="post">
	<div style="margin:auto;display:inline-block;text-align:left;">
	<label><?php echo gettext('Nom de la collection'), gettext(' : ');?><input type="text" name="Name" placeholder="<?php echo gettext('macollection');?>" required="required"  pattern="[a-z]+" value="<?php echo $Params['Name']?>"/> <?php echo gettext('Ne doit contenir que des minuscules non accentuées.');?> [a-z]+</label>
	<p><label><?php echo gettext('Langue source'),gettext(' : ');?>
		<select name="Source"  onchange="this.form.submit()">
			<option value=""><?php echo gettext('Choisir...');?></option>
		<?php afficheLanguesOptions($Params['Source']); ?>
		</select></label></p>
	<p><label><?php echo gettext('Langue cible'),gettext(' : ');?>
		<select name="Target"  onchange="this.form.submit()">
			<option value=""><?php echo gettext('Choisir...');?></option>
		<?php afficheLanguesOptions($Params['Target']); ?>
		</select></label></p>	
	<p><?php echo gettext('Liste des corpus'),gettext(' : ');?></p>
	</div>
	<table>
		<thead>
			<tr><th>&nbsp;</th>
			<th><a href="?tri=Nom"><?php echo gettext('Nom'); ?></a></th>
			<th><?php echo gettext('Catégorie'); ?></th>
			<th><?php echo gettext('Type'); ?></th>
			<th><?php echo gettext('Administrateur'); ?></th>
			<th><a href="?tri=Couple"><?php echo gettext('Couple'); ?></a></th>
			<th><a href="?tri=Source"><?php echo gettext('Source'); ?></a></th>
			<th><a href="?tri=Cible"><?php echo gettext('Cible'); ?></a></th>
			<th><?php echo gettext('Mots'); ?></th>
			<th><?php echo gettext('Liens'); ?></th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php
	$entrees = 0;
	$style='odd';
	$i=1;
	if ($tri == 'Source') {
		uasort($corpora,'srccmp');
	}
	else if ($tri == 'Target') {
		uasort($corpora,'trgcmp');
	}
	else if ($tri == 'Couple') {
		uasort($corpora,'cplcmp');
	}
	else {
		ksort($corpora,SORT_LOCALE_STRING);
	}
	$paires = 0;
	foreach ($corpora as $nom => $corpus) {
		if (($Params['Source'] == '' || $corpus['Source'] == $Params['Source']) &&  ($Params['Target'] == '' || $corpus['Target'] == $Params['Target'])) {
		$couple = $corpus['Source'] . '-' . $corpus['Target'];
		if (strcmp($corpus['Source'],$corpus['Target'])>0) {
			$couple = $corpus['Target'] . '-' . $corpus['Source'];
		}
		echo '<tr class="'; echo $i%2==0?'even':'odd'; echo '">';
		echo '<td>',$i,'</td>';
		echo '<td><acronym title="',$corpus['NameC'],'">',$nom,'</acronym> </td>';
		echo '<td>',$corpus['Category'],'</td>';
		echo '<td >',$corpus['Type'],'</td>';
		echo '<td>',$corpus['Administrators'],'</td>';
		echo '<td>',$couple,'</td>';
		echo '<td><abbr title="',$LANGUES[$corpus['Source']],'">',$corpus['Source'],'</abbr></td>';
		echo '<td><abbr title="',$LANGUES[$corpus['Target']],'">',$corpus['Target'],'</abbr></td>';
		echo '<td style="text-align:right">',$corpus['SourceWords'],'-',$corpus['TargetWords'],'</td>';
		echo '<td style="text-align:right">',$corpus['Pairs'],'</td>';
		$paires += intval($corpus['Pairs']);
		echo '<td><input type="checkbox" name="Corpus[]" value="',$corpus['Name'],'" ';
		if (in_array($nom,$Collection)) {
			echo 'checked="checked" ';
		} 
		echo '/>';
		echo '<td>';
		if (in_array($user, $corpus['AdministratorArray'])) {
			echo '<a title="Éditer" href="modifCorpus.php?Modifier=on&Dirname=',$corpus['Dirname'],'&Name=',$corpus['Name'],'"><img style="border:none;" src="',RACINE_WEB,'images/assets/b_edit.png" alt="Éditer"/></a>';
		}
		else {
			echo '<a title="Consulter" href="modifCorpus.php?Consulter=on&Dirname=',$corpus['Dirname'],'&Name=',$corpus['Name'],'"><img  style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_update.png" alt="Consulter"/></a>';
		}
		echo '</td>';
		echo '<td><a title="Ouvrir" href="',CORPUS_DAV,'/',$corpus['Dirname'],'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_send.png" alt="Ouvrir"/></a></td>';
		echo '</tr>';
		$i++;
		}
		}
	echo '<tr><th>&nbsp;</th><th>Total</th><td colspan="8" style="text-align:right">',$paires,'</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
			?>
		</tbody>
	</table>
	<p style="text-align:center;">
	<input type="submit" name="EnregistrerCollection" value="<?php echo gettext('Enregistrer');?>" />
	</p>
	</form>
 </section>
 <?php
 }
 ?>
<section>
	<table>
		<thead>
			<tr><th>&nbsp;</th>
			<th><a href="?tri=Nom"><?php echo gettext('Nom'); ?></a></th>
			<th><?php echo gettext('Source'); ?></th>
			<th><?php echo gettext('Cible'); ?></th>
			<th><?php echo gettext('Corpus'); ?></th>
			<th><?php echo gettext('Mots'); ?></th>
			<th><?php echo gettext('Liens'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$i=1;
	foreach ($collections as $nom => $collection) {
		echo '<tr class="'; echo $i%2==0?'even':'odd'; echo '">';
		echo '<td>', $i,'</td><td>',$collection['Name'],'</td>';
		echo '<td><abbr title="',$LANGUES[$collection['Source']],'">',$collection['Source'], '</abbr> - ', $collection['sr'],'</td>';
		echo '<td><abbr title="',$LANGUES[$collection['Target']],'">',$collection['Target'], '</abbr> - ', $collection['tr'],'</td>';
		echo '<td>';
		$motssource = 0;
		$motscible = 0;
		$liens = 0;
		foreach ($collection['Corpora'] as $corpus) {
			echo '<a href="modifCorpus.php?Consulter=on&Name=',$corpus,'&Dirname=',$corpora[$corpus]['Dirname'],'">',$corpus,'</a>';
			$motssource += intval($corpora[$corpus]['SourceWords']);
			$motscible += intval($corpora[$corpus]['TargetWords']);
			$liens += intval($corpora[$corpus]['Pairs']);
			if ($corpus !== end($collection['Corpora'])) {
				echo ', ';
			}
		}
		echo '</td><td>',$motssource,'-',$motscible,'</td><td>',$liens,'</td><td>';
		if (in_array($user, $collection['Administrators'])) {
			echo '<a title="Éditer" href="gestionCollections.php?ModifierCollection=on&Name=',$collection['Name'],'"><img style="border:none;" src="',RACINE_WEB,'images/assets/b_edit.png" alt="Éditer"/></a>';
		}
		else {
			echo '<a title="Consulter" href="gestionCollections.php?Consulter=on&Name=',$collection['Name'],'"><img  style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_update.png" alt="Consulter"/></a>';
		}
		echo '</td>';
		if (in_array($user, $collection['Administrators'])) {
			echo '<td><a title="Générer" href="gestionCollections.php?GenererCollection=on&Name=',$collection['Name'],'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_generate.png" alt="Générer"/></a></td>';
		}
		echo '<td><a title="Ouvrir" href="',CORPUS_DAV,'/',DIRCOLLECTIONS,'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_send.png" alt="Ouvrir"/></a></td>';
		echo '<td><a title="Supprimer" href="gestionCollections.php?SupprimerCollection=on&Name=',$collection['Name'],'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_delete.png" alt="Supprimer"/></a></td>';

		echo '</tr>';
		$i++;
	}
?>
		</tbody>
	</table>
	<p>Attention, l'indexation des corpus CWB ne marche pas si les fichiers de lien utilisent des ' à la place des " pour délimiter les attributs !!!</p>
		<p style="text-align:center;"><a href="gestionCollections.php?CreerCollection=on"><img src="<?php echo RACINE_WEB;?>images/assets/b_new.png"/>
	<?php echo gettext('Ajout d\'une collection');?></a></p>
</section>
<?php
   	if ($ongoing_analysis) {
   		$pid = '';
   		if (!empty($generatePid)) {
   			$pid = '&pid=' . $generatePid;
   		}
   		echo '<iframe id="analyze_frame" style="height:400px;width:100%" src="',RACINE_WEB,'include/analyze_frame.php?filename=',$logFile,$pid,'" name="analyze_frame" frameborder="0" border="0" src=""> </iframe>';
    }
?>
<?php include(RACINE_SITE.'include/footer.php');?>

