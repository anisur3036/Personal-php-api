<?php require_once 'header.php'; ?>
<!-- Page Header -->
<!-- Set your background image for this header on the line below. -->
<header class="intro-header" style="background-image: url('admin/img/home-bg.jpg')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                <div class="site-heading">
                    <h1>Clean Blog</h1>
                    <hr class="small">
                    <span class="subheading">A Clean Blog Theme by Start Bootstrap</span>
                </div>
            </div>
        </div>
    </div>
    
</header>
<div class="container">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
            <?php
                $objPost = new Post();
                //$db = new DB();
            ?>  
             <?php 
            // $db->query( $pagingQuery );
                $posts = $objPost->paginate(1);
                foreach ($posts as $post) {
            ?>
                <div class="post-preview">
                    <a href="/?page=details&id=<?php echo $post->id; ?>">
                        <h2 class="post-title">
                            <?php echo $post->title; ?>
                        </h2>
                        <p>
                            <?php echo substr($post->body, 0, 120); ?> ...
                        </p>
                    </a>
                    <p class="post-meta">Posted by <a href="#" title="Author name"><?php echo $post->author; ?></a> on <?php echo date('F j, Y, g:i a',strtotime($post->posted_date)); ?>&nbsp;&nbsp;<i class="fa fa-eye" title="Views by visitor"> <?php echo $post->view; ?></i></p>
                </div>
                <hr>
            <?php }  ?>        
            </div>
            </div>
                <!-- Pager -->
            <div class="pull-right">
                <?php echo $objPost->links(1, "pagination pagination-sm" ); ?>
            </div> 
            </div>         
        </div>
    </div>
    <hr>
<?php require_once 'footer.php'; ?>
