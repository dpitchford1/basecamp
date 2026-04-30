<?php
/**
 * Template Name: Sectors Page Template
 * Template Post Type: sector
 */
get_header();
?>
<main id="main-content" class="main--content content--region">
    <?php
    while ( have_posts() ) : the_post();
        
        $gallery = get_post_meta( get_the_ID(), '_micd_sector_gallery', true );
        if ( !is_array($gallery) ) $gallery = [];

        if ( count($gallery) || has_post_thumbnail() ) : ?>
            <div class="swiper hero--gallery global--hero">
                <div class="swiper-wrapper">
                    <?php /* First slide: default content */ ?>
                    <div class="swiper-slide first--slide">
                        <?php
                        if ( has_post_thumbnail() ) {
                            $thumb_id = get_post_thumbnail_id();
                            $img_landscape = wp_get_attachment_image_src( $thumb_id, 'miconcept-img-xxl' );
                            $img_portrait = wp_get_attachment_image_src( $thumb_id, 'portait-m' );
                            ?>
                            <picture>
                                <?php if ( $img_portrait ) : ?>
                                    <source srcset="<?php echo esc_url($img_portrait[0]); ?>" media="(max-width: 600px)">
                                <?php endif; ?>
                                <?php echo wp_get_attachment_image( $thumb_id, 'miconcept-img-xxl hero--img', false, [ 'loading' => 'eager' ] ); ?>
                            </picture>
                        <?php } ?>
                        <div class="swiper-slide-caption hero--text-block animate-slide-down">
                            <h2 class="hero--heading"><?php the_title(); ?></h2>
                            <?php the_content(''); ?>
                        </div>
                    </div>
                    <?php /* Gallery slides */ ?>
                    <?php foreach ( $gallery as $item ) :
                        // Get the image IDs from the metabox (only one image per slide)
                        $image_id = $item['image_id'] ?? '';
                        if ( ! $image_id ) continue;

                        // Get URLs for each size
                        $img_landscape = wp_get_attachment_image_src( $image_id, 'miconcept-img-xxl' );
                        $img_portrait = wp_get_attachment_image_src( $image_id, 'portait-m' );
                        ?>
                        <div class="swiper-slide">
                            <picture>
                                <?php if ( $img_portrait ) : ?>
                                    <source srcset="<?php echo esc_url($img_portrait[0]); ?>" media="(max-width: 600px)">
                                <?php endif; ?>
                                <?php echo wp_get_attachment_image( $image_id, 'miconcept-img-xxl hero--img', false, [ 'loading' => 'lazy' ] ); ?>
                            </picture>
                            <div class="parallax--text">
                                <?php if ( !empty($item['desc']) ) : ?>    
                                <h3 class="title project--meta"><?php echo esc_html($item['desc']); ?></h3>
                                <?php endif; ?>
                                <?php if ( !empty($item['client']) ) : ?>
                                <p class="client--name project--meta"><span class="is--bold">Client Location: </span><?php echo esc_html($item['client']); ?></p>
                                <?php endif; ?>
                                <p class="swiper-slide-caption project--meta"><?php the_title(); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
            
        <?php else:
            the_title( '<h2>', '</h2>' );
            if ( has_post_thumbnail() ) {
                the_post_thumbnail();
            }
            the_content();
        endif;

    endwhile;
    ?>
</main>

<?php
// Build top-level sectors list once for reuse (Explore + sidebar + grid)
$sectors = get_posts([
    'post_type'      => 'sector',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'post_parent'    => 0, // Only top-level sectors
    'no_found_rows'  => true,
]);
?>

