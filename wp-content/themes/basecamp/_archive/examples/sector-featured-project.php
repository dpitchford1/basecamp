<?php
/**
 * Template Name: Sector Featured Project
 * Template Post Type: sector
 */
get_header();
?>
<main id="main-content" class="main--content content--region">
    <?php
    while ( have_posts() ) : the_post();
        $date = get_post_meta(get_the_ID(), '_micd_project_date', true);
        $location = get_post_meta(get_the_ID(), '_micd_project_location', true);
        $gallery = get_post_meta(get_the_ID(), '_micd_sector_gallery', true);
        if (!is_array($gallery)) $gallery = [];
    ?>
<?php if ($gallery): ?>
    <div class="project-gallery">
        <div class="swiper hero--gallery">
            <div class="swiper-wrapper">
                <?php foreach ($gallery as $item):
                    $img_id = $item['image_id'] ?? '';
                    $desc = $item['desc'] ?? '';
                    $video = $item['video'] ?? '';
                    $img_landscape = $img_id ? wp_get_attachment_image_src($img_id, 'miconcept-img-xxl') : null;
                    $img_portrait = $img_id ? wp_get_attachment_image_src($img_id, 'portait-m') : null;
                    ?>
                    <div class="swiper-slide">
                        <?php if ($video): ?>
                            <div class="gallery-video">
                                <?php echo wp_oembed_get($video); ?>
                            </div>
                        <?php elseif ($img_id): ?>
                            <picture>
                                <?php if ($img_portrait): ?>
                                    <source srcset="<?php echo esc_url($img_portrait[0]); ?>" media="(max-width: 600px)">
                                <?php endif; ?>
                                <?php echo wp_get_attachment_image($img_id, 'miconcept-img-xxl hero--img'); ?>
                            </picture>
                        <?php endif; ?>
                        <div class="parallax--text">
                        <?php if ( !empty($item['desc']) ) : ?>    
                        <p class="title project--meta"><?php echo esc_html($item['desc']); ?></p>
                        <?php endif; ?>
                        <?php if ( !empty($item['client']) ) : ?>
                        <p class="client--name project--meta"><span class="is--bold">Client Location: </span></span><?php echo esc_html($item['client']); ?></p>
                        <?php endif; ?>
                        <p class="swiper-slide-caption project--meta"><?php the_title(); ?></p>
                    </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
    <script>document.addEventListener('DOMContentLoaded',function(){if(window.Swiper){new Swiper('.hero--gallery',{loop:true,pagination:{el:'.swiper-pagination',clickable:false},navigation:{nextEl:'.swiper-button-next',prevEl:'.swiper-button-prev'},slidesPerView:1,spaceBetween:0,speed:500,keyboard:{enabled:true},lazy:true})}});</script>
<?php endif; ?>

    <section class="fluid project-meta">
        <h2 class="section--heading">Featured Project: <?php the_title(); ?></h2>
        <?php if ($date): ?>
            <div class="project-date"><strong>Date:</strong> <?php echo esc_html($date); ?></div>
        <?php endif; ?>
        <?php if ($location): ?>
            <div class="project-location"><strong>Location:</strong> <?php echo esc_html($location); ?></div>
        <?php endif; ?>
        <div class="project-content">
            <?php the_content(); ?>
        </div>
    </section>
<?php endwhile; ?>
</main>
<?php
$sectors = get_posts([
    'post_type'      => 'sector',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'post_parent'    => 0,
    'no_found_rows'  => true,
]);
?>

<section class="fluid content--region">
    <h3 class="section--heading">Explore Sectors</h3>
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
                    <h4 class="card--caption is--centered"><a href="<?php echo esc_url( get_permalink($sector->ID) ); ?>"><?php echo esc_html( $sector->post_title ); ?></a></h4>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </article>
</section>

<script>document.addEventListener('DOMContentLoaded',function(){if(!window.Swiper){return}var listEl=document.querySelector('.swiper--sectors');if(listEl){new Swiper(listEl,{loop:true,pagination:{el:'.swiper-pagination',clickable:false},navigation:{nextEl:'.swiper-button-next',prevEl:'.swiper-button-prev'},slidesPerView:1,spaceBetween:10,speed:600,keyboard:{enabled:true},lazy:true,breakpoints:{680:{slidesPerView:2},1440:{slidesPerView:3}}})}});</script>
<?php get_footer(); ?>