<?php
/**
 * Our basic configurations, which inject our configurations inside the parent theme
 */
defined( 'ABSPATH' ) or die( 'Go eat veggies!' );

$configurations = [
    'elementor' => [
        'Waterfall_Reviews\Views\Elementor\Reviews'
    ],
    'enqueue'   => [
        ['handle' => 'wfr-style', 'src' => WFR_URI . 'assets/css/waterfall-reviews.min.css'], 
        ['handle' => 'wfr-chart', 'src' => WFR_URI . 'assets/js/vendor/chart.min.js', 'action' => 'register'],
        ['handle' => 'wfr-admin', 'src' => WFR_URI . 'assets/js/admin/wfr-admin.js', 'context' => 'admin'],
        [
            'handle'    => 'wfr-scripts', 
            'localize'  => [
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'debug'         => WP_DEBUG,
                'nonce'         => wp_create_nonce( 'we-love-good-reviews' ),                
            ], 
            'name'      => 'wfr',
            'src'       => WFR_URI . 'assets/js/waterfall-reviews.js', 
        ]
    ],
    'register' => [
        'post_types' => [
            [
                'name'      => 'reviews',
                'plural'    => __( 'Reviews', 'wfr' ),
                'singular'  => __( 'Review', 'wfr' ),
                'args'      => [
                    'menu_icon'     => 'dashicons-star-filled', 
                    'has_archive'   => true, 
                    'show_in_rest'  => true, 
                    'supports'      => ['author', 'comments', 'editor', 'thumbnail', 'title', 'custom-fields'], 
                    'rewrite'       => ['slug' => _x('reviews', 'Reviews Slug', 'wfr'), 'with_front' => false]
                ]
            ],
        ],
        'taxonomies' => [
            [
                'name'      => 'reviews_category',
                'object'    => 'reviews',
                'plural'    => __( 'Categories', 'wfr' ),
                'singular'  => __( 'Category', 'wfr' ),
                'args'      => [
                    'has_archive'       => true,
                    'hierarchical'      => true,
                    'rewrite'           => ['hierarchical' => true, 'slug' => _x('reviews/category', 'Reviews Category Slug', 'wfr'), 'with_front' => false],
                    'show_admin_column' => true,
                    'show_in_rest'      => true
                ]                
            ],
            [
                'name'      => 'reviews_tag',
                'object'    => 'reviews',
                'plural'    => __( 'Tags', 'wfr' ),
                'singular'  => __( 'tag', 'wfr' ),
                'args'      => [
                    'has_archive'       => false,
                    'hierarchical'      => false,
                    'rewrite'           => ['hierarchical' => false, 'slug' => _x('reviews/tag', 'Reviews Tag Slug', 'wfr'), 'with_front' => false, 'show_admin_column' => true],
                    'show_admin_column' => true,
                    'show_in_rest'      => true
                ]                
            ]                         
        ],
        'widgets' => [
            'Waterfall_Reviews\Views\Widgets\Filter',
            'Waterfall_Reviews\Views\Widgets\Prices',
            'Waterfall_Reviews\Views\Widgets\Similar'
        ]        
    ]
];