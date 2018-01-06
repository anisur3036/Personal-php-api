<?php require_once 'header.php'; ?>
<?php 
	$objPost = new Post();
	$post = $objPost->show($_GET['id']);
	//var_dump($post);
?>
<!-- Page Header -->
<!-- Set your background image for this header on the line below. -->
<header class="intro-header" style="background-image: url('admin/img/<?php echo $post->image; ?>')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                <div class="post-heading">
                    <h1><?php echo $post->title; ?></h1>
                    <!-- <h2 class="subheading">Problems look mighty small from 150 miles up</h2> -->
                    <span class="meta">Posted by <a href="#"><?php echo $post->author; ?></a> <?php echo date('F j, Y, g:i a',strtotime($post->posted_date)); ?></span>
                </div>
            </div>
        </div>
    </div>
</header>
<article>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
			<p>
				<?php echo $post->body; ?>
			</p>
            </div>
        </div>
    </div>
</article>
<hr>
<div class="container">
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
            <h2>Related News</h2>
            <div class="row">
                <?php 
                    $relatedPosts = $objPost->relatedPost($post->cat_name, $post->id);
                    if ($relatedPosts) :
                    foreach ($relatedPosts as $relatedPost) : 
                ?>
                <div class="col-lg-4 col-md-4">
                    <h4 style="margin-bottom: -15px">
                        <a href="/?page=details&id=<?php echo $relatedPost->id ?>"><?php echo $relatedPost->title; ?></a>
                    </h4>
                    <p>
                        <?php echo substr($relatedPost->body, 0, 120); ?>
                    </p>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <p align="center">No Post Found</p>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>
