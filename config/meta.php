<?php
/**
 * Our metabox configurations
 */
defined( 'ABSPATH' ) or die('Go eat veggies!');

$reviewMeta  = [
    'frame'     => 'meta',
    'fields'    => [
        'class'     => 'tabs-left',
        'context'   => 'normal',
        'id'        => 'wfr_review_meta',
        'priority'  => 'high',
        'screen'    => ['reviews'],
        'single'    => true,
        'title'     => __('Review Settings', 'wfr'),
        'type'      => 'post',
        'sections'  => [
            [
                'icon'      => 'build',
                'id'        => 'general',
                'title'     => __('General', 'wfr'),
                'fields'    => [
                    [
                        'id'            => 'reviewed_item',
                        'title'         => __('Product Reviewed', 'wfr'),
                        'description'   => __('Name of the Product that is reviewed.', 'wfr'),
                        'type'          => 'input'                     
                    ],
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
                        'title'         => __('Prices and Vendors', 'wfr'),
                        'description'   => __('Adds one or more prices and possible links to their suppliers.', 'wfr'),
                        'add'           => __('Add New Price', 'wfr'),
                        'remove'        => __('Remove Price', 'wfr'),
                        'type'          => 'repeatable',
                        'fields'        => [                                   
                            [
                                'columns'       => 'fifth',
                                'id'            => 'price',
                                'title'         => __('Price', 'wfr'),
                                'description'   => __('The default price from this supplier.', 'wfr'), 
                                'min'           => 0,
                                'step'          => 0.01,                   
                                'subtype'       => 'number', 
                                'type'          => 'input'                    
                            ],
                            [
                                'columns'       => 'fifth',
                                'id'            => 'url',
                                'title'         => __('Affiliate Url', 'wfr'),
                                'description'   => __('The optional supplier or affiliate url.', 'wfr'),
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
                                'description'   => __('The optional button text. Overwrites the general button text set in the customizer settings.', 'wfr'),
                                'type'          => 'input'                    
                            ],
                            [
                                'columns'       => 'fifth',
                                'id'            => 'best',
                                'title'         => __('Best Price', 'wfr'),
                                'description'   => __('Enable this to make this price the best price from the list, also shown in overviews.', 'wfr'),
                                'options'       => ['enable' => ['label' => __('Enable', 'wfr')]],
                                'type'          => 'checkbox',
                                'style'         => 'switcher',
                                'single'        => true
                            ]                                                                                                                                                                                     
                        ]                                
                    ],
                    [
                        'id'            => 'summary',
                        'title'         => __('Review Summary', 'wfr'),
                        'description'   => __('The summary for the review, rendered at the beginning.', 'wfr'),
                        'type'          => 'textarea',
                        'rows'          => 3                                      
                    ], 
                    [
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
                                'title'         => __('Advantage', 'wfr'),
                                'type'          => 'input',                                         
                            ],
                        ]
                    ], 
                    [
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
                                'title'         => __('Disadvantage', 'wfr'),
                                'type'          => 'input',                                         
                            ],
                        ]
                    ],
                    [
                        'id'            => 'manual_editing',
                        'title'         => __('Manual Templating', 'wfr'),
                        'description'   => __('Removes the default layout element within the content, allowing you to build up the review content using content builders.', 'wfr'),
                        'type'          => 'checkbox',
                        'single'        => true,
                        'style'         => 'switcher switcher-disable',
                        'options'       => [
                            'manual' => array( 'label' => __('Remove Default Review Content Element', 'wfr') )
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
                        'description'   => __('You can specify the url to a video here. It will be shown just near the summary as the first slide if enabled.', 'wfr'),
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
                        'columns'       => 'half',
                        'id'            => 'similar',
                        'title'         => __('Similar Products', 'wfr'),
                        'description'   => __('You can select reviews which are, for example, from the same product range.', 'wfr'),
                        'type'          => 'select', 
                        'multiple'      => true,                    
                        'object'        => 'posts',                     
                        'source'        => 'reviews'                     
                    ],
                    [
                        'columns'       => 'half',
                        'id'            => 'related',
                        'title'         => __('Related Products', 'wfr'),
                        'description'   => __('You can select custom related products here. These will overwrite the general related products.', 'wfr'),
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
 * Fields that are added dynamically through our options
 */
$themeOptions = wf_get_theme_option();

if( isset($themeOptions['rating_calculation']) && $themeOptions['rating_calculation'] == 'automatic' && isset($themeOptions['rating_visitors']) && $themeOptions['rating_visitors'] ) {
    $columns = 'third'; 
} elseif( isset($themeOptions['rating_visitors']) && $themeOptions['rating_visitors'] ) {
    $columns = 'half';
} elseif( isset($themeOptions['rating_calculation']) && $themeOptions['rating_calculation'] == 'automatic' ) {
    $columns = 'half'; 
} else {
    $columns = 'full';
} 

$ratingFields = [
    [
        'columns'       => $columns,
        'id'            => 'rating',
        'title'         => __('Overall Rating', 'wfr'),
        'description'   => isset($themeOptions['rating_calculation']) && $themeOptions['rating_calculation'] == 'visitors' ? __('The overall rating for this review. This will be automatically filled when visitors leave a rating, because Rating Calculation is set to Visitors in the Theme Settings, ', 'wfr') : __('The overall rating for this review. It is calculated automatically if automatic calculation is turned on in the theme settings and can be influenced by visitors if allowed in the Theme Settings.', 'wfr'),
        'min'           => 0,
        'max'           => isset($themeOptions['rating_maximum']) && $themeOptions['rating_maximum'] ? $themeOptions['rating_maximum'] : 5,
        'step'          => 0.1,
        'type'          => 'input',                                        
        'subtype'       => 'number'                                        
    ]  
];

if( isset($themeOptions['rating_visitors']) && $themeOptions['rating_visitors'] ) {
    $ratingFields[] = [
        'columns'       => $columns,
        'id'            => 'visitors_rating',
        'title'         => __('Visitor Rating', 'wfr'),
        'description'   => __('The average rating from visitors. This is calculated when visitors leave ratings at this review.', 'wfr'),
        'min'           => 0,
        'max'           => isset($themeOptions['rating_maximum']) && $themeOptions['rating_maximum'] ? $themeOptions['rating_maximum'] : 5,
        'step'          => 0.1,
        'readonly'      => true,
        'type'          => 'input',                                        
        'subtype'       => 'number'                                        
    ]; 
}

if( isset($themeOptions['rating_calculation']) && $themeOptions['rating_calculation'] == 'automatic' ) {
    $ratingFields[] = [
        'columns'       => $columns,
        'id'            => 'disable_calculation',
        'title'         => __('Disable Automatic Calculation', 'wfr'), 
        'description'   => __('In some cases you want to disable the automatic rating for this review specifically and add a manual rating. This also removes the combined influence of visitors on the rating.', 'wfr'), 
        'type'          => 'checkbox', 
        'single'        => true,          
        'style'         => 'switcher switcher-disable',
        'options'       => ['disable' => ['label' => __('Disable automatic calculation for the Overall Rating.')]]           
    ];            
}

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
         * This adds extra meta fields for our rating
         */
        $ratingFields[] = [
            'id'            => $key . '_heading',
            'title'         => $criteria['name'], 
            'type'          => 'heading'           
        ];
        $ratingFields[] = [
            'columns'       => isset($themeOptions['rating_visitors']) && $themeOptions['rating_visitors'] ? 'half' : 'full',
            'id'            => $key . '_rating',
            'title'         => sprintf( __('Rating for %s', 'wfr'), $criteria['name']),
            'description'   => isset($themeOptions['rating_calculation']) && $themeOptions['rating_calculation'] == 'visitors' ? __('The rating for this criteria. This will be automatically filled when visitors leave a rating, because Rating Calculation is set to Visitors in the Theme Settings, ', 'wfr') : __('The rating for this criteria.', 'wfr'),
            'min'           => 0,
            'max'           => isset($themeOptions['rating_maximum']) && $themeOptions['rating_maximum'] ? $themeOptions['rating_maximum'] : 5,
            'step'          => 0.1,
            'type'          => 'input',                                        
            'subtype'       => 'number'                                        
        ]; 

        if( isset($themeOptions['rating_visitors']) && $themeOptions['rating_visitors'] ) {
            $ratingFields[] = [
                'columns'       => 'half',
                'id'            => 'visitors_rating_' . $key,
                'title'         => sprintf( __('Average Visitor Rating for %s', 'wfr'), $criteria['name']),
                'description'   => __('The average rating from visitors for this Criteria. This is calculated when visitors leave ratings at this review.', 'wfr'),
                'min'           => 0,
                'max'           => isset($themeOptions['rating_maximum']) && $themeOptions['rating_maximum'] ? $themeOptions['rating_maximum'] : 5,
                'step'          => 0.1,
                'readonly'      => true,
                'type'          => 'input',                                        
                'subtype'       => 'number'                                        
            ]; 
        }        

        // Now, add the different fields according to our settings
        if( isset($themeOptions[$key . '_attributes']) && $themeOptions[$key . '_attributes'] ) {
            foreach( $themeOptions[$key . '_attributes'] as $attribute ) {

                // Name and type should be defined
                if( ! $attribute['name'] ) {
                    continue;
                }

                if( ! $attribute['type'] ) {
                    continue;
                } 

                $choices = [];
                $id      = sanitize_key($attribute['name']);

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
                        'id'        => $id . '_' . $key . '_attribute',
                        'title'     => $attribute['name'],
                        'type'      => 'repeatable',
                        'fields'    => [
                            'name' => [
                                'title'     => __('Plan Name', 'wfr'),
                                'id'        => 'name',
                                'columns'   => 'half',
                                'type'      => 'input',
                            ],                            
                            'value' => [
                                'title'     => __('Plan Value', 'wfr'),
                                'id'        => 'value',
                                'columns'   => 'half',
                                'type'      => $attribute['type'] == 'number' ? 'input' : $attribute['type'],
                                'subtype'   => $attribute['type'] == 'number' ? 'number' : NULL,
                                'rows'      => 3,
                                'options'   => $choices
                            ]
                        ]

                    ];
                } else {
                    $ratingFields[] = [
                        'columns'   => 'fourth',
                        'id'        => $id . '_' . $key . '_attribute',
                        'title'     => $attribute['name'],
                        'type'      => $attribute['type'] == 'number' ? 'input' : $attribute['type'],
                        'subtype'   => $attribute['type'] == 'number' ? 'number' : NULL,
                        'rows'      => 3,
                        'options'   => $choices
                    ];
                }


            }
        } 
        
    }


    /**
     * Add our additional rating meta fields
     */
    $reviewMeta['fields']['sections'][] = [
        'icon'          => 'grade',
        'id'            => 'rating',
        'title'         => __('Rating', 'wfr'),
        'fields'        => $ratingFields
    ];    

}

