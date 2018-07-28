<?php
/**
 * Created by PhpStorm.
 * User: Radioactiveman
 * Date: 27.07.2018
 * Time: 17:37
 */

namespace Parser;
require_once 'class/Log_Parser.php';
$argv = $_SERVER['argv'];

if (isset($argv[1]))//Есть наименование файла
{
    $parser = new Log_Parser($argv[1]);
    $parser->read_log_file();
    $output = $parser->print_results();
    print_r($output);
}
else
{
    print_r('Необходимо указать файл');
}
