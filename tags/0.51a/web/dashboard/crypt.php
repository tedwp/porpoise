<?php

function usage() {
	printf("%s\n",
<<<OUT
Usage (command line): php crypt.php <arg1> <arg2> ...
Usage (web): crypt.php?arg=<arg1>&arg=<arg2>&...

Outputs all arguments after running them through crypt() with no
second argument. When calling through the web the name of the arguments
does not really matter, every GET parameter is processed and printed.
OUT
	);
}

if (isset($_SERVER["TERM"])) {
	/* command line mode */
	$args = $_SERVER["argv"];
	unset($args[0]);	/* argv[0] contains the script's name */
} else {
	$args = $_GET;
	header("Content-Type: text/plain");
}

if (count($args) == 0) {
	usage();
	exit(1);
}

foreach ($args as $arg) {
	printf("%s\n", crypt($arg));
}
