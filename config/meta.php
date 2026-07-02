<?php
/**
 * Our metabox configurations
 */
defined( 'ABSPATH' ) or die('Go eat veggies!');

/**
 * Set-up our plans for dynamic display
 */
$plan_options = [];

// Get's the plans
if( isset($_GET['post']) && is_numeric($_GET['post']) ) {

    $plans = get_post_meta( intval($_GET['post']), 'plans', true );

    if( is_array($plans) ) {

        $plans = array_filter($plans);

        foreach( $plans as $plan ) {
            $key = sanitize_key($plan['name']);
            $plan_options[$key] = $plan['name'];
        }

    }

}

/**
 * Flexible settings depending on some general options
 */
$theme_options = wf_get_data('options', ['rating_calculation', 'rating_visitors', 'rating_calculation', 'rating_maximum', 'rating_criteria', 'properties']);

// Dynamic columns
if( $theme_options['rating_calculation'] === 'automatic' && $theme_options['rating_visitors'] ) {
    $columns = 'third'; 
} elseif( $theme_options['rating_visitors'] ) {
    $columns = 'half';
} elseif( $theme_options['rating_calculation'] == 'automatic' ) {
    $columns = 'half'; 
} else {
    $columns = 'full';
} 

// Flexible criteria properties
if( $theme_options['rating_criteria'] ) {
    foreach( $theme_options['rating_criteria'] as $key => $criteria ) {
        $key                = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);
        $criteria_keys[]    = $key . '_attributes';
    }

    $criteria_attributes = wf_get_data('options', $criteria_keys);
}

/**
 * Our dynamic review meta
 */
