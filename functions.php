<?php 

function init_template(){
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');

    register_nav_menus(
        array(
            'top_menu' => 'Menú Principal'
        )
        );
} 

add_action('after_setup_theme','init_template');

function assets(){
    wp_register_style('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', '', '4.5.2', 'all');
    wp_register_style('montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat&display=swap', '', '1.0', 'all');

    wp_enqueue_style('estilos', get_stylesheet_uri(), array('bootstrap', 'montserrat'), '1.1', 'all');
    wp_register_script('popper', 'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js', '', '1.16.1', true);
    wp_enqueue_script('boostraps','https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery', 'popper'), '4.5.2', true);

    wp_enqueue_script('custom', get_template_directory_uri().'/assets/js/custom.js', '', '1.0', true);
    wp_localize_script(
        'custom',
        'pg',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'apiurl' => home_url('/wp-json/pg/v1/')
        )
    );
}

add_action('wp_enqueue_scripts','assets');

function sidebar(){
    register_sidebar(
        array(
            'name' => 'Pie de página',
            'id' => 'footer',
            'description' => 'Zona de widgets para pie de página',
            'before_title' => '<p>',
            'after_title' => '</p>',
            'before_widget' => '<div id="%1$s" class="%2$s">',
            'after_widget' => '</div>'
        )
    );
}

add_action('widgets_init', 'sidebar');

function productos_type(){
    $labels = array(
        'name' => 'Productos',
        'singular_name' => 'Producto',
        'menu_name' => 'Productos',
    );

    $args = array(
        'label' => 'Productos',
        'description' => 'Productos de Platzi',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'revisions'),
        'public' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-cart',
        'can_export' => true,
        'publicly_queryable' => true,
        'rewrite' => true,
        'show_in_rest' => true
    );
    register_post_type('producto', $args);
}
add_action( 'init', 'productos_type');

add_action('init', 'lstCreateCustomTaxonomies');
function lstCreateCustomTaxonomies(){

    $taxonomy = array(
        'labels' => array(
            'name'          => _x('Categorías de Productos', 'taxonomy general name', 'ls19'),
            'singular_name' => _x('Categoría de Productos', 'taxonomy singular name', 'ls19'),
            'search_items'  => __('Buscar categoría'),
            'all_items'     => __('Todas las categorías'),
            'edit_item'     => __('Editar categoría'),
            'update_item'   => __('Actualizar categoría'),
            'add_new_item'  => __('Agregar categoría'),
            'menu_name'     => __('Categorías de Productos')
        ),
        'slug' => 'categorias-productos',
        'post_types' => array('producto')
    );

    $args = array(
        'hierarchical'      => true,
        'public'            => true,
        'labels'            => $taxonomy['labels'],
        'show_ui'           => true,
        'show_in_nav_menus' => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => $taxonomy['slug'])
    );
    register_taxonomy($taxonomy['slug'], $taxonomy['post_types'], $args);
}

add_action("wp_ajax_nopriv_pgFiltroProductos", "pgFiltroProductos");
add_action("wp_ajax_pgFiltroProductos", "pgFiltroProductos");
function pgFiltroProductos(){
    $args = array(
        'post_type' => 'producto',
        'posts_per_page' => -1,
        'order'     => 'ASC',
        'orderby' => 'title'
    );

    if ($_POST['categoria']){
    $args['tax_query'] = array(
            array(
                'taxonomy' => 'categorias-productos',
                'field' => 'slug',
                'terms' => $_POST['categoria']
                )
            );
        }

    $productos = new WP_Query($args); 

    if ($productos -> have_posts()){
        $return = array();
        while ($productos -> have_posts()) {
            $productos -> the_post();
            $return[] = array(
                'imagen' => get_the_post_thumbnail(get_the_ID(), 'large'),
                'link' => get_the_permalink(),
                'titulo' => get_the_title()
            );
        }
        wp_send_json($return);
    }
}

add_action('rest_api_init', 'novedadesAPI');
function novedadesAPI(){
    register_rest_route('pg/v1', 
                        '/novedades/(?P<cantidad>\d+)', 
                        array(
                            'methods' => 'GET',
                            'callback' => 'pedidoNovedades'
                        ));
}

function pedidoNovedades($data){
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $data['cantidad'],
        'order'     => 'ASC',
        'orderby' => 'title'
    );
    $novedades = new WP_Query($args);

    if ($novedades -> have_posts()){
        $return = array();
        while ($novedades -> have_posts()) {
            $novedades -> the_post();
            $return[] = array(
                'imagen' => get_the_post_thumbnail(get_the_ID(), 'large'),
                'link' => get_the_permalink(),
                'titulo' => get_the_title()
            );
        }
        return $return;
    }

}

// Asignación de la función de registro del bloque al Hook "init"
add_action('init', 'pgRegisterBlock');

// Función de registro del bloque
function pgRegisterBlock()
{
    // Tomamos el archivo PHP generado en el Build
    $assets = include_once get_template_directory().'/blocks/build/index.asset.php';

    wp_register_script(
        'pg-block', // Handle del Script
        get_template_directory_uri().'/blocks/build/index.js', // Usamos get_template_directory_uri() para recibir la URL del directorio y no el Path
        $assets['dependencies'], // Array de dependencias generado en el Build
        $assets['version'] // Cada Build cambia la versión para no tener conflictos de caché
    );

    register_block_type(
        'pg/basic', // Nombre del bloque
        array(
            'editor_script' => 'pg-block', // Handler del Script que registramos arriba
            'attributes'      => array( // Repetimos los atributos del bloque, pero cambiamos los objetos por arrays
                'content' => array(
                    'type'    => 'string',
                    'default' => 'Hello world'
                )
            ),
            'render_callback' => 'pgRenderDinamycBlock' // Función de callback para generar el SSR (Server Side Render)
        )
    );
}

function pgRenderDinamycBlock($attributes, $content){
    return '<h2>'.$attributes['content'].'</h2>';
}

add_action('acf/init', 'acfRegisterBlocks');
function acfRegisterBlocks(){
    acf_register_block(
        array(
            'name' => 'pg-institucional',
            'title' => 'Institucional',
            'description' => '',
            'render_template' => get_template_directory().'/template-parts/institucional.php',
            'category' => 'layout',
            'icon' => 'smiley',
            'mode' => 'edit'
        )
        );
}