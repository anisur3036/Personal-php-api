<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'inc/autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Anis\Post;

$objPost = new Post();
// var_dump($objPost->pdo);


// $post = $objPost->selectAll();
// var_dump($post);
// $objPost->insert([
//     'name' => 'anis',
//     'email' => 'anisu@yahoo.com',
//     'fullname' => 'Anisur Rahman'
// ]);

$posts = $objPost->selectAll();
foreach ($posts as $post) {
	echo '<li>' . $post->title . '</li>';
}