$review_meta  = [
    'frame'     => 'meta',
    'fields'    => [
        'class'     => 'tabs-left wfr-review-meta wfr-rating-calculation-' . $theme_options['rating_calculation'],
        'context'   => 'normal',
        'id'        => 'wfr_review_meta',
        'priority'  => 'high',
        'screen'    => ['reviews'],
        'single'    => true,
        'title'     => __('Review Settings', 'wfr'),
        'type'      => 'post',
        'sections'  => [
            'general' => [
                'icon'      => 'build',
                'id'        => 'general',
                'title'     => __('General', 'wfr'),
                'fields'    => [
                    'rating' => [
                        'columns'       => $columns,
                        'id'            => 'rating',
                        'title'         => __('Overall Rating', 'wfr'),
                        'description'   => $theme_options['rating_calculation'] == 'visitors' 
                            ? __('The overall rating. Automatically calculated when visitors leave a rating as defined in the Theme Settings, ', 'wfr') 
                            : __('The overall rating. Automatically calculated if automatic calculation is turned on in the Theme Settings.', 'wfr'),
                        'min'           => 0,
                        'max'           => $theme_options['rating_maximum'] ? $theme_options['rating_maximum'] : 5,
                        'step'          => 0.1,
                        'type'          => 'input',                                        
                        'subtype'       => 'number'                                        
                    ],  
                    'visitors_rating' => [
                            'columns'       => $columns,
                            'id'            => 'visitors_rating',
                            'title'         => __('Visitor Rating', 'wfr'),
                            'description'   => __('The average rating from visitors. This is calculated when visitors leave ratings at this review.', 'wfr'),
                            'min'           => 0,
                            'max'           => $theme_options['rating_maximum'] ? $theme_options['rating_maximum'] : 5,
                            'step'          => 0.1,
                            'readonly'      => true,
                            'type'          => 'input',                                        
                            'subtype'       => 'number'                                        
                    ], 
                    'disable_calculation' => [
                            'columns'       => $columns,
                            'id'            => 'disable_calculation',
                            'title'         => __('Overall Rating Calculation', 'wfr'), 
                            'description'   => __('In some cases you want to disable the automatic rating for this review specifically and add a manual rating.', 'wfr'), 
                            'type'          => 'checkbox', 
                            'single'        => true,          
                            'style'         => 'switcher switcher-disable',
                            'options'       => [
                                'disable' => ['label' => __('Disable automatic calculation.')]
                            ]           
                    ],                                                                  
                    'summary' => [
                        'columns'       => 'half',
                        'id'            => 'summary',
                        'title'         => __('Review Summary', 'wfr'),
                        'description'   => __('The summary for the review, rendered at the beginning.', 'wfr'),
                        'type'          => 'textarea',
                        'rows'          => 3                                      
                    ], 
                    'reviewed_item' => [
                        'columns'       => 'half',
                        'id'            => 'reviewed_item',
                        'title'         => __('Product Reviewed', 'wfr'),
                        'description'   => __('Name of the Product that is reviewed.', 'wfr'),
                        'type'          => 'input'                     
                    ],               
                    'advantages' => [
                        'columns'       => 'half',
                        'id'            => 'advantages',
                        'title'         => __('Advantages', 'wfr'),
                        'description'   => __('Adds advantages for this review, displayed under the summary.', 'wfr'),
                        'add'           => __('Add New Advantage', 'wfr'), 
                        'remove'        => __('Remove Advantage', 'wfr'),
                        'type'          => 'repeatable',
                        'fields'        => [
                            [
                                'id'            => 'name',
                                'placeholder'   => __('Enter an advantage', 'wfr'),
                                'type'          => 'input',                                         
                            ]
                        ]
                    ], 
                    'disadvantages' => [
                        'columns'       => 'half',
                        'id'            => 'disadvantages',
                        'title'         => __('Disadvantages', 'wfr'),
                        'description'   => __('Adds disadvantages for this review, displayed under the summary.', 'wfr'),
                        'add'           => __('Add New Disadvantage', 'wfr'), 
                        'remove'        => __('Remove Disadvantage', 'wfr'),
                        'type'          => 'repeatable',
                        'fields'        => [
                            [
                                'id'            => 'name',
                                'placeholder'   => __('Enter a disadvantage', 'wfr'),
                                'type'          => 'input',                                         
                            ]
                        ]
                    ],
                    'manual_editing' => [
                        'columns'       => 'half',
                        'id'            => 'manual_editing',
                        'title'         => __('Manual Templating', 'wfr'),
                        'description'   => __('Removes all default review elements within the content, allowing you to build up the review content using content builders.', 'wfr'),
                        'type'          => 'checkbox',
                        'single'        => true,
                        'style'         => 'switcher switcher-enable',
                        'options'       => [
                            'manual' => array( 'label' => __('Remove Default Review Content Elements', 'wfr') )
                        ]  
                    ],
                    'hide_properties' => [
                        'columns'       => 'half',
                        'id'            => 'wfr_hide_properties',
                        'title'         => __('Hide Properties and Attributes', 'wfr'),
                        'description'   => __('Hides the table with all general and criteria related properties.', 'wfr'),
                        'type'          => 'checkbox',
                        'single'        => true,
                        'style'         => 'switcher switcher-enable',
                        'options'       => [
                            'manual' => array( 'label' => __('Hide Properties and Attributes Table', 'wfr') )
                        ]  
                    ]                                               
                ]
            ],
            [
                'icon'      => 'attach_money',
                'id'        => 'prices',
                'title'     => __('Prices', 'wfr'),
                'fields'    => [  
                    [
                        'columns'       => 'fourth',
                        'id'            => 'price_prefix',
                        'title'         => __('Price Prefix', 'wfr'),
                        'description'   => __('A prefix displayed in front of the price, such as From.', 'wfr'),
                        'type'          => 'input'                     
                    ],                      
                    [
                        'columns'       => 'fourth',
                        'id'            => 'price_currency',
                        'title'         => __('Price Currency', 'wfr'),
                        'description'   => __('The currency for the price, overwrites the general currency in the themesettings.', 'wfr'),
                        'type'          => 'input'                                                 
                    ],                                                                                         
                    [
                        'columns'       => 'fourth',
                        'id'            => 'price_unit',
                        'title'         => __('Price Unit', 'wfr'),
                        'description'   => __('The optional unit for prices, such as monthly or yearly.', 'wfr'),
                        'type'          => 'input'                                                 
                    ], 
                    [
                        'columns'       => 'fourth',
                        'id'            => 'price',
                        'title'         => __('Best Price', 'wfr'),
                        'description'   => __('The Best Price, shown in reviews and archives. Automatically set from the prices in the list hereafter.', 'wfr'),
                        'type'          => 'input',
                        'min'           => 0,
                        'step'          => 0.01,
                        'readonly'      => true,
                        'subtype'       => 'number',                                                                                   
                    ],                                                                                                                                                       
                    [
                        'id'            => 'prices',
                        'title'         => __('Review Item Prices', 'wfr'),
                        'description'   => __('Add one or more prices and possible affiliated links to their suppliers. These prices are shown in the review.', 'wfr'),
                        'type'          => 'repeatable',
                        'fields'        => [                                   
                            [
                                'columns'       => 'fifth',
                                'id'            => 'price',
                                'title'         => __('Price', 'wfr'),
                                'description'   => __('The default price.', 'wfr'), 
                                'min'           => 0,
                                'step'          => 0.01,                   
                                'subtype'       => 'number', 
                                'type'          => 'input'                    
                            ],
                            [
                                'columns'       => 'fifth',
                                'id'            => 'url',
                                'title'         => __('Affiliate Url', 'wfr'),
                                'description'   => __('The optional external url.', 'wfr'),
                                'type'          => 'input',                     
                                'subtype'       => 'url'                     
                            ],
                            [
                                'columns'       => 'fifth',
                                'id'            => 'name',
                                'title'         => __('Name', 'wfr'),
                                'description'   => __('The optional supplier name.', 'wfr'),
                                'type'          => 'input'                    
                            ],
                            [
                                'columns'       => 'fifth',
                                'id'            => 'button',
                                'title'         => __('Button', 'wfr'),
                                'description'   => __('The optional button text.', 'wfr'),
                                'type'          => 'input'                    
                            ],
                            [
                                'columns'       => 'fifth',
                                'id'            => 'best',
                                'title'         => __('Best Price', 'wfr'),
                                'options'       => [
                                    'enable' => ['label' => __('Make best price', 'wfr')]
                                ],
                                'type'          => 'checkbox',
                                'style'         => 'switcher',
                                'single'        => true
                            ]                                                                                                                                                                                     
                        ]                                
                    ],
                    [
                        'class'         => 'wfr-plans-meta',
                        'id'            => 'plans',
                        'title'         => __('Item Plans', 'wfr'),
                        'description'   => __('Adds pricing plans for this item. These plans can be used to display dynamic tables and link repeatable attributes and properties.', 'wfr'),
                        'type'          => 'repeatable',
                        'fields'        => [ 
                            [
                                'columns'       => 'fourth',
                                'id'            => 'name',
                                'title'         => __('Unique Name', 'wfr'),
                                'type'          => 'input'                    
                            ],                                                              
                            [
                                'columns'       => 'fourth',
                                'id'            => 'price',
                                'title'         => __('Price', 'wfr'),
                                'min'           => 0,
                                'step'          => 0.01,                   
                                'subtype'       => 'number', 
                                'type'          => 'input'                    
                            ],
                            [
                                'columns'       => 'half',
                                'id'            => 'description',
                                'title'         => __('Optional Description', 'wfr'),
                                'placeholder'   => __('An optional description for the plan.', 'wfr'),
                                'type'          => 'textarea',
                                'rows'          => 1                    
                            ]
                        ]
                    ]                
                ] 
            ],         
            [
                'icon'      => 'camera_enhance',
                'id'        => 'media',
                'title'     => __('Media', 'wfr'),
                'fields'    => [
                    [
                        'columns'       => 'half',
                        'id'            => 'media',
                        'title'         => __('Review Image Media', 'wfr'),
                        'description'   => __('You can select additional images here. It will be shown under the title in a slider.', 'wfr'),
                        'multiple'      => true,
                        'type'          => 'media', 
                        'subtype'       => 'image'                  
                    ],
                    [
                        'columns'       => 'half',
                        'id'            => 'logo',
                        'title'         => __('Review Logo', 'wfr'),
                        'description'   => __('You can select a logo image here. This can be (optionally) load in review overviews.', 'wfr'),
                        'multiple'      => false,
                        'type'          => 'media', 
                        'subtype'       => 'image'                  
                    ],                              
                    [
                        'id'            => 'video',
                        'title'         => __('Review Video URL', 'wfr'),
                        'description'   => __('You can specify the url to a video here. It will be shown if enabled within the customizer settings.', 'wfr'),
                        'multiple'      => false,
                        'type'          => 'input', 
                        'subtype'       => 'url'                   
                    ]
                                                                            
                ]                        
            ],                    
            [
                'icon'      => 'device_hub',
                'id'        => 'relations',
                'title'     => __('Relations', 'wfr'),
                'fields'    => [
                    [
                        'id'            => 'similar',
                        'title'         => __('Similar Products', 'wfr'),
                        'description'   => __('You can select reviews which are, for example, from the same product range.', 'wfr'),
                        'type'          => 'select', 
                        'multiple'      => true,                    
                        'object'        => 'posts',                     
                        'source'        => 'reviews'                     
                    ],
                    [
                        'id'            => 'related',
                        'title'         => __('Related Products', 'wfr'),
                        'description'   => __('Custom related products here. These will overwrite the general related products.', 'wfr'),
                        'type'          => 'select', 
                        'multiple'      => true,     
                        'object'        => 'posts',               
                        'source'        => 'reviews'                     
                    ],                            
                ]                        
            ]
        ]   
    ]
];

