<?php
/**
 * Our options page additional configurations
 */
defined( 'ABSPATH' ) or die('Go eat veggies!');

$options = [
    'frame'     => 'options',
    'fields'    => [              
        'sections'      => [
            'review_general' => [
                'icon'      => 'star',
                'id'        => 'review_general_section',
                'title'     => __('Review Settings', 'wfr'),
                'fields'    => [
                    [
                        'columns'       => 'fourth',
                        'id'            => 'rating_style',
                        'title'         => __('Rating Style', 'wfr'),
                        'description'   => __('Determine the dominant style for ratings. This determines how your scores are displayed on a review.', 'wfr'),
                        'type'          => 'select',   
                        'options'       => [
                            'stars'         => __('Stars', 'wfr'),
                            'bars'          => __('Bars', 'wfr'),
                            'circles'       => __('Circles', 'wfr'),
                            'numbers'       => __('Numbers', 'wfr'),
                            'smileys'       => __('Smileys', 'wfr'),
                            'percentages'   => __('Percentages', 'wfr')
                        ]                         
                    ],
                    [
                        'columns'       => 'fourth',
                        'id'            => 'review_scheme',
                        'title'         => __('Rating Scheme', 'wfr'),
                        'description'   => __('Determine the microscheme that is used for reviews. This determines how Google sees the type of your content.', 'wfr'),
                        'type'          => 'select',   
                        'options'       => [
                            'CreativeWork'    => __('Creative Work', 'wfr'),
                            'BlogPosting'     => __('Blog Post', 'wfr'),
                            'Article'         => __('Article', 'wfr'),                           
                            'Product'         => __('Product', 'wfr'),
                            'Book'            => __('Book', 'wfr'),
                            'Movie'           => __('Movie', 'wfr'),
                            'Game'            => __('Game', 'wfr')
                        ]   
                    ], 
                    [
                        'columns'       => 'fourth',
                        'default'       => 5,
                        'id'            => 'rating_maximum',
                        'title'         => __('Maximum Rating Value', 'wfr'),
                        'description'   => __('What is the maximum rating value you can enter? In most cases, this will be either 5 or 10.', 'wfr'),
                        'type'          => 'input',   
                        'subtype'       => 'number',   
                    ],
                    [
                        'columns'       => 'fourth',
                        'default'       => '$',
                        'id'            => 'review_currency',
                        'title'         => __('Review Currency', 'wfr'),
                        'description'   => __('The currency for prices in reviews.', 'wfr'),
                        'type'          => 'input',     
                    ],                                                                                                               
                    [
                        'id'            => 'rating_calculation_heading',
                        'title'         => __('Rating Calculation Settings', 'wfr'),
                        'type'          => 'heading',  
                    ],
                    [
                        'columns'       => 'fourth',
                        'id'            => 'rating_calculation',
                        'title'         => __('Rating Calculation', 'wfr'),
                        'description'   => __('The overall rating can be calculated manually, by visitors reviews only, or automatically by comparing the scores of criteria.', 'wfr'),
                        'type'          => 'select',   
                        'options'       => [
                            'manual'        => __('Manual', 'wfr'),
                            'automatic'     => __('Automatic', 'wfr'),
                            'visitors'      => __('Visitors', 'wfr')
                        ]   
                    ],
                    [
                        'columns'       => 'fourth',
                        'id'            => 'rating_visitors',
                        'title'         => __('Enable Visitor Ratings', 'wfr'),
                        'description'   => __('Allows visitors to rate through commenting on a review. Comments should be allowed for reviews.', 'wfr'),
                        'type'          => 'checkbox',
                        'single'        => true,
                        'style'         => 'switcher',   
                        'options'       => [
                            'enable'    => ['label' => __('Allow visitors to rate', 'wfr')],
                        ]   
                    ],                                                                                     
                    [
                        'columns'       => 'fourth',
                        'id'            => 'rating_visitors_influence',
                        'title'         => __('Combine Visitor and Review Ratings', 'wfr'),
                        'description'   => __('Allows visitors to influence the review rating. Disabled if Rating Calculation is set to Visitors.', 'wfr'),
                        'type'          => 'select', 
                        'options'       => [
                            'disabled'  => __('Disable visitor influence', 'wfr'),
                            'overall'   => __('Influence overall rating', 'wfr'),
                            'criteria'  => __('Act as a criteria', 'wfr'),
                        ]   
                    ],
                    [
                        'columns'       => 'fourth',
                        'default'       => 1,
                        'id'            => 'rating_weight_ratio',
                        'title'         => __('Visitors Rating Weight', 'wfr'),
                        'description'   => __('The influence visitors have on the overall rating. A value of 1 is equal influence with that of the overal rating of review, a value of 0.1 ten times less.', 'wfr'),
                        'type'          => 'input', 
                        'subtype'       => 'number',
                        'max'           => 10,
                        'min'           => 0.1,
                        'step'          => 0.1                                    
                    ],                                                                                                                                             
                ]    
            ],
            'criteria' => [
                'icon'          => 'linear_scale',
                'id'            => 'rating_criteria_section',
                'title'         => __('Review Criteria', 'wfr'),
                'description'   => __('You can add a list of extra review criteria here, which each can be scored. Upon saving, extra fields will be added in which you can add attributes for each criteria.', 'wfr'),
                'fields'        => [
                    [
                        'id'            => 'rating_criteria',
                        'title'         => __('Add Rating Criteria', 'wfr'),
                        'description'   => __('Rating criteria will appear while editing a single review and allow you to give ratings on several criteria.', 'wfr'),
                        'type'          => 'repeatable',
                        'add'           => __('Add New Criteria', 'wfr'),
                        'remove'        => __('Remove Criteria', 'wfr'),                               
                        'fields'        => [
                            [
                                'columns'       => 'fourth',
                                'id'            => 'name',
                                'title'         => __('Criteria Name', 'wfr'),
                                'type'          => 'input'                                        
                            ],
                            [
                                'columns'       => 'fourth',
                                'id'            => 'weight',
                                'title'         => __('Criteria Weight', 'wfr'),
                                'description'   => __('The influence of this criteria on overall ratings. Applies when rating calculation is set to automatic.', 'wfr'),
                                'type'          => 'input',
                                'subtype'       => 'number',
                                'min'           => 1,
                                'step'          => 1,
                                'max'           => 10                                        
                            ],                                     
                            [
                                'columns'       => 'half',
                                'id'            => 'description',
                                'title'         => __('Criteria Description', 'wfr'),
                                'type'          => 'textarea',
                                'rows'          => 3                                       
                            ],
                                                                
                        ]                                
                    ]
                ]
            ],                     
            'properties' => [
                'icon'          => 'fingerprint',
                'id'            => 'standard_properties_section',
                'title'         => __('Review Properties', 'wfr'),
                'description'   => __('You can add a list of standard attributes here. These will be added as custom fields to your reviews.', 'wfr'),
                'fields'    => [
                    [
                        'id'            => 'properties',
                        'title'         => __('Properties', 'wfr'),
                        'type'          => 'repeatable',
                        'add'           => __('Add New Property', 'wfr'),
                        'remove'        => __('Remove Property', 'wfr'),
                        'fields'        => [
                            [
                                'columns'       => 'third',
                                'id'            => 'name',
                                'title'         => __('Property Name', 'wfr'),
                                'type'          => 'input'                                        
                            ],
                            [
                                'columns'       => 'third',
                                'id'            => 'type',
                                'title'         => __('Property Field Type', 'wfr'),
                                'type'          => 'select',
                                'options'       => [
                                    'input'     => __('Text Input', 'wfr'),
                                    'number'    => __('Number Input', 'wfr'),
                                    'textarea'  => __('Textarea', 'wfr'),
                                    'select'    => __('Select Dropdown', 'wfr'),
                                    'checkbox'  => __('Checkboxes', 'wfr'),
                                ]                                        
                            ],
                            [
                                'columns'       => 'third',
                                'id'            => 'values',
                                'title'         => __('Property Field Values', 'wfr'),
                                'description'   => __('Add values if your property field type is checkbox or select. Seperate them by comma.', 'wfr'),
                                'type'          => 'textarea',
                                'rows'          => 1                                       
                            ],                                                                                                                   
                        ]                                
                    ]                                
                ]
            ]                                       
        ]                    
    ]
];

