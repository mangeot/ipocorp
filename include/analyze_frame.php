<?php
// http://2bits.com/articles/installing-php-apc-gnulinux-centos-5.html
require('../init.php');
require(RACINE_SITE . 'include/Process.php');
session_start();
$url = basename($_SERVER['SCRIPT_FILENAME']);
$file = $_GET['filename'];
$pid = '';
$pidparam = '';
if (!empty($_GET['pid'])) {
	$pid = $_GET['pid'];
	$process = new Process();
	$process->setPid($pid);
	$pidparam = '&pid=' . $pid;
}
if(isset($_GET['randval'])) {
	if (!empty($_GET['pid'])) {
		$pid = $_GET['pid'];
    	if ($process->status()){
        	echo gettext('La génération est en cours.');
    	} else {
        	echo gettext('La génération est terminée.');
    	}
	}
	else {
   		$string = `tail -25 $file`;
   		preg_replace('/\r/',' ',$string);
   		echo $string;
   	}
	die;
}
?>
<html>
 <head>
<script src="../js/jquery-1.4.0.js" type="text/javascript"></script>
<link href="../style/style_progress.css" rel="stylesheet" type="text/css" />
<script>
$(document).ready(function() { 
//

	setInterval(function() 
		{
	$.get("<?php echo $url;?>"+ "?filename=<?php echo $file;?><?php echo $pidparam;?>&randval=0" , { 
		//get request to the current URL (upload_frame.php) which calls the code at the top of the page.  It checks the file's progress based on the file id "progress_key=" and returns the value with the function below:
	},
		function(data)	//return information back from jQuery's get request
			{
				$('#analyze_container').fadeIn(100);	//fade in progress bar	
				$('#analyze_completed').html(data);	//display the % completed within the progress bar
			}
		)},500);	//Interval is set at 500 milliseconds (the progress bar will refresh every .5 seconds)

});
</script>
</head>
<body style="margin:0px">
<!-- Progress bar divs -->
<div id="analyze_container">
  		<pre id="analyze_completed"></pre>
</div>
</body>
</html>