<?php

return [
    '__name' => 'admin-post-category',
    '__version' => '0.1.0',
    '__git' => 'git@github.com:getmim/admin-post-category.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/admin-post-category' => ['install','update','remove'],
        'theme/admin/post/category' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'admin' => NULL
            ],
            [
                'lib-formatter' => NULL
            ],
            [
                'lib-form' => NULL
            ],
            [
                'lib-pagination' => NULL
            ],
            [
                'admin-site-meta' => NULL
            ],
            [
                'post-category' => NULL
            ],
            [
                'admin-post' => NULL 
            ]
        ],
        'optional' => [
            [
                'post-category-logo' => NULL
            ]
        ]
    ],
    'autoload' => [
        'classes' => [
            'AdminPostCategory\\Controller' => [
                'type' => 'file',
                'base' => 'modules/admin-post-category/controller'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'admin' => [
            'adminPostCategory' => [
                'path' => [
                    'value' => '/post/category'
                ],
                'method' => 'GET',
                'handler' => 'AdminPostCategory\\Controller\\Category::index'
            ],
            'adminPostCategoryEdit' => [
                'path' => [
                    'value' => '/post/category/(:id)',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET|POST',
                'handler' => 'AdminPostCategory\\Controller\\Category::edit'
            ],
            'adminPostCategoryRemove' => [
                'path' => [
                    'value' => '/post/category/(:id)/remove',
                    'params' => [
                        'id'  => 'number'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'AdminPostCategory\\Controller\\Category::remove'
            ]
        ]
    ],
    'adminUi' => [
        'sidebarMenu' => [
            'items' => [
                'post' => [
                    'label' => 'Post',
                    'icon' => '<i class="fas fa-newspaper"></i>',
                    'priority' => 0,
                    'filterable' => false,
                    'children' => [
                        'category' => [
                            'label' => 'Category',
                            'icon'  => '<i></i>',
                            'route' => ['adminPostCategory'],
                            'perms' => 'manage_post_category'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'admin.post.edit' => [
                'category' => [
                    'label' => 'Category',
                    'type' => 'checkbox-tree',
                    'rules' => []
                ]
            ],
            'admin.post-category.edit' => [
                '@extends' => ['std-site-meta'],
                'name' => [
                    'label' => 'Name',
                    'type' => 'text',
                    'rules' => [
                        'required' => true
                    ]
                ],
                'slug' => [
                    'label' => 'Slug',
                    'type' => 'text',
                    'slugof' => 'name',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE,
                        'unique' => [
                            'model' => 'PostCategory\\Model\\PostCategory',
                            'field' => 'slug',
                            'self' => [
                                'service' => 'req.param.id',
                                'field' => 'id'
                            ]
                        ]
                    ]
                ],
                'parent' => [
                    'label' => 'Parent',
                    'type' => 'radio-tree',
                    'rules' => [
                        'exists' => [
                            'model' => 'PostCategory\\Model\\PostCategory',
                            'field' => 'id'
                        ]
                    ]
                ],
                'content' => [
                    'label' => 'About',
                    'type' => 'summernote',
                    'rules' => []
                ],
                'meta-schema' => [
                    'options' => ['ItemList' => 'ItemList']
                ],
                'logo' => [
                    'label' => 'Logo',
                    'type' => 'image',
                    'form' => 'std-image',
                    'modules' => ['post-category-logo'],
                    'rules' => [
                        'upload' => TRUE
                    ]
                ]
            ],
            'admin.post-category.index' => [
                'q' => [
                    'label' => 'Search',
                    'type' => 'search',
                    'nolabel' => true,
                    'rules' => []
                ]
            ]
        ]
    ]
];
