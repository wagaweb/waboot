<?php

/**
 * Generate a temp file parsing commented include tags in the $filepath less file.
 *
 * @param $filepath (the absolute path to the file to parse (usually waboot.less or waboot-child.less)
 *
 * @return string filepath to temp file
 *
 * @since 0.7.0
 */
function parse_input_file($filepath){
	$inputFile = new SplFileInfo($filepath);
	if($inputFile->isReadable()){
		$inputFileObj = $inputFile->openFile();
		$tmpFile = new SplFileInfo($inputFile->getPath()."/tmp_".$inputFile->getFilename());
		$tmpFileObj = $tmpFile->openFile("w+");
		if($tmpFileObj->isWritable()){
			while (!$inputFileObj->eof()) {
				$line = $inputFileObj->fgets();
				if(preg_match("|\{@import '([a-zA-Z0-9\-/_.]+)'\}|",$line,$matches)){
					$fileToImport = new SplFileInfo(dirname($filepath)."/".$matches[1]);
					if($fileToImport->isFile() && $fileToImport->isReadable()){
						if($inputFile->getPath() == $fileToImport->getPath()){
							$line = "@import '{$fileToImport->getBasename()}';\n";
						}else{
							$line = "@import '{$fileToImport->getRealPath()}';\n";
						}
					}/*else{
						//If we are in the child theme, search the file into parent directory
						if(is_child_theme()){
							$fileToImport = new SplFileInfo(get_template_directory()."/sources/less/".$matches[1]);
							if($fileToImport->isFile() && $fileToImport->isReadable()){
								$line = "@import '{$fileToImport->getFilename()}';\n";
							}
						}
					}*/
				}
				$tmpFileObj->fwrite($line);
			}
			$filepath = $tmpFile->getRealPath();
		}
	}

	return $filepath;
}