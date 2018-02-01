<?php
	require_once('../init.php');
	$metadataFile = '';
	if (!empty($_REQUEST['Dirname']) && !empty($_REQUEST['Name'])) {
		if (!empty($_REQUEST['ManageTexts']) && !empty($_REQUEST['Authors']) && !empty($_REQUEST['Administrators'])) {
//		echo '[',$_REQUEST['Dirname'],'][',$_REQUEST['Name'],']';
//		header('Location:gestionTextes.php?Dirname='.$_REQUEST['Dirname'].'&Name='.$_REQUEST['Name']);
			header('Location:gestionTextes.php?Dirname='.$_REQUEST['Dirname'].'&Name='.$_REQUEST['Name'].
			'&Authors='.$_REQUEST['Authors'].'&Administrators='.$_REQUEST['Administrators'] .
			'&Language1='.$_REQUEST['Language1'].'&Language2='.$_REQUEST['Language2']);
			exit;
		}
		$metadataFile = CORPUS_SITE.'/'.$_REQUEST['Dirname']."/".$_REQUEST['Name'].'-metadata.xml';
	}
	$Params = array();
	if (empty($_REQUEST['Enregistrer']) && file_exists($metadataFile)) {
		$doc = new DOMDocument();
  		$doc->load($metadataFile);
  		$dicts = $doc->getElementsByTagName("corpus-metadata");
  		$dict = $dicts->item(0);
  		$Params['Dirname'] = $_REQUEST['Dirname'];
  		$Params['NameC'] = $dict->getAttribute('fullname');
  		$Params['Name'] = $dict->getAttribute('name');
  		$Params['Owner'] = $dict->getAttribute('owner');
  		$Params['Category'] = $dict->getAttribute('category');
  		$Params['Type'] = $dict->getAttribute('type');
  		$Params['Language1'] = $dict->getElementsByTagName('source-language')->item(0)->getAttribute('d:lang');
  		$Params['Language2'] = $dict->getElementsByTagName('target-language')->item(0)->getAttribute('d:lang');
  		$Params['CreationDate'] = $dict->getAttribute('creation-date');
  		$Params['InstallationDate'] = $dict->getAttribute('installation-date');
  		$Params['Contents'] = $dict->getElementsByTagName('contents')->item(0)->nodeValue;
  		$Params['Domain'] = $dict->getElementsByTagName('domain')->item(0)->nodeValue;
  		$Params['Source'] = $dict->getElementsByTagName('source')->item(0)->nodeValue;
  		$Params['Authors'] = $dict->getElementsByTagName('authors')->item(0)->nodeValue;
  		$Params['Legal'] = $dict->getElementsByTagName('legal')->item(0)->nodeValue;
  		$Params['Access'] = ($dict->getElementsByTagName('access')->item(0))?$dict->getElementsByTagName('access')->item(0)->nodeValue:'restricted';
  		$Params['Reference'] = $dict->getElementsByTagName('reference')->item(0)->nodeValue;
  		$Params['Comments'] = $dict->getElementsByTagName('comments')->item(0)->nodeValue;
		$administrators = $dict->getElementsByTagName('user-ref');
		$adminString = '';
		foreach ($administrators as $user) {
			$adminString .= $user->getAttribute('name') . ',';
		}
		$Params['Administrators'] = trim($adminString,',');
  	}
	else {
		$Params = $_REQUEST;
	}
	include(RACINE_SITE.'include/header.php');
?>
<header id="enTete">
	<?php print_lang_menu();?>
	<h1><?php echo gettext('iPoCorp : entrepôt de corpus');?></h1>
	<h2><?php echo gettext('Ajout/Modification du corpus');?> <?php affichep('Name');?></h3>
	<hr />
