<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'inc/autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Anis\Post;
use Anis\Config;

$objPost = new Post();
$posts = $objPost->paginate(2);

// // var_dump($post);
// // $objPost->insert([
// //     'name' => 'anis',
// //     'email' => 'anisu@yahoo.com',
// //     'fullname' => 'Anisur Rahman'
// // ]);
foreach ($posts as $post) {
    echo '<li>' . $post->title . '</li>';
}
echo $objPost->links(1, 'anis');

// var_dump($posts);
// echo ROOT_PATH;