/**
 * We initialize our initial ratingsfields here, and add it later once we have completed adding all fields
 */
$themeOptions = wf_get_theme_option(); 

/**
 * Based upon what top level review criteria are added, we add additional settings. Hence, we can add dynamic fields
 */
if( isset($themeOptions['rating_criteria']) && $themeOptions['rating_criteria'] ) {

    // Add our fields for adding
    foreach( $themeOptions['rating_criteria'] as $key => $criteria ) {

        // Bail out if there is no name
        if( ! isset($criteria['name']) || ! $criteria['name'] ) {
            continue;
        }

        $key = sanitize_key($criteria['name']);

        /**
         * Add extra option screens.
         */
        $options['fields']['sections']['criteria']['fields'][] = [           
            'id'            => $key . '_attributes',
            'title'         => sprintf( __('Custom Attributes for %s', 'wfr'), $criteria['name']),
            'description'   => sprintf( __('Here you can add custom attributes for the %s criteria. These are added as custom fields to reviews.', 'wfr'), $criteria['name']),
            'type'          => 'repeatable',
            'add'           => __('Add New Attribute', 'wfr'),
            'remove'        => __('Remove Attribute', 'wfr'),
            'fields'        => [              
                [
                    'columns'       => 'fourth',
                    'id'            => 'name',
                    'title'         => __('Attribute Name', 'wfr'),
                    'description'   => __('The name for this attribute.', 'wfr'),
                    'type'          => 'input'                                        
                ],
                [
                    'class'         => 'medium-text',
                    'columns'       => 'fourth',
                    'id'            => 'key',
                    'title'         => __('Attribute ID', 'wfr'),
                    'description'   => __('Optional ID. By default the sanitized name is used. Use lowercase characters.', 'wfr'),
                    'type'          => 'input'                                        
                ],                  
                [
                    'columns'       => 'fourth',
                    'id'            => 'type',
                    'title'         => __('Attribute Field Type', 'wfr'),
                    'description'   => __('The type of field this attribute resembles.', 'wfr'),
                    'type'          => 'select',
                    'options'       => [
                        'number'    => __('Number Input', 'wfr'),
                        'select'    => __('Select Dropdown', 'wfr'),
                        'checkbox'  => __('Checkboxes', 'wfr'),
                    ]                                        
                ],                     
                [
                    'columns'       => 'fourth',
                    'id'            => 'values',
                    'title'         => __('Attribute Field Values', 'wfr'),
                    'description'   => __('Add values if you have a select or checkbox attribute field type. Seperate them by comma.', 'wfr'),
                    'type'          => 'textarea',
                    'rows'          => 1                                        
                ]                                                                                                                                
            ]
        ];

    }

}