<?php
	require_once('../init.php');
	require_once(RACINE_SITE . 'include/Process.php');
	$user = !empty($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'';
	$ongoing_analysis = 0;
	$generatePid = '';
	

	$Collection = array();
	if (!empty($_REQUEST['Collection'])) {
		$Collection = $_REQUEST['Corpus'];
	}

# initialise la liste de tous les corpus et des corpus de la collection à construire
	$allcorpora = array();
	$collcorpora = array();
    if ($dh = opendir(CORPUS_SITE)) {
        while (($file = readdir($dh)) !== false) {
			if (filetype(CORPUS_SITE . '/'.$file)=='dir'
				&& substr($file,0,1)!== '.'
				&& strpos($file,'_')>0) {
				$souligne = strpos($file,'_');
				$nom = substr($file,0,$souligne);
				$corpus = CORPUS_SITE . '/'. $file . '/' . $nom . '-metadata.xml';
				$infos = parseCorpus($corpus);
				$infos['Dirname']= $file;
				$allcorpora[$infos['Name']] = $infos;
				if (in_array($nom, $Collection) && file_exists($corpus)) {
					$collcorpora[$infos['Name']] = $infos;
				}
			}
        }
        closedir($dh);
    }
	ksort($collcorpora,SORT_LOCALE_STRING);
	ksort($allcorpora,SORT_LOCALE_STRING);

# crée puis enregistre la collection 
	if (!empty($_REQUEST['SaveCollection']) && 
		!empty($_REQUEST['CollectionName']) && 
		!empty($_REQUEST['CollectionSrc']) &&
		!empty($_REQUEST['CollectionTrg'])) {
		$collectiondir = CORPUS_SITE.'/'.DIRCOLLECTIONS;
		`mkdir -p $collectiondir`;
		$collecMetadata = creerCollectionMetadata($_REQUEST['CollectionName'],$_REQUEST['CollectionSrc'],$_REQUEST['CollectionTrg'],$Collection, $user);
		$myFile = $collectiondir ."/".$_REQUEST['CollectionName'].'-metadata.xml';
		$fh = fopen($myFile, 'w') or die("impossible d'ouvrir le fichier ".$myFile);
		fwrite($fh, $collecMetadata);
		fclose($fh);
		// echo 'Collection créée !';
	}
	
# initialise la liste des collections (après avoir créé la nouvelle collection)
	$collections = array();
    if ($dh = opendir(CORPUS_SITE . DIRCOLLECTIONS)) {
        while (($file = readdir($dh)) !== false) {
        	$metafile = CORPUS_SITE . DIRCOLLECTIONS . '/'.$file;
			if (is_file($metafile) && preg_match('/-metadata.xml$/',$file)) {
				$infos = parseCollection($metafile);
				$infos['Dirname']= $metafile;
				$collections[$infos['Name']] = $infos;
			}
        }
        closedir($dh);
    }
	ksort($collections,SORT_LOCALE_STRING);

# génère le registre CWB de la collection sélectionnée
	if (!empty($_REQUEST['GenerateCollection']) && 
		!empty($_REQUEST['CollectionName'])) {
		$colname = $_REQUEST['CollectionName'];
		$collection = $collections[$colname];
		// TODO supprimer le logFile qd c'est terminé => avec un cron
		$logFile = tempnam(RACINE_SITE . 'data', 'generation');
		$ongoing_analysis = 1;
		$linksfiles = '';
		
		foreach ($collection['Corpora'] as $corpusname) {
			$corpus = $allcorpora[$corpusname];
			$dirpath = CORPUS_SITE . $corpus['Dirname'] . '/' . DIRXML . '/' . DIRLINKS;
			$dirs = array();
			array_push($dirs, $dirpath);
			// TODO recherche récursive des fichiers de link
			if ($dh = opendir($dirpath)) {
				while (($file = readdir($dh)) !== false) {
					$filepath = CORPUS_SITE . $corpus['Dirname'] . '/' . DIRXML . '/' . DIRLINKS . '/'.$file;
					if (is_file($filepath) && preg_match('/.xml$/',$file)) {
						$linksfiles .= ' ' . $filepath;
					}
				}
				closedir($dh);
			}
		}
		$command = RACINE_SITE . 'pl/cree_corpus_cwb.pl ' . $colname . ' ' . $collection['sr'] . ' ' . $collection['tr']. ' ' . $linksfiles;
		$process = new Process($command);
		$process->start();
		$generatePid = $process->getPid();
	}

	include(RACINE_SITE.'include/header.php');
?>
<header id="enTete">
	<?php print_lang_menu();?>
    <h1><?php echo gettext('iPoCorp : entrepôt de corpus');?></h1>
	<h2><?php echo gettext('Gestion des collections');?></h2>
	<hr />
</header>
<section id="partieCentrale">
<?php
	$tri = !empty($_REQUEST['tri'])?$_REQUEST['tri']:'Nom';
	$user = !empty($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'';
?>
<?php if (!empty($_REQUEST['CreateCollection'])) { ?>
	<h3  style="text-align:center;"><?php echo gettext('Création d\'une collection');?></h3>
   <form action="?" style="text-align:center;">
	<div style="margin:auto;display:inline-block;text-align:left;">
	<label><?php echo gettext('Nom de la collection'), gettext(' : ');?><input type="text" name="CollectionName" placeholder="collec" required="required"  pattern="[a-z]+"/> <?php echo gettext('Ne doit contenir que des minuscules non accentuées.');?> [a-z]+</label>
	<p><label><?php echo gettext('Langue source'),gettext(' : ');?>
		<select name="CollectionSrc">
			<option value=""><?php echo gettext('Choisir...');?></option>
		<?php afficheLanguesOptions($corpora[key($corpora)]['Source']); ?>
		</select></label></p>
	<p><label><?php echo gettext('Langue cible'),gettext(' : ');?>
		<select name="CollectionTrg">
			<option value=""><?php echo gettext('Choisir...');?></option>
		<?php afficheLanguesOptions($corpora[key($corpora)]['Target']); ?>
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
			<th><?php echo gettext('Entrées'); ?></th>
			<th><?php echo gettext('Collection'); ?></th>
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

	foreach ($collcorpora as $nom => $corpus) {
		$couple = $corpus['Source'] . '-' . $corpus['Target'];
		if (strcmp($corpus['Source'],$corpus['Target'])>0) {
			$couple = $corpus['Target'] . '-' . $corpus['Source'];
		}
		echo '<tr class="'; echo $i%2==0?'even':'odd'; echo '">';
		echo '<td>',$i,'</td>';
		echo '<td><acronym title="',$corpus['NameC'],'">',$nom,'</acronym> </td>';
		echo '<td>',$corpus['Category'],'</td>';
		echo '<td >',$corpus['Type'],'</td>';
		echo '<td>',implode(',',$corpus['Administrators']),'</td>';
		echo '<td>',$couple,'</td>';
		echo '<td><abbr title="',$LANGUES[$corpus['Source']],'">',$corpus['Source'],'</abbr></td>';
		echo '<td><abbr title="',$LANGUES[$corpus['Target']],'">',$corpus['Target'],'</abbr></td>';
		$HwNumber= 0;
		echo '<td style="text-align:right">',$HwNumber,'</td>';
		$entrees += intval($HwNumber);
		echo '<td><input type="checkbox" name="Corpus[]" value="',$corpus['Name'],'" checked="checked" />';
		echo '<td>';
		if (in_array($user, $corpus['Administrators'])) {
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
	echo '<tr><th>&nbsp;</th><th>Total</th><td colspan="7" style="text-align:right">',$entrees,'</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
			?>
		</tbody>
	</table>
	<p style="text-align:center;">
	<input type="submit" name="SaveCollection" value="<?php echo gettext('Enregistrer');?>" />
	</p>
	</form>
 </section>
 <?php
 }
 ?>
<?php
			
	function srccmp($a, $b) {
		return strcmp($a['Source'],$b['Source']);
	}

	function trgcmp($a, $b) {
		return strcmp($a['Target'],$b['Target']);
	}

	function cplcmp($a, $b) {
		$couplea = $a['Source'] . '-' . $a['Target'];
		if (strcmp($a['Source'],$a['Target'])>0) {
			$couplea = $a['Target'] . '-' . $a['Source'];
		}
		$coupleb = $b['Source'] . '-' . $b['Target'];
		if (strcmp($b['Source'],$b['Target'])>0) {
			$coupleb = $b['Target'] . '-' . $b['Source'];
		}
		return strcmp($couplea,$coupleb);
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
		foreach ($collection['Corpora'] as $corpus) {
			echo '<a href="modifCorpus.php?Consulter=on&Name=',$corpus,'&Dirname=',$allcorpora[$corpus]['Dirname'],'">',$corpus,'</a>';
			if ($corpus !== end($collection['Corpora'])) {
				echo ', ';
			}
		}
		echo '</td><td>';
		if (in_array($user, $collection['Administrators'])) {
			echo '<a title="Éditer" href="gestionCollections.php?Modifier=on&Name=',$collection['Name'],'"><img style="border:none;" src="',RACINE_WEB,'images/assets/b_edit.png" alt="Éditer"/></a>';
		}
		else {
			echo '<a title="Consulter" href="modifCorpus.php?Consulter=on&Name=',$collection['Name'],'"><img  style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_update.png" alt="Consulter"/></a>';
		}
		echo '</td>';
		if (in_array($user, $collection['Administrators'])) {
			echo '<td><a title="Générer" href="?GenerateCollection=on&CollectionName=',$collection['Name'],'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_generate.png" alt="Générer"/></a></td>';
		}
		echo '<td><a title="Ouvrir" href="',CORPUS_DAV,'/',DIRCOLLECTIONS,'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_send.png" alt="Ouvrir"/></a></td>';

		echo '</tr>';
	}
?>
		</tbody>
	</table>
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

