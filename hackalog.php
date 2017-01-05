<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>h@ck@l0g</title>
</head>

<body>

<?php //First off, why does this exist and why is it in PHP? It scans log files for attempts to open admin pages and returns a list of IP addresses to block. It's in PHP because I am still getting up to speed in Rust. And yes I know there are better ways to do this than what I wrote. A month of logs my test site was a little over a gig. To run the entire 31 files takes about 20 seconds running on my local laptop server. 
foreach(glob("logs/*[!-]?[!.txt]") as $filename) {

	$handle = @fopen($filename, "r"); //The log file name to look at. Yes this could be replaced by a dialog but I wanted the code to be short as possible. We set the folder to logs at the same level as hackalog. If you have a lot of logs, you will need to bump up execution time & memory or process in smaller chunks.
	$targetfile = @fopen("logs/hackalog.txt","a"); //Write the offending lines here. We work from files for two reasons, because often this info is sent to someone else and sending these lines to the browser can cause malicious code to execute. So let's package it up. We append instead of over writing so you can run a bunch of times and still end up with one list of IPs.
	if ($handle) {
		while (($buffer = fgets($handle, 4096)) !== false) { //Pull the info line by line. So giant log files can be processed without blowing up the browsers.
			if ((strpos($buffer,'admin')!==false or strpos($buffer,'base64')!==false) and strpos($buffer,'career')!=true){ // Look for admin and base64 on each log line. You should be blocking base64 anyway but it doesn't hurt to look. We filter out the "career" section because if we list an administration job we want to leave those people alone.
				if (substr($buffer,0,3)<>"10."){ //Our admin pages are only accessible from in house. So any request from a 10.x.x.x address is ignored. Also the thing to disable if you are looking for a hack from an internal machine.
					fwrite($targetfile, $buffer); //Slap the offending line into the file
					$linesize=strlen($buffer); //this is to color code the sign of life tick marks to show longer ergo more complex attempts in the sign of life. The ranges are set and then a switch runs through them. NEVER echo the buffer to the browser. Doing so can and will execute some malicious codes.
					$range1to149=range(1,149);
					$range150to249=range(150,249);
					$range250to349=range(250,349);
					$range350to499=range(350,499);
					$range500to699=range(500,699);
					$range700to10000=range(700,10000);
					switch (true) {
						case in_array($linesize, $range1to149):
							$tickmark= "<span style=\"color:#ddf;word-wrap: break-word;\">&#9608;</span>";
							break;
						case in_array($linesize, $range150to249):
							$tickmark= "<span style=\"color:#ccf;word-wrap: break-word;\">&#9608;</span>"; 
							break;
						case in_array($linesize, $range250to349):
							$tickmark= "<span style=\"color:#99f;word-wrap: break-word;\">&#9608;</span>"; 
							break;
						case in_array($linesize, $range350to499):
							$tickmark= "<span style=\"color:#66f;word-wrap: break-word;\">&#9608;</span>";
							break;
						case in_array($linesize, $range500to699):
							$tickmark= "<span style=\"color:#33f;word-wrap: break-word;\">&#9608;</span>";
							break;
						case in_array($linesize, $range700to10000):
							$tickmark= "<span style=\"color:#00f;word-wrap: break-word;\">&#9608;</span>";
							break;
					}
					echo $tickmark;
				}
			}
		}
		fclose($targetfile);
		if (!feof($handle)) {
			echo "Error: Files not found\n";
		}
		fclose($handle);
	}
	
}
$data = file("logs/hackalog.txt"); //Now lets reopen the file. I did a regular open since it should be fairly small, 2,000-5,000 lines and we need it all at once to sort quickly. 
natsort($data); //Here we sort the contents by the IP addresses. So the hackalog.txt is by date/time and we just made a new one by IP. 
$data=array_filter($data);
file_put_contents("logs/hackalog2.txt", implode(PHP_EOL, $data)); //Write this one back to a file. This is where you can look at the history of individual IP's	
	
//Now lets extract just the missbehaving IP's. Yep pretty much the same as we did the first time except we only want IPs.
$handle = @fopen("logs/hackalog2.txt", "r");
$targetfile = @fopen("logs/BadIPs.txt","a"); //Again, we append so you can run this multiple times and build up the list from several days of logs

if ($handle) {
	$buffer = fread($handle, filesize("logs/hackalog2.txt"));
	preg_match_all("/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/", $buffer, $output_array); //pull the IPs only into an array.
	$ddd=array_to_1d($output_array); //Since preg pulled everything into a single line with each ip as a column we need to flip it
	$eee=array_unique($ddd); //Now we remove any duplicates
	}
	foreach($eee as $writeme){
		fwrite($targetfile, $writeme.PHP_EOL); //Here we step through the finished array and write out the lines. Because we do this every run, you can open the files at any point to see what you have. If you want to save time, you can make this output ready to cut and paste into an .htaccess file. Change $writeme."/n" to "deny from ".$writeme."/n"   Which will result in each line looking like this "deny from xxx.xxx.xxx.xxx"
	}
	fclose($targetfile);
	fclose($handle);	
	
	function array_to_1d($a) { //this does the heavy lifting for swapping the array
		$out = array();
		foreach ($a as $b) {
			foreach ($b as $c) {
				if (isset($c)) {
					$out[] = $c;
				}
			}
		}
		return $out;
}
echo "<br><br>Done. Darker marks are more serious attempts."	
	//From here you could run a polish on the BadIPs.txt file to sort them by IP. However most of the time you would just email the bad ip list after reviewing the hackalog2.txt file for false positives. Like that time I banned a web security company hired without my knowledge to test our security minutes after they started. 
?>

</body>
</html>