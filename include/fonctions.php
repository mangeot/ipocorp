<?php
	
	define ('DML_PREFIX','http://www-clips.imag.fr/geta/services/dml');
	define ('XLINK_PREFIX','http://www.w3.org/1999/xlink');
	
	function creerCorpusMetadata($params) {
		$res = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<corpus-metadata
   xmlns="http://www-clips.imag.fr/geta/services/dml"
   xmlns:d="http://www-clips.imag.fr/geta/services/dml" 
   xmlns:xlink="http://www.w3.org/1999/xlink" 
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www-clips.imag.fr/geta/services/dml
   http://www-clips.imag.fr/geta/services/dml/dml.xsd"
   category="'.$params['Category'].'" 
   creation-date="'.$params['CreationDate'].'" 
   installation-date="'.$params['InstallationDate'].'" 
   last-modification-date="'.date('c').'" 
   fullname="'.htmlspecialchars($params['NameC']).'"
   name="'.$params['Name'].'" 
   owner="'.$params['Owner'].'"
   pairs="'.$params['Pairs'].'"
   type="'.$params['Type'].'"> 
 <languages>
 	 <source-language texts="'.$params['SourceTexts'].'"  words="'.$params['SourceWords'].'" sentences="'.$params['SourceSentences'].'" d:lang="'.$params['Source'].'"/>
';
 	if (!empty($params['Target'])) {
 		$res .= ' 	 <target-language texts="'.$params['TargetTexts'].'" words="'.$params['TargetWords'].'" sentences="'.$params['TargetSentences'].'"  d:lang="'.$params['Target'].'"/>
';
 	}
 	$res .=' </languages>
 <contents>'.htmlspecialchars($params['Contents']).'</contents>
 <domain>'.htmlspecialchars($params['Domain']).'</domain> 
 <source>'.htmlspecialchars($params['Provenance']).'</source>
 <authors>'.$params['Authors'].'</authors>
 <legal>'.$params['Legal'].'</legal>
 <access>'.$params['Access'].'</access>
 <reference>'.htmlspecialchars($params['Reference']).'</reference>
 <comments>'.htmlspecialchars($params['Comments']).'</comments>
 <administrators>';
	$admins = preg_split("/[\s,;]+/", $params['Administrators']);
	foreach ($admins as $admin) {
		  $res .= '
		  <user-ref name="'.$admin.'"/>';
	}
 $res .= '
 </administrators>
</corpus-metadata>
';
		return $res;
	}
		
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
  		$infos['Pairs'] = $corp->getAttribute('pairs');
  		$infos['Contents'] = $corp->getElementsByTagName('contents')->item(0)->nodeValue;
  		$infos['Domain'] = $corp->getElementsByTagName('domain')->item(0)->nodeValue;
  		if (empty($infos['Domain'])) {echo 'domaine vide : ',$dico;}
  		$infos['Provenance'] = $corp->getElementsByTagName('source')->item(0)->nodeValue;
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
		$infos['AdministratorArray'] = $admins; 
		$infos['Administrators'] = join(',',$admins); 
  		$infos['Source'] = $corp->getElementsByTagName('source-language')->item(0)->getAttribute('d:lang');
  		$infos['SourceTexts'] = $corp->getElementsByTagName('source-language')->item(0)->getAttribute('texts');
  		$infos['SourceWords'] = $corp->getElementsByTagName('source-language')->item(0)->getAttribute('words');
  		$infos['SourceSentences'] = $corp->getElementsByTagName('source-language')->item(0)->getAttribute('sentences');
  		$infos['Target'] = $corp->getElementsByTagName('target-language')->item(0)->getAttribute('d:lang');
  		$infos['TargetTexts'] = $corp->getElementsByTagName('target-language')->item(0)->getAttribute('texts');
  		$infos['TargetWords'] = $corp->getElementsByTagName('target-language')->item(0)->getAttribute('words');
  		$infos['TargetSentences'] = $corp->getElementsByTagName('target-language')->item(0)->getAttribute('sentences');
		return($infos);
	}
	
	function creerCollectionMetadata($name, $src, $trg, $corpora, $adminstring) {
		$res = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<collection-metadata
   xmlns="http://www-clips.imag.fr/geta/services/dml"
   xmlns:d="http://www-clips.imag.fr/geta/services/dml" 
   xmlns:xlink="http://www.w3.org/1999/xlink" 
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www-clips.imag.fr/geta/services/dml
   http://www-clips.imag.fr/geta/services/dml/dml.xsd"
   creation-date="'.date('c').'" 
   installation-date="'.date('c').'" 
   last-modification-date="'.date('c').'" 
   name="'.$name.'"> 
 <languages>
 	 <source-language d:lang="'.$src.'"/>
';
 	if (!empty($trg)) {
 		$res .= ' 	 <target-language d:lang="'.$trg.'"/>
';
 	}
 	$res .=' </languages>
 <corpora>
';
 	foreach ($corpora as $corpus) {
 		$res .= '      <corpus name="'.$corpus.'"/>
';
 	}
 	$res .=' </corpora>
  <administrators>';
	$admins = preg_split("/[\s,;]+/", $adminstring);
	foreach ($admins as $admin) {
		  $res .= '
		  <user-ref name="'.$admin.'"/>';
	}
 $res .= '
 </administrators>
</collection-metadata>
';
		return $res;
	}

	function parseCollection($collection) {
		global $ISO6392TO1;
		$infos = array();
		$doc = new DOMDocument();
		$doc->load($collection);
		$corpora = $doc->getElementsByTagName("collection-metadata");
		$corp = $corpora->item(0);
  		$infos['Name'] = $corp->getAttribute('name');
  		$infos['CreationDate'] = $corp->getAttribute('creation-date');
  		$infos['InstallationDate'] = $corp->getAttribute('installation-date');
  		$admins = array();
  		$adminNodes = $corp->getElementsByTagName('user-ref');
  		foreach ($adminNodes as $admin) {
  			array_push($admins,$admin->getAttribute('name'));
  		}
		$infos['Administrators'] = $admins; 
  		$corpora = array();
  		$corporaNodes = $corp->getElementsByTagName('corpus');
  		foreach ($corporaNodes as $corpus) {
  			array_push($corpora,$corpus->getAttribute('name'));
  		}
		$infos['Corpora'] = $corpora; 
  		$infos['Source'] = $corp->getElementsByTagName('source-language')->item(0)->getAttribute('d:lang');
  		$infos['Target'] = $corp->getElementsByTagName('target-language')->item(0)->getAttribute('d:lang');
  		$infos['sr'] = $ISO6392TO1[$infos['Source']];
  		$infos['tr'] = $ISO6392TO1[$infos['Target']];
		return($infos);
	}
	
	function getCorpora() {
		$corpora = array();
	# initialise la liste des corpus
		if (is_dir(CORPUS_SITE) && $dh = opendir(CORPUS_SITE)) {
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
					}
					else {
						echo '<p class="erreur">',gettext('Le corpus suivant n\'a pas de métadonnées : '),$nom,'</p>';
					}
				}
			}
			closedir($dh);
		}
		ksort($corpora,SORT_LOCALE_STRING);
		return $corpora;
    }
    
    function getCollections() {
    	$collectiondir = CORPUS_SITE.DIRCOLLECTIONS;
		$collections = array();
		if ($dh = opendir($collectiondir)) {
			while (($file = readdir($dh)) !== false) {
				$metafile = $collectiondir . '/'.$file;
				if (is_file($metafile) && preg_match('/-metadata.xml$/',$file)) {
					$infos = parseCollection($metafile);
					$infos['Dirname']= $metafile;
					$collections[$infos['Name']] = $infos;
				}
			}
			closedir($dh);
		}
		ksort($collections,SORT_LOCALE_STRING);
		return $collections;
	}


	function restrictAccess($dirname, $users) {
		$filename = CORPUS_SITE.'/'.$dirname.'/.htaccess';		
		$htaccess = '<LimitExcept GET HEAD OPTIONS POST PROPFIND>
        Require user ';
        foreach ($users as $user) {
        	$htaccess .= $user.' ';
        }
        $htaccess .= '
</LimitExcept>
';
		$fh = fopen($filename, 'w') or die("impossible d'ouvrir le fichier ".$myFile);
		fwrite($fh, $htaccess);
		fclose($fh);
	}

	function afficheLanguesOptions($option) {
		echo 'option:',$option;
		global $LANGUES;
		asort($LANGUES,SORT_LOCALE_STRING);
		foreach ($LANGUES as $key => $val) {
    		echo "<option value='" . $key . "'";
    		if ($key==$option) {echo ' selected="selected" ';}
    		echo ">" . $val . "</option>\n";
		}
	}
	
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

# fonction récursive pour récupérer des fichers
function select_files ($dir, $criteria) {
	$res = array();
	if (is_dir($dir) && $dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			$filepath = $dir . '/'.$file;
			if (is_file($filepath) && preg_match($criteria,$file)) {
				$res[] = $filepath;
			}
			else if (is_dir($filepath) && $file != '.' && $file != '..') {
				$subres = select_files($filepath, $criteria);
				$res = array_merge($res, $subres);
			}
		}
		closedir($dh);
	}
	return $res;
}


  if (!function_exists('pathinfo_filename')) {
      if (version_compare(phpversion(), "5.2.0", "<")) {
       function pathinfo_filename($path) {
         $temp = pathinfo($path);
         if ($temp['extension']) {
           $temp['filename'] = substr($temp['basename'], 0, strlen($temp['basename']) -strlen($temp['extension']) -1);
         }
         else {
           $temp['filename'] = $temp['basename'];
         }
         return $temp['filename'];
       }
     }
     else {
       function pathinfo_filename($path) {
         return pathinfo($path,PATHINFO_FILENAME);
       }
     }
   }
?>