<section class="fluid content--region">
    <h2 class="section--heading">Explore Our Other Sectors</h2>
    <p>Discover our various sectors, each with its own unique focus and offerings.</p>
    <article class="region swiper--sectors img--masking">
        <div class="swiper-wrapper">
        <?php if ( !empty($sectors) ) : ?>
            <?php foreach ( $sectors as $sector ) :
                $sector_thumb = get_the_post_thumbnail_url( $sector->ID, 'miconcept-img-sm' );
                if ( !$sector_thumb ) continue; ?>
                <div class="swiper-slide card--item has--overlay">
                    <a class="swiper--link" href="<?php echo esc_url( get_permalink($sector->ID) ); ?>">
                        <picture>
                            <source srcset="<?php echo esc_url($sector_thumb); ?>" media="(max-width: 600px)">
                            <?php echo wp_get_attachment_image( get_post_thumbnail_id($sector->ID), 'miconcept-img-sm', false, [ 'loading' => 'lazy', 'class' => 'card--img' ] ); ?>
                        </picture>
                    </a>
                    <h3 class="card--caption is--centered"><a href="<?php echo esc_url( get_permalink($sector->ID) ); ?>"><?php echo esc_html( $sector->post_title ); ?></a></h3>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </article>
</section>

<?php
// --- Featured Projects Section (moved outside <main>) ---
$featured_projects = get_children([
    'post_parent' => get_the_ID(),
    'post_type'   => 'sector',
    'post_status' => 'publish',
    'meta_key'    => '_micd_project_featured',
    'meta_value'  => '1',
    'orderby'     => 'menu_order',
    'order'       => 'ASC',
]);
if ( $featured_projects ) : ?>
<div class="featured--project is--standalone">
    <?php foreach ( $featured_projects as $project ) :
        $date = get_post_meta($project->ID, '_micd_project_date', true);
        $location = get_post_meta($project->ID, '_micd_project_location', true);
        $thumb_id = get_post_thumbnail_id($project->ID);
        $img_landscape = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'miconcept-img-xxl') : null;
        $img_portrait = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'portait-m') : null;
        ?>
        <article class="project--item">
            <div class="project--caption card--caption is--centered">
                <h3 class="">Featured Project</h3>
                <h4 class="hero--heading"><a href="<?php echo esc_url(get_permalink($project->ID)); ?>"><?php echo esc_html($project->post_title); ?></a></h4>
                <p class="flex--centered"><a class="slick--btn slick--secondary" href="<?php echo esc_url(get_permalink($project->ID)); ?>">
                <span class="circle" aria-hidden="true"><span class="icon arrow"></span></span>
                    <span class="button-text"><?php esc_html_e('View Project', 'micd'); ?></span></a></p>

                <!-- <p><a class="button" href="<?php echo esc_url(get_permalink($project->ID)); ?>">View Project</a></p> -->
            </div>
            <?php if ($thumb_id): ?>
                <a href="<?php echo esc_url(get_permalink($project->ID)); ?>">
                    <picture>
                        <?php if ($img_portrait): ?>
                            <source srcset="<?php echo esc_url($img_portrait[0]); ?>" media="(max-width: 600px)">
                        <?php endif; ?>
                        <?php echo wp_get_attachment_image($thumb_id, 'miconcept-img-xxl', false, [ 'loading' => 'lazy', 'class' => 'card--img' ] ); ?>
                    </picture>
                </a>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<script>document.addEventListener('DOMContentLoaded',function(){if(!window.Swiper){return}var mainEl=document.querySelector('.hero--gallery');if(mainEl){new Swiper(mainEl,{loop:true,pagination:{el:'.swiper-pagination',clickable:false},navigation:{nextEl:'.swiper-button-next',prevEl:'.swiper-button-prev'},slidesPerView:1,spaceBetween:0,speed:500,keyboard:{enabled:true},lazy:true})}var listEl=document.querySelector('.swiper--sectors');if(listEl){new Swiper(listEl,{loop:true,pagination:{el:'.swiper-pagination',clickable:false},navigation:{nextEl:'.swiper-button-next',prevEl:'.swiper-button-prev'},slidesPerView:1,spaceBetween:10,speed:600,keyboard:{enabled:true},lazy:true,breakpoints:{680:{slidesPerView:2},1440:{slidesPerView:3}}})}});</script>
<?php get_footer(); ?>
