<?php

namespace App\Form;

use App\Form\Contract\InputFormInterface;
use App\Models\Ticket;
use Illuminate\Support\Collection;

/**
 * Created by PhpStorm.
 * User: MD Eyasin
 * Date: 8/24/2019
 * Time: 12:23 PM
 */
class ConversationSearchForm extends Ticket implements InputFormInterface
{

    public $parent = "div";
    public $parentAttr = ['class'=>'col-sm-6 col-md-4'];
    public $childAttr = ['class'=>'form-group full'];

    public function inputField(Collection $valueFields)
    {

        return collect( [
            [
                'wrapper' => [
                    'tag' => $this->parent,
                    'attr' => $this->parentAttr,
                    'innerWrapper' => [
                        'tag' => $this->parent,
                        'attr' => $this->childAttr,
                    ],
                    'fields' =>[
                        'label' => [
                            'name' => __('Name'),
                            'attr' =>[]
                        ],
                        'input' =>[
                            'type' => 'text',
                            'name' => 'name',
                            'value' => $valueFields->has('name') ? $valueFields->get('name')['value'] : "",
                            'attr' =>['disabled' => 'disabled','class' => 'form-control']

                        ]
                    ]
                ]

            ],
            [
                'wrapper' => [
                    'tag' => $this->parent,
                    'attr' => $this->parentAttr,
                    'innerWrapper' => [
                        'tag' => $this->parent,
                        'attr' => $this->childAttr,
                    ],
                    'fields' =>[
                        'label' => [
                            'name' => __('Email'),
                            'attr' =>[]
                        ],
                        'input' =>[
                            'type' => 'text',
                            'name' => 'email',
                            'value' => $valueFields->has('email') ? $valueFields->get('email')['value'] : "",
                            'attr' =>['disabled' => 'disabled','class' => 'form-control']

                        ]
                    ]
                ]

            ],
            [
                'wrapper' => [
                    'tag' => $this->parent,
                    'attr' => $this->parentAttr,
                    'innerWrapper' => [
                        'tag' => $this->parent,
                        'attr' => $this->childAttr,
                    ],
                    'fields' =>[
                        'label' => [
                            'name' => __('Category'),
                            'attr' =>[]
                        ],
                        'input' =>[
                            'type' => 'select',
                            'name' =>'category',
                            'value' => $valueFields->has('category') ? $valueFields->get('category')['options'] : [],
                            'displayable' => $valueFields->has('category') ? $valueFields->get('category')['displayable'] : [],
                            'selected' => $valueFields->has('category') ? $valueFields->get('category')['selected'] : "",
                            'attr' =>['disabled' => 'disabled','class' => 'form-control']

                        ]
                    ]
                ]

            ],
            [
                'wrapper' => [
                    'tag' => $this->parent,
                    'attr' => $this->parentAttr,
                    'innerWrapper' => [
                        'tag' => $this->parent,
                        'attr' => $this->childAttr,
                    ],
                    'fields' =>[
                        'label' => [
                            'name' => __('Status'),
                            'attr' =>[]
                        ],
                        'input' =>[
                            'type' => 'select',
                            'name' => 'status',
                            'value' => $valueFields->has('status') ? $valueFields->get('status')['options'] : "",
                            'selected' => $valueFields->has('status') ? $valueFields->get('status')['selected'] : "",
                            'attr' =>['disabled' => 'disabled','class' => 'form-control']

                        ]
                    ]
                ]

            ],
            [
                'wrapper' => [
                    'tag' => $this->parent,
                    'attr' => $this->parentAttr,
                    'innerWrapper' => [
                        'tag' => $this->parent,
                        'attr' => $this->childAttr,
                    ],
                    'fields' =>[
                        'label' => [
                            'name' => __('Created At'),
                            'attr' =>[]
                        ],
                        'input' =>[
                            'type' => 'text',
                            'name' => 'created_at',
                            'value' => $valueFields->has('created_at') ? $valueFields->get('created_at')['value'] : "",
                            'attr' =>['disabled' => 'disabled','class' => 'form-control']

                        ]
                    ]
                ]

            ],
            [
                'wrapper' => [
                    'tag' => $this->parent,
                    'attr' => $this->parentAttr,
                    'innerWrapper' => [
                        'tag' => $this->parent,
                        'attr' => $this->childAttr,
                    ],
                    'fields' =>[
                        'label' => [
                            'name' => __('Updated at'),
                            'attr' =>[]
                        ],
                        'input' =>[
                            'type' => 'text',
                            'name' => 'updated_at',
                            'value' => $valueFields->has('updated_at') ? $valueFields->get('updated_at')['value'] : "",
                            'attr' =>['disabled' => 'disabled','class' => 'form-control']

                        ]
                    ]
                ]

            ],


        ]);

    }

}
