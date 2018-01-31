<?php
	require_once('../init.php');
	include(RACINE_SITE.'include/header.php');
?>
<header id="enTete">
	<?php print_lang_menu();?>
    <h1><?php echo gettext('iPoCorp : entrepôt de corpus');?></h1>
	<h2><?php echo gettext('Accueil');?></h2>
	<hr />
</header>
<section id="partieCentrale">
<?php
	$tri = !empty($_REQUEST['tri'])?$_REQUEST['tri']:'Nom';
	$user = !empty($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'';
	$corpora = array();
	$langs = array();
// Open a known directory, and proceed to read its contents*ça ouvre le répertoire contenant les dictionaires et lit leur contenu.
if (is_dir(CORPUS_SITE)) {
    if ($dh = opendir(CORPUS_SITE)) {
        while (($file = readdir($dh)) !== false) {
			if (filetype(CORPUS_SITE . '/'.$file)=='dir' 
				&& substr($file,0,1)!== '.'
				&& strpos($file,'_')>0) {
				$souligne = strpos($file,'_');
				$nom = substr($file,0,$souligne);
				$corpus = CORPUS_SITE . '/'. $file . '/' . $nom . '-metadata.xml';
				if (file_exists($corpus)) {
					$infos = parseCorpus($corpus);
					$infos['Dirname']= $file;
					$corpora[$infos['Name']] = $infos;
					$src = $infos['Source'];
					$langs[$src] = $src;
					$trg = $infos['Target'];
					$langs[$trg] = $trg;
				}
				else {
					echo '<p class="erreur">',gettext('Le corpus suivant n\'a pas de métadonnées : '),$nom,'</p>';

				}
			}
        }
        closedir($dh);
    }
	ksort($corpora,SORT_LOCALE_STRING);
	ksort($langs,SORT_LOCALE_STRING);
}
?>
	<table>
		<thead>
			<?php if ($tri == 'Source') {
				 	echo '<tr><th>&nbsp;</th><th>Source</th><th><a href="?tri=Nom">',gettext('Nom'),
				 	'</a></th><th>',gettext('Catégorie'),'</th><th>Type</th><th>',gettext('Administrateur'),
				 	'</th><th>',gettext('Cible'),' <a style="font-size:smaller;" href="?tri=Langues">',gettext('toutes'),
				 	' </a></th><th>',gettext('Taille'),'</th><th colspan="3"></th></tr>';
				}
				else if ($tri == 'Langues') {
				 	echo '<tr><th>&nbsp;</th><th>',gettext('Langue'),'</th><th><a href="?tri=Nom">',gettext('Nom'),'</a></th><th>',gettext('Catégorie'),
				 	'</th><th>Type</th><th>',gettext('Administrateur'),'</th><th><a href="?tri=Langues">',gettext('Langues'),
				 	'</a> <a style="font-size:smaller;" href="?tri=Source">',gettext('Source'),'</a></th><th>',gettext('Entrées'),'</th></tr>';
				}
				else echo '<tr><th>&nbsp;</th><th>',gettext('Nom'),'</th><th>',gettext('Catégorie'),'</th><th>',gettext('Type'),
				'</th><th>',gettext('Administrateur'),'</th><th><a href="?tri=Source">',gettext('Source'),
				'</a></th><th><a href="?tri=Cible">',gettext('Cible'),'</a></th><th>',gettext('Entrées'),'</th></tr>';
				?>
		</thead>
		<tbody>
			<?php
	$entrees = 0;
	$style='odd';
	$i=1;
	if ($tri == 'Langues') {
		$srcs = $langs;
	}
	if ($tri == 'Source' || $tri == 'Langues') {
		foreach ($srcs as $source => $dicts) {
		$j=0;
		ksort($dicts,SORT_LOCALE_STRING);
		foreach ($dicts as $nom => $dict) {
			echo '<tr class="'; echo $i%2==0?'even':'odd'; echo '">';
			if ($j==0) {
				echo '<td rowspan="',count($dicts),'">',$i,'</td>';
				echo '<td rowspan="',count($dicts),'"><abbr title="',$LANGUES[$source],'">',$source,'</abbr></td>';
			}
			echo '<td><acronym title="',$dict['NameC'],'">',$nom,'</acronym> </td>';
				echo '<td>',$dict['Category'],'</td>';
				echo '<td>',$dict['Type'],'</td>';
				echo '<td>',implode(',',$dict['Administrators']),'</td>';
				
				echo '<td>',$dict['Volumes'][key($dict['Volumes'])]['Format'],'</td>';
			if ($tri == 'Source') {
				$trgs = $dict['Volumes'][$source]['Targets'];
			}
			else {
				$trgs = toutesLangues($dict['Volumes']);
			}
			echo '<td>';
			foreach ($trgs as $trg) {
				echo '<abbr title="',$LANGUES[$trg],'">',$trg,'</abbr>, ';
			}
			echo '</td>';
			$articles = $dict['Volumes'][key($dict['Volumes'])]['HwNumber'];
			echo '<td style="text-align:right">',$articles,'</td>';
				$entrees += intval($articles);
				echo '<td>';
				if (in_array($user, $dict['Administrators'])) {
					echo '<a title="Éditer" href="modifCorpus.php?Modifier=on&Dirname=',$dict['Dirname'],'&Name=',$dict['Name'],'"><img style="border:none;" src="',RACINE_WEB,'images/assets/b_edit.png" alt="Éditer"/></a>';
				}
				else {
					echo '<a title="Consulter" href="modifCorpus.php?Consulter=on&Dirname=',$dict['Dirname'],'&Name=',$dict['Name'],'"><img  style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_update.png" alt="Consulter"/></a>';
				}
				echo '</td>';
				echo '<td><a title="Ouvrir" href="',CORPUS_DAV,'/',$dict['Dirname'],'"><img style="border:none;" width="20" src="',RACINE_WEB,'images/assets/b_send.png" alt="Ouvrir"/></a></td>';
			echo '</tr>';
			$j++;
		}
		$i++;
	}
	}
	else {
	foreach ($corpora as $nom => $corpus) {
		echo '<tr class="'; echo $i%2==0?'even':'odd'; echo '">';
			echo '<td>',$i,'</td>';
			echo '<td><acronym title="',$corpus['NameC'],'">',$nom,'</acronym> </td>';
			echo '<td>',$corpus['Category'],'</td>';
			echo '<td >',$corpus['Type'],'</td>';
			echo '<td>',implode(',',$corpus['Administrators']),'</td>';
			echo '<td><abbr title="',$LANGUES[$src],'">',$src,'</abbr></td>';
			echo '<td><abbr title="',$LANGUES[$trg],'">',$trg,'</abbr></td>';
			$HwNumber= 0;
			echo '<td style="text-align:right">',$HwNumber,'</td>';
			$entrees += intval($HwNumber);
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
	}
	echo '<tr><th>&nbsp;</th><th>Total</th><td colspan="7" style="text-align:right">',$entrees,'</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
			?>
		</tbody>
	</table>
	
	<p style="text-align:center;"><a href="modifCorpus.php"><img src="<?php echo RACINE_WEB;?>images/assets/b_new.png"/>
	<?php echo gettext('Ajout d\'un corpus');?></a></p>
 	<p><?php echo gettext('Vous pouvez accéder directement aux corpus en montant le site sur votre bureau comme un répertoire distant avec le protocole <a href="http://fr.wikipedia.org/wiki/WebDAV">WebDav</a>.'); 
 	echo ' ',gettext('Pour monter un corpus spécifique, copiez l\'adresse URL de la flèche droite verte.');
 	echo ' ',gettext('Pour monter le répertoire contenant tous les corpus, utilisez l\'adresse URL suivante'); echo gettext(' : ');?><a href="<?php echo CORPUS_DAV;?>">
 	<?php echo CORPUS_DAV;?></a>.</p>
 
 </section>
<?php
	function parseCorpus($corpus) {
		$infos = array();
		$doc = new DOMDocument();
		$doc->load($corpus);
		$corpora = $doc->getElementsByTagName("corpus-metadata");
		$corp = $corpora->item(0);
  		$infos['NameC'] = $corp->getAttribute('fullname');
  		$infos['Name'] = $corp->getAttribute('name');
  		$infos['Owner'] = $corp->getAttribute('owner');
  		$infos['Category'] = $corp->getAttribute('category');
  		$infos['Type'] = $corp->getAttribute('type');
  		$infos['CreationDate'] = $corp->getAttribute('creation-date');
  		$infos['InstallationDate'] = $corp->getAttribute('installation-date');
  		$infos['Category'] = $corp->getAttribute('category');
  		$infos['Type'] = $corp->getAttribute('type');
  		$infos['Contents'] = $corp->getElementsByTagName('contents')->item(0)->nodeValue;
  		$infos['Domain'] = $corp->getElementsByTagName('domain')->item(0)->nodeValue;
  		if (empty($infos['Domain'])) {echo 'domaine vide : ',$dico;}
  		$infos['Source'] = $corp->getElementsByTagName('source')->item(0)->nodeValue;
  		$infos['Authors'] = $corp->getElementsByTagName('authors')->item(0)->nodeValue;
  		//if (empty($infos['Authors'])) {echo 'auteurs vides : ',$dico;}
  		$infos['Legal'] = $corp->getElementsByTagName('legal')->item(0)->nodeValue;
  		$tmp = $corp->getElementsByTagName('comments');
  		if ($tmp->length>0) {$infos['Comments'] = $tmp->item(0)->nodeValue;}
  		$tmp = $corp->getElementsByTagName('Reference');
  		if ($tmp->length>0) {$infos['Reference'] = $tmp->item(0)->nodeValue;}
  		$adminNodes = $corp->getElementsByTagName('user-ref');
  		$admins = array();
  		foreach ($adminNodes as $admin) {
  			array_push($admins,$admin->getAttribute('name'));
  		}
		$infos['Administrators'] = $admins; 
  		$infos['Source'] = $corp->getElementsByTagName('source-language')->item(0)->getAttribute('d:lang');
  		$infos['Target'] = $corp->getElementsByTagName('target-language')->item(0)->getAttribute('d:lang');
		return($infos);
	}
		
	function cibles($volumes) {
		$cibles = array();
		foreach ($volumes as $volume) {
			$cibles = array_merge($volume['Targets'],$cibles);
		}
		$cibles = array_unique($cibles);
		sort($cibles,SORT_LOCALE_STRING);
		return $cibles;
	}
	
	function toutesLangues($volumes) {
		$cibles = array();
		foreach ($volumes as $volume) {
			$cibles = array_merge($volume['Targets'],$cibles);
			array_push($cibles,$volume['Source']);
		}
		$cibles = array_unique($cibles);
		sort($cibles,SORT_LOCALE_STRING);
		return $cibles;
	}
	
?>
<?php include(RACINE_SITE.'include/footer.php');?>

