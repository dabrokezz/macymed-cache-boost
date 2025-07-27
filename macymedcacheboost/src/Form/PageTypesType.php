<?php

namespace MacymedCacheBoost\Form;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PageTypesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('CACHEBOOST_CACHE_HOMEPAGE', SwitchType::class, [
                'label' => 'Cache Homepage',
                'required' => false,
            ])
            ->add('CACHEBOOST_CACHE_CATEGORY', SwitchType::class, [
                'label' => 'Cache Category Pages',
                'required' => false,
            ])
            ->add('CACHEBOOST_CACHE_PRODUCT', SwitchType::class, [
                'label' => 'Cache Product Pages',
                'required' => false,
            ])
            ->add('CACHEBOOST_CACHE_CMS', SwitchType::class, [
                'label' => 'Cache CMS Pages',
                'required' => false,
            ]);
    }
}