if( isset($themeOptions['properties']) && $themeOptions['properties'] ) {

    $propertyFields = [];

    foreach( $themeOptions['properties'] as $property ) {
        
        // Name and type should be defined
        if( ! $property['name'] ) {
            continue;
        }

        if( ! $property['type'] ) {
            continue;
        }        

        $choices = [];
        $id      = sanitize_key($property['name']);

        if( in_array($property['type'], ['checkbox', 'select']) && $property['values'] ) {
            $values = array_filter( explode(',', $property['values']) );
            foreach( $values as $key => $choice ) {
                $key           = sanitize_key($choice);
                $choices[$key] = $property['type'] == 'checkbox' ? ['label' => trim( sanitize_text_field($choice) )] : trim( sanitize_text_field($choice) );
            }
        }

        if( isset($property['repeat']) && $property['repeat'] ) {
            $propertyFields[] = [
                'id'        => $id . '_property',
                'title'     => $property['name'],
                'type'      => 'repeatable',
                'fields'    => [
                    'name'  => [
                        'title'     => __('Plan Name', 'wfr'),
                        'id'        => 'name',
                        'columns'   => 'half',
                        'type'      => 'input',
                    ],                            
                    'value' => [
                        'title'     => __('Plan Value', 'wfr'),
                        'id'        => 'value',
                        'columns'   => 'half',
                        'type'      => $property['type'] == 'number' ? 'input' : $property['type'],
                        'subtype'   => $property['type'] == 'number' ? 'number' : NULL,
                        'rows'      => 3,
                        'options'   => $choices
                    ]
                ]

            ];
        } else {
            $propertyFields[] = [
                'columns'   => 'fourth',
                'id'        => $id . '_property',
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
    $reviewMeta['fields']['sections'][] = [
        'icon'          => 'fingerprint',
        'id'            => 'properties',
        'title'         => __('Properties', 'wfr'),
        'fields'        => $propertyFields
    ];

}
