<?php get_header(); ?>

<main class="container">
    <?php if(have_posts()){
        while (have_posts()){
            the_post(); ?>
        <h1 class="my-3"><?php the_title(); ?>!!</h1>
        <?php the_content(); ?>
        <?php }
    }?>

    <div class="lista-productos my-5">
        <h2 class='text-center'>PRODUCTOS</h2>
        <div class="row">
            <div class="col-12">
                <select class="form-control" name="categorias-productos" id="categorias-productos">
                    <option value="">Categorías de productos</option>
                    <?php 
                        $terms = get_terms('categorias-productos', array('hide_empty' => true)); 
                    ?>
                    <?php 
                        foreach ($terms as $term){
                            echo '<option value="'.$term->slug.'">'.$term->name.'</option>';
                        }?>
                </select>
            </div>
        </div>
        <div class="row justify-content-center">
            <?php 
                $args = array(
                    'post_type' => 'producto',
                    'post_per_page' => -1,
                    'order' => 'ASC',
                    'orderby' => 'title'

                );

                $productos = new WP_Query($args);

                if($productos->have_posts()){
                    while($productos->have_posts()){
                        $productos->the_post();
                    ?>

                    <div class="col-4">
                        <figure>
                            <?php the_post_thumbnail('large'); ?>
                        </figure>
                        <h4 class='my-3 text-center'>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h4>
                    </div>        

                    <?php }
                }
            ?>
        </div>
    </div>
    <div class="container text-center">
        <div class="row">
            <div class="col-12">
                <h2>Últimas Novedades</h2>
            </div>
        </div>
        <div id="resultado-novedades" class="row">

        </div>
    </div>
</main>

<?php get_footer(); ?>