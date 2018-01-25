<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'inc/autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Anis\Post;
use Anis\User;
use Anis\Form\Input;
use Anis\Validation\Token;
use Anis\Validation\Validator;
use Anis\Validation\ErrorHandler;

// $p = (new Post)->findByTitle('This is 3rd Post')->get();
// $p = Post::findByTitle('This is 3rd Post')->get();

/*$p = Post::selectWhere(array(
    'id > ? AND id < ?' 	=> array(1, 3),
    'title LIKE ?'       	=> '%s%',
    'tag = :value' 			=> [
    	'value' => 'php'
    ]
), ['body', 'title'], 1)->get();*/

// $p = Post::where('id', '=', 2)->count();


/*$obj = new Post;
$p = $obj->paginate(1);
var_dump($obj->links(1, 'anis'));*/

// $p = Post::all();

// $p = Post::where('id', '=', 1)->get();
$post = new User;
/*$p->insert([
	'username' => 'Dev'
]);

$p->update(2, [
	'username' => 'Dev'
]);*/

// 
// var_dump($p);
// die();



$validation = new Validator(new ErrorHandler, $post);

if (Input::exists()) {
	if (Token::check(Input::get('token'))) {
		$validation->check($_POST, [
			'username' => [
				'required' => true,
				'maxlength' => 20,
				'minlength' => 3,
				'alnum' => true,
				'unique' => 'users'
			],
			'email' => [
				'required' => true,
				'maxlength' => 255,
				'email' => true,
			],
			'password' => [
				'required' => true,
				'minlength' => 6,
			],
			'password_again' => [
				'match' => 'password'
			],
		]);
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Form Validation</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-sm-6 col-md-6 col-lg-6 col-sm-offset-3 col-md-offset-3 col-lg-offset-3"> 
				
				<form action="" method="POST" role="form">
					<legend>Form Validation</legend>
					<div class="form-group <?php echo $validation->errors()->first('username') ?  'has-error' : ''  ?>">
						<label for="username">Username: </label>
						<input type="text" name="username" class="form-control" id="username" value="<?php old('username') ?>">
						<?php 
							echo $validation->fails()
							? '<span class="help-block">'. $validation->errors()->first('username') .'</span>' 
							: ''
						?>
					</div>
				
					<div class="form-group <?php  echo $validation->errors()->first('email') ?  'has-error' : ''  ?>">
						<label for="email">Email</label>
						<input type="text" name="email" class="form-control" id="email" value="<?php old('email') ?>">
						<?php 
							echo $validation->fails()
							? '<span class="help-block">'. $validation->errors()->first('email') .'</span>' 
							: ''
						?>
					</div>

					<div class="form-group <?php  echo $validation->errors()->first('password') ?  'has-error' : ''  ?>">
						<label for="password">Password</label>
						<input type="password" name="password" class="form-control" id="password">
						<?php 
							echo $validation->fails()
							? '<span class="help-block">'. $validation->errors()->first('password') .'</span>' 
							: ''
						?>
					</div>

					<div class="form-group <?php  echo $validation->errors()->first('password_again') ?  'has-error' : ''  ?>">
						<label for="password_again">Repeat Password</label>
						<input type="password" name="password_again" class="form-control" id="password_again">
						<?php 
							echo $validation->fails()
							? '<span class="help-block">'. $validation->errors()->first('password_again') .'</span>' 
							: ''
						?>
					</div>
					<input type="hidden" name="token" value="<?php echo Token::generate('name'); ?>">
					<button type="submit" name="submit" class="btn btn-primary">Submit</button>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
