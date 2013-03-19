<?php
chdir(realpath(dirname(__FILE__)));

$antivirus   = $argv[1];
$file        = $argv[2];
$id_analysis = $argv[3];

require_once 'RunnableAbstract.php';
require_once "{$antivirus}.php";

new $antivirus($file, $id_analysis);