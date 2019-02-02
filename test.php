<?php

require_once('vendor/autoload.php');

use Mantis\Tree\Tree;

$data = array(
    array('id' => 1,  'parent' => 0,  'data' =>'Microsoft'),
    array('id' => 3,  'parent' => 0,  'data' =>'Adobe'),
    array('id' => 4,  'parent' => 0,  'data' =>'Apple'),
    array('id' => 5,  'parent' => 1,  'data' =>'Google'),
    array('id' => 6,  'parent' => 5,  'data' =>'IBM'),
);

$tree = new Tree($data);

// Example of Retrieving Data from a node
var_dump($tree->getNodeById(1)->getData());

// Print Example Tree Nodes
echo $tree;
