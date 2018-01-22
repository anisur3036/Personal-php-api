<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'inc/autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Anis\Post;
use Anis\User;
use Anis\Form\Input;
use Anis\Validation\Token;
use Anis\Validation\Validator;
use Anis\Validation\ErrorHandler;

$user = new Post();
$re = $user->select(array(
    'id > ? AND id < ? ' => array(1, 3),
    'title LIKE ?'       => '%post%',
    'tag = :value' => [
    	'value' => 'php'
    ]
), ['body', 'title'], 1);
var_dump($re);
die();
