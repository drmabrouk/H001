<?php
/**
 * Default theme index / page template
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        .healthedia-default-page-wrapper {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 20px;
            font-family: 'Inter', system-ui, sans-serif;
            box-sizing: border-box;
            min-height: 400px;
        }
        .healthedia-default-title {
            font-size: 36px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 24px;
            letter-spacing: -0.5px;
        }
        .healthedia-default-content {
            font-size: 16px;
            line-height: 1.8;
            color: #333333;
        }
    </style>
</head>
<body>
    <div class="healthedia-default-page-wrapper">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <h1 class="healthedia-default-title"><?php the_title(); ?></h1>
            <div class="healthedia-default-content">
                <?php the_content(); ?>
            </div>
        <?php endwhile; endif; ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
