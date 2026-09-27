<?php

declare(strict_types=1);

/**
 * Example Template: single-project.php
 * Demonstrates consuming fields in templates cleanly.
 */

use YourVendor\ThemeFramework\Field;

get_header();

while (have_posts()) :
    the_post();
    $postId = get_the_ID();

    // 1. Text Field
    $location = Field::get('location');

    // 2. Media Field URL and metadata
    $iconUrl = Field::url('icon');
    $iconAttachment = Field::attachment('icon');

    // 3. Gallery Field Images (array of image URLs)
    $galleryImages = Field::images('gallery', $postId, 'large');
    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class('project-article'); ?>>
        <header class="project-header">
            <?php if ($iconUrl) : ?>
                <img src="<?php echo esc_url($iconUrl); ?>" alt="<?php echo esc_attr($iconAttachment['alt'] ?? ''); ?>" class="project-icon" />
            <?php endif; ?>

            <h1 class="project-title"><?php the_title(); ?></h1>

            <?php if ($location) : ?>
                <p class="project-location">
                    <span class="dashicons dashicons-location"></span>
                    <strong>Location:</strong> <?php echo esc_html($location); ?>
                </p>
            <?php endif; ?>
        </header>

        <div class="project-content">
            <?php the_content(); ?>
        </div>

        <?php if (!empty($galleryImages)) : ?>
            <section class="project-gallery">
                <h2>Project Gallery</h2>
                <div class="project-gallery-grid">
                    <?php foreach ($galleryImages as $imageUrl) : ?>
                        <div class="gallery-item">
                            <a href="<?php echo esc_url($imageUrl); ?>" target="_blank" rel="noopener">
                                <img src="<?php echo esc_url($imageUrl); ?>" alt="" loading="lazy" />
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </article>

    <?php
endwhile;

get_footer();
