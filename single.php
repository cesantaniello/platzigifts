<?php get_header(); ?>

<main class="container my-3">
    <?php if(have_posts()){
        while(have_posts()){
            the_post();
        ?>
            <h1 class='my-3'><?php the_title() ?></h1>
            <div class="row">
                <div class="col-4">
                    <?php the_post_thumbnail('large'); ?>
                </div>
                <div class="col-8">
                    <?php the_content(); ?>
                </div>            
            </div>
        <?php get_template_part('template-parts/part', 'navigation') ?>
        <?php } ?>
    <?php } ?>
</main>

<?php get_footer(); ?>