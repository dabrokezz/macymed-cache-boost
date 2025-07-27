<?php

namespace MacymedCacheBoost\Form;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AssetsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('CACHEBOOST_ASSET_CACHE_ENABLED', SwitchType::class, [
                'label' => 'Enable Asset Cache',
                'required' => false,
            ])
            ->add('CACHEBOOST_ASSET_EXTENSIONS', TextType::class, [
                'label' => 'Asset Extensions',
                'required' => false,
            ])
            ->add('CACHEBOOST_ASSET_DURATION', IntegerType::class, [
                'label' => 'Asset Cache Duration (seconds)',
                'required' => false,
            ]);
    }
}