/**
 * These fields are removed depending on our settings
 */
if( ! $theme_options['rating_visitors'] ) {
    unset($review_meta['fields']['sections']['general']['fields']['visitors_rating']);
}

if( ! $theme_options['rating_calculation'] === 'automatic' ) {
    unset($review_meta['fields']['sections']['general']['fields']['disable_calculation']);   
}

/**
 * Based upon what top level review criteria are added, we add additional settings. Hence, we can add dynamic fields
 */
if( $theme_options['rating_criteria'] ) {

    // Add our fields for adding
    foreach( $theme_options['rating_criteria'] as $key => $criteria ) {

        // Bail out if there is no name
        if( ! isset($criteria['name']) || ! $criteria['name'] ) {
            continue;
        }

        $key            = isset($criteria['key']) && $criteria['key'] ? sanitize_key($criteria['key']) : sanitize_key($criteria['name']);
        $ratingFields   = [];

        /**
         * This adds extra meta fields for our rating
         */
        $ratingFields[] = [
            'class'         => 'wfr-rating-criteria',
            'columns'       => $theme_options['rating_visitors'] ? 'half' : 'full',
            'id'            => $key . '_rating',
            'title'         => sprintf( __('Rating for %s', 'wfr'), $criteria['name']),
            'description'   => $theme_options['rating_calculation'] === 'visitors' ? __('The rating for this criteria. This will be automatically filled when visitors leave a rating, because Rating Calculation is set to Visitors in the Theme Settings, ', 'wfr') : __('The rating for this criteria.', 'wfr'),
            'min'           => 0,
            'max'           => $theme_options['rating_maximum'] ? $theme_options['rating_maximum'] : 5,
            'step'          => 0.1,
            'type'          => 'input',                                        
            'subtype'       => 'number'                                        
        ]; 

        if( $theme_options['rating_visitors'] ) {
            $ratingFields[] = [
                'columns'       => 'half',
                'id'            => 'visitors_rating_' . $key,
                'title'         => sprintf( __('Average Visitor Rating for %s', 'wfr'), $criteria['name']),
                'description'   => __('The average rating from visitors for this Criteria. This is calculated when visitors leave ratings at this review.', 'wfr'),
                'min'           => 0,
                'max'           => $theme_options['rating_maximum'] ? $theme_options['rating_maximum'] : 5,
                'step'          => 0.1,
                'readonly'      => true,
                'type'          => 'input',                                        
                'subtype'       => 'number'                                        
            ]; 
        }        

        // Now, add the different fields according to our settings
        if( $criteria_attributes[$key . '_attributes'] ) {

            foreach( $criteria_attributes[$key . '_attributes'] as $attribute ) {

                // Name and type should be defined
                if( ! $attribute['name'] ) {
                    continue;
                }

                if( ! $attribute['type'] ) {
                    continue;
                } 

                $choices = [];
                $id      = isset($attribute['key']) && $attribute['key'] ? sanitize_key($attribute['key']) : sanitize_key($attribute['name']) . '_' . $key . '_attribute';

                if( in_array($attribute['type'], ['checkbox', 'select']) && $attribute['values'] ) {
                    $values = array_filter( explode(',', $attribute['values']) );
                    
                    foreach( $values as $choice ) {

                        $identifier     = sanitize_key($choice);

                        if( strpos($choice, ':') ) {
                            $divide     = explode(':', $choice);
                            $identifier = sanitize_text_field($divide[0]);
                            $choice     = $divide[1];
                        }

                        $choices[$identifier] = $attribute['type'] == 'checkbox' ? ['label' => trim( sanitize_text_field($choice) )] : trim( sanitize_text_field($choice) );

                    }
                }

                if( isset($attribute['repeat']) && $attribute['repeat'] ) {
                    $ratingFields[] = [
                        'class'     => 'wfr-meta-linked-plans',
                        'id'        => $id,
                        'title'     => $attribute['name'],
                        'type'      => 'repeatable',
                        'fields'    => [
                            'value' => [
                                'id'            => 'value',
                                'columns'       => 'fourth',
                                'type'          => $attribute['type'] == 'number' ? 'input' : $attribute['type'],
                                'subtype'       => $attribute['type'] == 'number' ? 'number' : NULL,
                                'placeholder'   => $attribute['type'] == 'select' ? __('Select', 'wfr') : __('Enter value', 'wfr'),
                                'mode'          => 'plain',
                                'options'       => $choices
                            ],                              
                            'plan' => [
                                'id'            => 'plan',
                                'columns'       => 'fourth',
                                'placeholder'   => __('Select associated plan', 'wfr'),
                                'mode'          => 'plain',
                                'type'          => 'select',
                                'options'       => $plan_options
                            ], 
                            'name' => [
                                'placeholder'   => __('Optional custom plan name', 'wfr'),
                                'id'            => 'name',
                                'columns'       => 'fourth',
                                'type'          => 'input'
                            ],
                            'price' => [
                                'style'         => 'medium-text',
                                'placeholder'   => __('Optional custom price', 'wfr'),
                                'id'            => 'price',
                                'columns'       => 'fourth',
                                'type'          => 'input',
                                'subtype'       => 'number',
                                'min'           => 0,
                                'step'          => 0.01
                            ]                                                                                                                                        
                        ]

                    ];
                } else {
                    $ratingFields[] = [
                        'columns'   => 'fourth',
                        'id'        => $id,
                        'title'     => $attribute['name'],
                        'type'      => $attribute['type'] == 'number' ? 'input' : $attribute['type'],
                        'subtype'   => $attribute['type'] == 'number' ? 'number' : NULL,
                        'rows'      => 3,
                        'options'   => $choices
                    ];
                }


            }

        }
        
        /**
         * Add our additional rating meta fields
         */
        $review_meta['fields']['sections'][$key] = [
            'icon'          => 'grade',
            'id'            => $key,
            'title'         => esc_html($criteria['name']),
            'fields'        => $ratingFields
        ];    
             
    }

}

