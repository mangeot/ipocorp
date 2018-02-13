<?php
	require_once('../init.php');
	$tri = !empty($_REQUEST['tri'])?$_REQUEST['tri']:'Nom';
	$user = !empty($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:DEFAULT_TEST_USER;
	$corpora = getCorpora();
	$collections = getCollections();

	include(RACINE_SITE.'include/header.php');	
?>
<header id="enTete">
	<?php print_lang_menu();?>
    <h1><?php echo gettext('iPoCorp : entrepôt de corpus');?></h1>
	<h2><?php echo gettext('Accueil');?></h2>
	<hr />
</header>
<section id="partieCentrale">
   <h3 style="text-align:center;"><?php echo gettext('Liste des corpus'); ?></h3>
   <form action="gestionCollections.php" method="post">
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
		echo '<td>';
		if (in_array($user, $corpus['AdministratorArray'])) {
			echo '<a title="Éditer" href="modifCorpus.php?Modifier=on&Dirname=',$corpus['Dirname'],'&Name=',$corpus['Name'],'"><img style="border:none;" src="',RACINE_WEB,'images/assets/b_edit.png" alt="Éditer"/></a>';
		}
		else {
			echo '<a title="Consulter" href="modifCorpus.php?Consulter=on&Dirname=',$corpus['Dirname'],'&Name=',$corpus['Name'],'"><img  style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_update.png" alt="Consulter"/></a>';
		}
		echo '</td>';
		//echo '<td><a title="Textes" href="',CORPUS_DAV,'/',$corpus['Dirname'],'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_text.png" alt="Ouvrir"/></a></td>';
		echo '<td><a title="Ouvrir" href="',CORPUS_DAV,'/',$corpus['Dirname'],'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_send.png" alt="Ouvrir"/></a></td>';
		echo '</tr>';
		$i++;
		}
	echo '<tr><th>&nbsp;</th><th>Total</th><td colspan="8" style="text-align:right">',$paires,'</td></tr>';
			?>
		</tbody>
	</table>
	</form>
	
	<p style="text-align:center;"><a href="modifCorpus.php"><img src="<?php echo RACINE_WEB;?>images/assets/b_new.png"/>
	<?php echo gettext('Ajout d\'un corpus');?></a></p>
 	<p><?php echo gettext('Vous pouvez accéder directement aux corpus en montant le site sur votre bureau comme un répertoire distant avec le protocole <a href="http://fr.wikipedia.org/wiki/WebDAV">WebDav</a>.'); 
 	echo ' ',gettext('Pour monter un corpus spécifique, copiez l\'adresse URL de la flèche droite verte.');
 	echo ' ',gettext('Pour monter le répertoire contenant tous les corpus, utilisez l\'adresse URL suivante'); echo gettext(' : ');?><a href="<?php echo CORPUS_DAV;?>">
 	<?php echo CORPUS_DAV;?></a>.</p>
 
 </section>
 <section>
   <h3 style="text-align:center;"><?php echo gettext('Liste des collections'); ?></h3>
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
	<p style="text-align:center;"><a href="gestionCollections.php?CreerCollection=on"><img src="<?php echo RACINE_WEB;?>images/assets/b_new.png"/>
	<?php echo gettext('Ajout d\'une collection');?></a></p>
</section>

<?php include(RACINE_SITE.'include/footer.php');?>

