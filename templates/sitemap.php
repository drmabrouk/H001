<?php
/**
 * Template Name: Healthedia XML/HTML Sitemap
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Healthedia Sitemap - Complete open-access index of health, sports physiology, and biomechanics index records.">
    <?php wp_head(); ?>
    <style>
        .healthedia-sitemap-wrapper {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 20px;
            box-sizing: border-box;
            min-height: 400px;
        }
        .healthedia-sitemap-title {
            font-size: 32px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .healthedia-sitemap-subtitle {
            font-size: 13px;
            color: #777777;
            margin-bottom: 30px;
        }
        .healthedia-sitemap-section {
            background-color: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.01);
            text-align: left;
        }
        .healthedia-section-title {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            color: #000000;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
        }
        .healthedia-sitemap-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .healthedia-sitemap-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .healthedia-sitemap-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .healthedia-sitemap-link {
            font-size: 14px;
            font-weight: 600;
            color: #000000;
            text-decoration: none;
        }
        .healthedia-sitemap-link:hover {
            text-decoration: underline;
        }
        .healthedia-sitemap-loc {
            font-size: 12px;
            color: #777777;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="healthedia-sitemap-wrapper">
        <h1 class="healthedia-sitemap-title">Healthedia Sitemap</h1>
        <p class="healthedia-sitemap-subtitle">Sitemap XML/HTML index of pages, portals, and open-access scientific publications.</p>

        <div class="healthedia-sitemap-section">
            <h2 class="healthedia-section-title">Primary Indexes</h2>
            <ul class="healthedia-sitemap-list">
                <li class="healthedia-sitemap-item">
                    <a href="<?php echo esc_url( home_url( '/healthedia-search/' ) ); ?>" class="healthedia-sitemap-link">Archive Search Homepage</a>
                    <span class="healthedia-sitemap-loc">/healthedia-search/</span>
                </li>
                <li class="healthedia-sitemap-item">
                    <a href="<?php echo esc_url( home_url( '/healthedia-auth/' ) ); ?>" class="healthedia-sitemap-link">Researcher Sign-in & Authentication Portal</a>
                    <span class="healthedia-sitemap-loc">/healthedia-auth/</span>
                </li>
                <li class="healthedia-sitemap-item">
                    <a href="<?php echo esc_url( home_url( '/healthedia-dashboard/' ) ); ?>" class="healthedia-sitemap-link">SaaS Administration Dashboard</a>
                    <span class="healthedia-sitemap-loc">/healthedia-dashboard/</span>
                </li>
            </ul>
        </div>

        <div class="healthedia-sitemap-section">
            <h2 class="healthedia-section-title">Institutional Directories</h2>
            <ul class="healthedia-sitemap-list">
                <li class="healthedia-sitemap-item">
                    <a href="<?php echo esc_url( home_url( '/sample-page' ) ); ?>" class="healthedia-sitemap-link">Sample Research Document</a>
                    <span class="healthedia-sitemap-loc">/sample-page</span>
                </li>
            </ul>
        </div>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