/**
 * Dynamic property fields for a review
 */
if( $theme_options['properties'] ) {

    $propertyFields = [];

    foreach( $theme_options['properties'] as $property ) {
        
        // Name and type should be defined
        if( ! $property['name'] ) {
            continue;
        }

        if( ! $property['type'] ) {
            continue;
        }        

        $choices = [];
        $id      = isset($property['key']) && $property['key'] ? sanitize_key($property['key']) : sanitize_key($property['name']) . '_property';

        if( in_array($property['type'], ['checkbox', 'select']) && $property['values'] ) {
                      
            $values = array_filter( explode(',', $property['values']) );

                    
            foreach( $values as $key => $choice ) {
                $key           = sanitize_key($choice);
                $choices[$key] = $property['type'] == 'checkbox' ? ['label' => trim( sanitize_text_field($choice) )] : trim( sanitize_text_field($choice) );
            }
        }

        if( isset($property['repeat']) && $property['repeat'] ) {
            $propertyFields[] = [
                'class'     => 'wfr-meta-linked-plans',
                'id'        => $id,
                'title'     => $property['name'],
                'type'      => 'repeatable',
                'fields'    => [
                    'value' => [
                        'id'        => 'value',
                        'columns'   => 'fourth',
                        'type'      => $property['type'] == 'number' ? 'input' : $property['type'],
                        'subtype'   => $property['type'] == 'number' ? 'number' : NULL,
                        'mode'      => 'plain',
                        'rows'      => 3,
                        'placeholder'   => $property['type'] == 'select' ? __('Select', 'wfr') : __('Enter', 'wfr'),
                        'options'   => $choices
                    ],                    
                    'plan' => [
                        'id'            => 'plan',
                        'columns'       => 'fourth',
                        'placeholder'   => __('Select associated plan', 'wfr'),
                        'type'          => 'select',
                        'mode'          => 'plain',
                        'options'       => $plan_options
                    ],                             
                    'name' => [
                        'placeholder'   => __('Optional custom plan name', 'wfr'),
                        'id'            => 'name',
                        'columns'       => 'fourth',
                        'type'          => 'input'
                    ],
                    'price' => [
                        'style'         => 'medium-text',
                        'placeholder'   => __('Optional custom price', 'wfr'),                  
                        'id'            => 'price',
                        'columns'       => 'fourth',
                        'type'          => 'input',
                        'subtype'       => 'number',
                        'min'           => 0,
                        'step'          => 0.01
                    ]                                      
                ]

            ];
        } else {
            $propertyFields[] = [
                'columns'   => 'fourth',
                'id'        => $id,
                'title'     => $property['name'],
                'type'      => $property['type'] == 'number' ? 'input' : $property['type'],
                'subtype'   => $property['type'] == 'number' ? 'number' : NULL,
                'rows'      => 3,
                'options'   => $choices
            ];
        }

    }

    /**
     * Adds our additional properties
     */
    $review_meta['fields']['sections'][] = [
        'icon'          => 'fingerprint',
        'id'            => 'properties',
        'title'         => __('Properties', 'wfr'),
        'fields'        => $propertyFields
    ];

}