</header>
<div id="partieCentrale">
<?php
	$modif = false;
	$user=!empty($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:DEFAULT_TEST_USER;
	if (!empty($Params['Administrators'])) {
		$admins = preg_split("/[\s,;]+/", $Params['Administrators']);
		$modif = in_array($user, $admins);
		if ($modif && !empty($_REQUEST['Enregistrer']) && !empty($Params['Name'])) {
			$Params['Dirname'] = creerCorpus($Params);
			$metadataFile = CORPUS_SITE.'/'.$Params['Dirname']."/".$Params['Name'].'-metadata.xml';
		}
	}
	if (file_exists($metadataFile)) {?>
		<form action=""><?php echo gettext('Le fichier de métadonnées du corpus a été enregistré.'); ?>
		<input type="hidden" name="Dirname" value="<?php echo $Params['Dirname']; ?>" />
		<input type="hidden" name="Name" value="<?php echo $Params['Name'];?>" />
		<input type="hidden" name="Authors" value="<?php echo $Params['Authors']; ?>" />
		<input type="hidden" name="Administrators" value="<?php echo $Params['Administrators']; ?>" />
		<input type="hidden" name="Language1" value="<?php echo $Params['Language1']; ?>" />
		<?php if (!empty($Params['Language2'])) {
			echo '<input type="hidden" name="Language2" value="',$Params['Language2'],'" />';
		}
		 echo gettext('Vous pouvez maintenant gérer les <input type="submit" name="ManageTexts" value="textes"/>.')?>
		</form>
	<?php
	}
?>
<form action="?" method="post">
<fieldset name="Gérer un corpus">
<legend><?php echo gettext('Gestion d\'un corpus');?></legend>
<div>
	<p>*<?php echo gettext('Nom complet'); echo gettext(' : ');?><input type="text" required="required" size="50" id="NameC" name="NameC" value="<?php affichep('NameC')?>" /></p>
	<p>*<?php echo gettext('Nom abrégé'); echo gettext(' : ');?><input type="text" required="required"  pattern="[A-Z][a-zA-Z0-9\-]+"  id="Name" name="Name" onfocus="copyifempty(this,'NameC');" value="<?php affichep('Name')?>"/> <?php echo gettext('Le nom doit commencer par une majuscule. Caractères ASCII alphanumériques et tiret uniquement !');?>  [A-Z][a-zA-Z0-9\-]+</p>
	<p><?php echo gettext('Propriétaire'); echo gettext(' : ');?><input type="text" id="Owner"  onfocus="copyifempty(this,'Name');" name="Owner"  value="<?php affichep('Owner')?>"/></p>
	<p>*<?php echo gettext('Catégorie'); echo gettext(' : ');?><select id="Category"  required="required" name="Category" onchange="this.form.submit()">
		<option value=""><?php echo gettext('choisir...');?></option>
		<?php afficheo('Category',"monolingual")?><?php echo gettext('monolingue');?></option>
		<?php afficheo('Category',"bilingual")?><?php echo gettext('bilingue');?></option>
		<!--?php afficheo('Category',"multilingual")?><?php echo gettext('multilingue');?></option-->
	</select>
	</p>
	<?php if (!empty($Params['Category']) && $Params['Category'] !== 'monolingual') {
		echo '
	<p>*Type : <select id="Type" name="Type" onchange="this.form.submit()">';
		afficheo('Type','aligned'); echo gettext('aligné'),'</option>';
		afficheo('Type','comparable'); echo gettext('comparable'),'</option>';
		//afficheo('Type','mixed'); echo gettext('mixé'),'</option>';
		echo '
			</select>
			</p>';
	}
	?>
	<p><?php echo gettext('Langue source'),gettext(' : ');?>
		<select name="Language1" onchange="this.form.submit()">
			<option value=""><?php echo gettext('Choisir...');?></option>
		<?php afficheLanguesOptions($Params['Language1']); ?>
		</select></p>
	<?php if (!empty($Params['Category']) && $Params['Category'] !== 'monolingual') {
		echo gettext('Langue cible'),gettext(' : ');?>
		<select name="Language2" onchange="this.form.submit()">
			<option value=""><?php echo gettext('Choisir...');?></option>
			<?php afficheLanguesOptions($Params['Language2']); ?>
		</select></p>
	<?php 
	}
	?>

	<p><?php echo gettext('Contenu');?> <input type="text" id="Contents" name="Contents" value="<?php affichep('Contents','vocabulaire général');?>" /></p>	
	<p><?php echo gettext('Domaine');?> <input type="text" id="Domain" name="Domain" value="<?php affichep('Domain','général');?>"/></p>	
	<p><?php echo gettext('Source');?> <input type="text" id="Source" name="Source" value="<?php affichep('Source','GETALP');?>"/></p>	
	<p><?php echo gettext('Auteurs');?> <input type="text" id="Authors" name="Authors" onfocus="copyifempty(this,'Owner');"  value="<?php affichep('Authors');?>"/></p>	
	<p><?php echo gettext('Licence');?> <input type="text"  size="50" id="Legal" name="Legal" value="<?php affichep('Legal','Creative Commons, CC by SA');?>"/></p>
	<p>*<?php echo gettext('Accès'); echo gettext(' : ');?><select id="Access"  required="required" name="Access">
		<option value=""><?php echo gettext('choisir...');?></option>
		<?php afficheo('Access',"public")?><?php echo gettext('public = web');?></option>
		<?php afficheo('Access',"restricted")?><?php echo gettext('réservé = labo');?></option>
		<?php afficheo('Access',"private")?><?php echo gettext('privé = admin');?></option>
	</select>
	</p>

	<p><?php echo gettext('Référence');?> <textarea id="Reference" name="Reference"><?php affichep('Reference');?></textarea></p>	
	<p><?php echo gettext('Commentaires');?> <textarea id="Comments" name="Comments"><?php affichep('Comments');?></textarea></p>	
	<p><?php echo gettext('Administrateurs');?> <input type="text" id="Administrators" name="Administrators" value="<?php affichep('Administrators',$user);?>"/></p>	
	<a href="#" onclick="document.getElementById('moreInfo').style.display='block'"><?php echo gettext('Plus d\'infos')?></a><br/>
	<div id="moreInfo" style="display:none;">
	<?php echo gettext('Répertoire'),gettext(' : ');?>	<input type="text" size="50" name="Dirname" value="<?php affichep('Dirname')?>" /><br/>
	<?php echo gettext('URL des métadonnées'),gettext(' : ');?>
	 <?php echo CORPUS_DAV, '/';affichep('Dirname');echo '/',affichep('Name');echo '-metadata.xml';?><br/>
	<?php echo gettext('Date de création'),gettext(' : ');?><input type="text"  size="50" name="CreationDate" value="<?php affichep('CreationDate',date('c'))?>" /><br/>
	<?php echo gettext('Date d\'installation'),gettext(' : ');?><input type="text"  size="50" name="InstallationDate" value="<?php affichep('InstallationDate',date('c'))?>" /><br/>
	</div>
	<?php
		if ($modif && !empty($Params['Name']) && !empty($Params['Type'])) {
			echo '<p style="text-align:center;"><input type="submit" name="Enregistrer" value="',gettext('Enregistrer'),'" /></p>';
		}
		if ($modif && !empty($metdataFile)) {
			echo '<p style="text-align:center;"><input type="submit" name="Enregistrer" value="',gettext('Enregistrer'),'" /></p>';
		}
	?>
</div>
</fieldset>
</form>
<?php

	function affichep ($param, $default='') {
		global $Params;
		echo !empty($Params[$param])?stripslashes($Params[$param]):$default;
	}
	function afficheo ($param, $option) {
		global $Params;
		echo '<option value="',$option,'"';
		if (!empty($Params[$param]) && $Params[$param]==$option) echo ' selected="selected" ';
		echo '>';
	}
			
	function afficheLanguesOptions($option) {
		global $LANGUES;
		asort($LANGUES,SORT_LOCALE_STRING);
		foreach ($LANGUES as $key => $val) {
    		echo "<option value='" . $key . "'";
    		if ($key==$option) {echo ' selected="selected" ';}
    		echo ">" . $val . "</option>\n";
		}
	}
	
	function creerCorpus($params) {
		$admins = preg_split("/[\s,;]+/", $params['Administrators']);		
		$name = $params['Name'];
		if (!preg_match('/^[A-Z][a-zA-Z0-9\-]+$/',$name)) {
			echo '<p class="erreur">',gettext('Le nom abrégé du dictionnaire contient des caractères non autorisés !'),'</p>';
			return '';
		}
		$dirname = $name.'_' . $params['Language1'];
		if (!empty($params['Language2'])) {
			$dirname .= '-' . $params['Language2'];
		}
		if (!empty($params['Dirname'])) {
			$olddirname = $params['Dirname'];
			if ($dirname !== $olddirname) {
				rename(CORPUS_SITE.'/'.$olddirname,CORPUS_SITE.'/'.$dirname);
				@unlink(CORPUS_SITE_PUBLIC.'/'.$olddirname);
			}
		}
		else {
			@mkdir(CORPUS_SITE.'/'.$dirname);
			@mkdir(CORPUS_SITE.'/'.$dirname.'/'.DIRTXT);
			@mkdir(CORPUS_SITE.'/'.$dirname.'/'.DIRTXT.'/'.$params['Language1']);
			if (!empty($params['Language2'])) {
				@mkdir(CORPUS_SITE.'/'.$dirname.'/'.DIRTXT.'/'.$params['Language2']);
			}
			@mkdir(CORPUS_SITE.'/'.$dirname.'/'.DIRXML);
			@mkdir(CORPUS_SITE.'/'.$dirname.'/'.DIRXML.'/'.$params['Language1']);
			if (!empty($params['Language2'])) {
				@mkdir(CORPUS_SITE.'/'.$dirname.'/'.DIRXML.'/'.$params['Language2']);
				@mkdir(CORPUS_SITE.'/'.$dirname.'/'.DIRXML.'/'.DIRLINKS);
			}
		}
		if (!empty($params['Access'])) {
			if ($params['Access'] == 'public') {
				symlink(CORPUS_SITE.'/'.$dirname,CORPUS_SITE_PUBLIC.'/'.$dirname);
			}
			else {
				@unlink(CORPUS_SITE_PUBLIC.'/'.$dirname);
			}
		}
		$corpusmetadata = creerCorpusMetadata($params);
		$myFile = CORPUS_SITE.'/'.$dirname."/".$name.'-metadata.xml';
		$fh = fopen($myFile, 'w') or die("impossible d'ouvrir le fichier ".$myFile);
		fwrite($fh, $corpusmetadata);
		fclose($fh);
		restrictAccess($dirname,$admins);
		
		return $dirname;
	}
?>
</div>
<?php include(RACINE_SITE.'include/footer.php');?>