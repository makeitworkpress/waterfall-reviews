<?php
/**
 * Our customizer additional configurations
 */
defined( 'ABSPATH' ) or die('Go eat veggies!');

/**
 * We initialize our initial ratingsfields here, and add it later once we have completed adding all fields
 */
$themeOptions = wf_get_theme_option(); 


/**
 * Adds our additional customizer fields for review singles and archives
 * This is a woefully long array chain, might we do this more smart?
 */

// Within header
$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_header_title',
    'title'         => __('Title Section Review Fields', 'wfr'),  
    'type'          => 'heading'   
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_header_media',
    'title'         => __('Load media in the title header.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_header_rating',
    'title'         => __('Display overall rating in the title header.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_header_rating_criteria',
    'title'         => __('Display criteria rating in the title header.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_header_prices_disable',
    'title'         => __('Disable prices within the title header.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_prices_best',
    'title'         => __('Display the best price.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'selector'      => [
        'html'      => true,
        'selector'  => '.wfr-price-button span'
    ],
    'default'       => __('View', 'wfr'),
    'id'            => 'reviews_price_button',
    'title'         => __('Price button label', 'wfr'),
    'description'   => __('What would the button text for price buttons?', 'wfr'),
    'transport'     => 'postMessage',
    'type'          => 'input'    
];

// At the beginning of content
$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_title',
    'title'         => __('Main Content Review Fields', 'wfr'),  
    'type'          => 'heading'   
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_summary_disable',
    'title'         => __('Disable the review summary at the beginning of the content.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => __('Advantages', 'wfr'),
    'id'            => 'reviews_ad_title',
    'title'         => __('Advantages Title', 'wfr'),
    'description'   => __('What would the title above the advantages in the review summary?', 'wfr'),
    'type'          => 'input'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => __('Disadvantages', 'wfr'),
    'id'            => 'reviews_dis_title',
    'title'         => __('Disadvantages Title', 'wfr'),
    'description'   => __('What would the title above the disadvantages in the review summary?', 'wfr'),
    'type'          => 'input'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_rating_disable',
    'title'         => __('Disable the review rating at the beginning of the content.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_prices',
    'title'         => __('Show prices at the beginning of review content.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_properties_before',
    'title'         => __('Show product properties at the beginning of review content.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_criteria',
    'title'         => __('Show criteria properties within product properties.', 'wfr'),
    'type'          => 'checkbox'    
];

// After the content
$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_summary_after',
    'title'         => __('Show review summary after the content.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_rating_after',
    'title'         => __('Show rating after the content.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_prices_after',
    'title'         => __('Show prices after the content.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_content_properties_after',
    'title'         => __('Show product properties after the content.', 'wfr'),
    'type'          => 'checkbox'    
];

// Related
$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_related_title_heading',
    'title'         => __('Extra Settings for Related Reviews', 'wfr'),  
    'type'          => 'heading'   
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => 'none',
    'description'   => __('Determines if you load the standard featured image or from the media settings.', 'waterfall'),
    'id'            => 'reviews_related_featured',
    'choices'       => [
        'standard'  => __('Standard Featured Image', 'wfr'),
        'logo'      => __('Image from Logo in Media', 'wfr')
    ],
    'title'         => __('Featured Image Related Reviews', 'waterfall'),
    'type'          => 'select'
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_related_price',
    'title'         => __('Show Price in Related Reviews', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_related_price_button',
    'title'         => __('Optional Text for Pricebutton', 'wfr'),
    'description'   => __('If set, shows a button with above text linking to the supplier.', 'waterfall'),
    'type'          => 'input', 
    'selector'      => [
        'html'      => true,
        'selector'  => '.related-posts .wfr-price-button span'
    ],
    'transport'     => 'postMessage'      
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_related_rating',
    'title'         => __('Show Rating in Related Reviews.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_related_summary',
    'title'         => __('Show Summary in Related Reviews.', 'wfr'),
    'type'          => 'checkbox'    
];

// Similar
$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar_title_heading',
    'title'         => __('Similar Reviews Settings', 'wfr'),  
    'type'          => 'heading'   
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar',
    'title'         => __('Enable section with Similar Reviews.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => __('Similar', 'wfr'),
    'id'            => 'reviews_similar_title',
    'title'         => __('Title above Similar Reviews', 'wfr'),
    'type'          => 'input'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar_number',
    'title'         => __('Number of Similar Reviews', 'wfr'),
    'type'          => 'number'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => 'grid',
    'id'            => 'reviews_similar_style',
    'title'         => __('Style Similar Reviews.', 'wfr'),
    'choices'       => wf_get_grid_options(),
    'type'          => 'select'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => 'third',
    'description'   => __('Amount of grid columns for posts.', 'waterfall'),
    'id'            => 'reviews_similar_grid',
    'choices'       => wf_get_column_options(),
    'title'         => __('Similar Reviews  Posts Columns', 'waterfall'),
    'type'          => 'select'
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'description'   => __('Minimum height of related posts in pixels.', 'waterfall'),
    'id'            => 'reviews_similar_height',
    'title'         => __('Similar Reviews  Posts Height', 'waterfall'),
    'selector'      => array('selector' => '.related-posts .molecule-post', 'property' => 'min-height'),
    'transport'     => 'postMessage',                 
    'type'          => 'number'
]; 

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => 'square-ld',
    'description'   => __('Featured Image size within related posts.', 'waterfall'),
    'id'            => 'reviews_similar_image',
    'choices'       => wf_get_image_sizes(),
    'title'         => __('Similar Reviews  Featured Image Size', 'waterfall'),
    'type'          => 'select'
]; 

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => 'none',
    'description'   => __('Float of featured image within the related posts.', 'waterfall'),
    'id'            => 'reviews_similar_image_float',
    'choices'       => wf_get_float_options(),
    'title'         => __('Similar Reviews Featured Image Float', 'waterfall'),
    'type'          => 'select'
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar_image_enlarge',
    'title'         => __('Enlarge Featured Image on Hover', 'waterfall'),
    'type'          => 'checkbox'
];  

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar_button',
    'title'         => __('Text of Similar Reviews Button', 'waterfall'),
    'description'   => __('The title inside the buttons. Leave empty to remove the button.', 'waterfall'), 
    'selector'      => array('selector' => '.wfr-similar-reviews .atom-button span', 'html' => true),
    'transport'     => 'postMessage',                   
    'type'          => 'input'
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => 'none',
    'description'   => __('Determines if you load the standard featured image or from the media settings.', 'waterfall'),
    'id'            => 'reviews_similar_featured',
    'choices'       => [
        'standard'  => __('Standard Featured Image', 'wfr'),
        'logo'      => __('Image from Logo in Media', 'wfr')
    ],
    'title'         => __('Featured Image Similar Reviews', 'waterfall'),
    'type'          => 'select'
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar_price',
    'title'         => __('Show Price in Similar Reviews.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar_price_button',
    'title'         => __('Optional Text for Pricebutton', 'wfr'),
    'description'   => __('If set, shows a button with above text linking to the supplier.', 'waterfall'),
    'type'          => 'input',
    'selector'      => [
        'html'      => true,
        'selector'  => '.wfr-similar-reviews .wfr-price-button span'
    ],
    'transport'     => 'postMessage'        
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar_rating',
    'title'         => __('Show Rating in Similar Reviews.', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_similar_summary',
    'title'         => __('Show Summary in Similar Reviews.', 'wfr'),
    'type'          => 'checkbox'    
];

if( isset($themeOptions['rating_visitors']) && $themeOptions['rating_visitors'] ) {

    $layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
        'default'       => '',
        'id'            => 'reviews_visitor_title',
        'title'         => __('Visitor Ratings Settings', 'wfr'),
        'type'          => 'heading'    
    ];    

    $layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
        'default'       => '',
        'id'            => 'reviews_visitors_rating_add',
        'title'         => __('Display average user rating.', 'wfr'),
        'description'   => __('Displays the average rating of users under the overall rating.', 'wfr'),
        'type'          => 'checkbox'    
    ];

    $layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
        'default'       => '',
        'id'            => 'reviews_visitors_rating_component',
        'title'         => __('Display complete user ratings.', 'wfr'),
        'description'   => __('Displays the user ratings above the comments section, including ratings for criteria if added.', 'wfr'),
        'type'          => 'checkbox'    
    ];

    $layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
        'default'       => __('Average Visitor Rating', 'wfr'),
        'id'            => 'reviews_visitors_rating_title',
        'title'         => __('Title above complete user ratings.', 'wfr'),
        'description'   => __('The title above the ratings on top of the comments section.', 'wfr'),
        'type'          => 'input',
        'selector'      => [
            'html'      => true,
            'selector'  => '.wfr-rating-average-visitors-title'
        ],
        'transport'     => 'postMessage'              
    ]; 
    
    $layoutPanel['fields']['sections']['reviews_content']['fields'][] = [
        'default'       => '',
        'id'            => 'reviews_visitors_rating_reply',
        'title'         => __('Reply Only Checkbox Label', 'wfr'),
        'description'   => __('The Reply Only Checkbox allows visitors to reply to a review, without giving a rating. Leave this text empty to disable.', 'wfr'),
        'type'          => 'input'            
    ];    

}

/**
 * Remove extra layoutpanel sections for single review settings
 */
if(  function_exists('wf_elementor_theme_has_location') && wf_elementor_theme_has_location('single', 'reviews') ) {
    $layoutPanel = [];
}

/**
 * Archive customizer settings
 */
$layoutPanel['fields']['sections']['reviews_archives']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_archive_title_heading',
    'title'         => __('Additional Reviews Settings', 'wfr'),  
    'type'          => 'heading'   
];

$layoutPanel['fields']['sections']['reviews_archives']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_archive_content_charts',
    'title'         => __('Enable Chart Tab in Archives', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_archives']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_archive_content_compare',
    'title'         => __('Enable Compare Tab in Archives.', 'wfr'),
    'type'          => 'checkbox'    
];


$layoutPanel['fields']['sections']['reviews_archives']['fields'][] = [
    'default'       => 'none',
    'description'   => __('Determines if you load the standard featured image or from the media settings.', 'waterfall'),
    'id'            => 'reviews_archive_content_featured',
    'choices'       => [
        'standard'  => __('Standard Featured Image', 'wfr'),
        'logo'      => __('Image from Logo in Media', 'wfr')
    ],
    'title'         => __('Featured Image Reviews', 'waterfall'),
    'type'          => 'select'
];

$layoutPanel['fields']['sections']['reviews_archives']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_archive_content_price',
    'title'         => __('Show Price in Reviews', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_archives']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_archive_content_price_button',
    'title'         => __('Optional Text for Pricebutton', 'wfr'),
    'description'   => __('If set, shows a button with above text linking to the supplier.', 'waterfall'),
    'type'          => 'input',
    'selector'      => [
        'html'      => true,
        'selector'  => '.archive-content .wfr-price-button span'
    ],
    'transport'     => 'postMessage'        
];

$layoutPanel['fields']['sections']['reviews_archives']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_archive_content_rating',
    'title'         => __('Show Rating in Reviews', 'wfr'),
    'type'          => 'checkbox'    
];

$layoutPanel['fields']['sections']['reviews_archives']['fields'][] = [
    'default'       => '',
    'id'            => 'reviews_archive_content_summary',
    'title'         => __('Show Summary in Reviews', 'wfr'),
    'type'          => 'checkbox'    
];

/**
 * Remove extra layoutpanel sections for single review settings
 */
if( function_exists('wf_elementor_theme_has_location') && wf_elementor_theme_has_location('archive', 'reviews') ) {
    $layoutPanel = [];
}

/**
 * Typography customizer settings
 */
$typographyPanel['fields']['sections'][] = [
    'id'        => 'waterfall_reviews_typography',
    'title'     => __('Reviews', 'wfr'),
    'fields'    => [
        array(
            'default'       => '',
            'selector'      => '.wfr-source-overall .wfr-rating-name',
            'id'            => 'overall_rating',
            'title'         => __('Overall Rating Typography', 'wfr'),
            'description'   => __('Alters the typography of the overal rating element in single reviews.', 'wfr'),
            'type'          => 'typography'
        ),
        array(
            'default'       => '',
            'selector'      => '.wfr-best-price',
            'id'            => 'best_price',
            'title'         => __('Best Price Typography', 'wfr'),
            'description'   => __('Alters the typography of the best price in single reviews.', 'wfr'),
            'type'          => 'typography'
        ),        
        array(
            'default'       => '',
            'selector'      => '.wfr-summary-description',
            'id'            => 'rating_summary',
            'title'         => __('Review Summary Typography', 'wfr'),
            'type'          => 'typography'
        ),
        array(
            'default'       => '',
            'selector'      => '.wfr-summary-details h4',
            'id'            => 'advantage_titles',
            'title'         => __('Advantage and Disadvantage Titles', 'wfr'),
            'description'   => __('Alters the typography of titles above advantages and disadvantages.', 'wfr'),
            'type'          => 'typography'
        ),                 
    ]
];

/**
 * Rating color customizer settings
 */
$colorsPanel['fields']['sections'][] = [
    'id'        => 'waterfall_reviews_colors',
    'title'     => __('Reviews Colors', 'wfr'),
    'fields'    => [
        array(
            'default'       => '',
            'selector'      => '.wfr-rating-display .fa, .wfr-rating-display .wfr-rating-bars span, .wfr-style-numbers .wfr-rating-display, .wfr-style-percentages .wfr-rating-display',
            'id'            => 'rating_color',
            'title'         => __('Rating Element Color', 'wfr'),
            'description'   => __('Alters the color of the rating element, such as stars, circles or bars displaying your rating.', 'wfr'),
            'type'          => 'colorpicker',
            'transport'     => 'postMessage'
        ),
        array(
            'default'       => '',
            'selector'      => ['selector' => '.wfr-summary-details', 'property' => 'background-color'],
            'id'            => 'summary_color',
            'title'         => __('Summary Element Background Color', 'wfr'),
            'description'   => __('Alters the background color of the Review Summary, Advantages and Disadvantages.', 'wfr'),
            'type'          => 'colorpicker',
            'transport'     => 'postMessage'
        ),
        array(
            'default'       => '',
            'selector'      => '.wfr-summary-details ul.advantages .fa',
            'id'            => 'advantages_icon_color',
            'title'         => __('Advantages Icon Color', 'wfr'),
            'description'   => __('Alters the icon color in front of advantages.', 'wfr'),
            'type'          => 'colorpicker',
            'transport'     => 'postMessage'
        ),
        array(
            'default'       => '',
            'selector'      => '.wfr-summary-details ul.disadvantages .fa',
            'id'            => 'disadvantages_icon_color',
            'title'         => __('Disadvantages Icon Color', 'wfr'),
            'description'   => __('Alters the icon color in front of disadvantages.', 'wfr'),
            'type'          => 'colorpicker',
            'transport'     => 'postMessage'
        )             
    ]    
];