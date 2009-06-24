<?php



/* Copyright 2007-2008 Tim Weber <scy-proj-qb@scytale.name>

   This file is part of qb <http://scytale.name/proj/qb/>.

   See the LICENSE file for legal stuff.
   */



/* This script does the following things:
   ======================================
   
   - Find each source file (QB_SUF_SRC suffix) in the QB_SRC directory.
   - For each of them, check whether a corresponding QB_SUF_CRE file exists
     in the QB_META directory.
   - If there is one (and only then), compare the timestamp of the .cre file
     metadata and the one that is written inside the file.
   - If there is no timestamp in the file, write its metadata timestamp.
   - If the contained timestamp is older than the metadata one, assume that
     the metadata is broken and set it to the contained time.
   - TODO: Go through the QB_META directory and find .cre files which don't have
     a corresponding source file. Warn about them.
   - Print confused warnings for every other strange thing that happens.
   */



if (php_sapi_name() != 'cli')
	die("This needs to be run from the command line!\n");

// Where to find the config file.
$conf = 'lib/qb-0.2.conf.php';

// Allow $conf override as command line parameter.
if ($_SERVER['argc'] > 1)
	$conf = $_SERVER['argv'][1];

// Load config values.
require_once($conf);

// Check if QB_SRC is a valid directory.
if (!is_dir(QB_SRC))
	die("Source directory '" . QB_SRC . "' does not exist or is no directory.\n");

// Queue the top of QB_SRC to be searched.
$scanq = array('');

while (count($scanq) > 0) {
	$reldir = array_shift($scanq);
	$qualdir = rtrim(QB_SRC, '/') . "/$reldir";
	$ls = scandir($qualdir);
	foreach ($ls as $file) {
		if ($file == '.' || $file == '..')
			continue;
		$relfile = "$reldir$file";
		$qualfile = "$qualdir/$file";
		if (is_dir($qualfile)) {
			$scanq[] = "$relfile/";
			continue;
		}
		if (substr($file, strlen(QB_SUF_SRC) * (-1)) == QB_SUF_SRC) {
			echo("$relfile: ");
			$metafile = rtrim(QB_META, '/') . '/' . substr($relfile, 0, strlen(QB_SUF_SRC) * (-1)) . QB_SUF_CRE;
			if (is_file($metafile)) {
				$mtime = filemtime($metafile);
				$ts = @file_get_contents($metafile);
				if ($ts == 0) {
					echo('WARNING: no timestamp in file, writing... ');
					$ok = @file_put_contents($metafile, $mtime);
					if ($ok) {
						echo('ok.');
					} else {
						echo('ERROR: failed to write.');
					}
				} else {
					if ($ts < $mtime) {
						echo('WARNING: timestamp in file is older than mtime, fixing... ');
						$ok = @touch($metafile, $ts);
						if ($ok) {
							echo('ok.');
						} else {
							echo('ERROR: failed to touch.');
						}
					} else if ($ts > $mtime) {
						echo('WARNING: timestamp in file is NEWER than mtime, wtf?! - skipping');
					} else {
						echo('ok.');
					}
				}
			} else {
				echo("WARNING: no meta file ($metafile), skipping");
			}
			echo("\n");
		}
	}
}



?>
