<?php
declare(strict_types=1);

$repo = realpath(__DIR__ . '/..');
$output = __DIR__ . '/Infinity.phar';

if(file_exists($output)){
	unlink($output);
}

$phar = new Phar($output);
$phar->startBuffering();

$dirs = ['pocketmine', 'raklib', 'spl', 'synapse', 'native'];
foreach($dirs as $dir){
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($repo . '/' . $dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach($iterator as $file){
		if(!$file->isFile()){
			continue;
		}
		$local = 'src/' . $dir . '/' . substr($file->getPathname(), strlen($repo . '/' . $dir . '/'));
		$phar->addFile($file->getPathname(), $local);
	}
}

$phar->setStub('<?php define("pocketmine\\PATH", "phar://". __FILE__ ."/"); require_once("phar://". __FILE__ ."/src/pocketmine/PocketMine.php");  __HALT_COMPILER(); ?>');

$phar->stopBuffering();

echo "Infinity.phar build sucessfully\n";
