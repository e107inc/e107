<?  ###################################################################
   ##
  ##  Plugin for htmlArea, to run code through the server's HTML Tidy
 ##   By Adam Wright, for The University of Western Australia
##    This is the server-side script, which dirty code is run through.
##
##  Distributed under the same terms as HTMLArea itself.
##  This notice MUST stay intact for use (see license.txt).
##

	// Get the original source
	$source = $_POST['htisource_name'];
	$source = stripslashes($source);

	// Open a tidy process - I hope it's installed!
	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("file", "/dev/null", "a")
	);
	$process = proc_open("tidy -config html-tidy-config.cfg", $descriptorspec, $pipes);

	// Make sure the program started and we got the hooks...
	// Either way, get some source code into $source
	if (is_resource($process)) {

		// Feed untidy source into the stdin
		fwrite($pipes[0], $source);
		fclose($pipes[0]);

		// Read clean source out to the browser
		while (!feof($pipes[1])) {
			//echo fgets($pipes[1], 1024);
			$newsrc .= fgets($pipes[1], 1024);
		}
		fclose($pipes[1]);

		// Clean up after ourselves
		proc_close($process);

	} else {
		// Better give them back what they came with, so they don't lose it all...
		$newsrc = "<body>\n" .$source. "\n</body>";
	}

	// Split our source into an array by lines
	$srcLines = explode("\n",$newsrc);

	// Get only the lines between the body tags
	$startLn = 0;
	while ( strpos( $srcLines[$startLn++], '<body' ) === false && $startLn < sizeof($srcLines) );
	$endLn = $startLn;
	while ( strpos( $srcLines[$endLn++], '</body' ) === false && $endLn < sizeof($srcLines) );

	$srcLines = array_slice( $srcLines, $startLn, ($endLn - $startLn - 1) );

	// Create a set of javascript code to compile a new source string
	foreach ($srcLines as $line) {
		$jsMakeSrc .= "\tns += '" . str_replace("'","\'",$line) . "\\n';\n";
	}
?>


<html>
  <head>
    <script type="text/javascript">

function setNewHtml() {
	var htRef = window.parent._editorRef.plugins['HtmlTidy'];
	htRef.instance.processTidied(tidyString());
}
function tidyString() {
	var ns = '\n';
	<?=$jsMakeSrc;?>
	return ns;
}

    </script>
  </head>

  <body id="htiNewBody" onload="setNewHtml()">
  </body>
</html>
