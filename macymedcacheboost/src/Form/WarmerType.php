<?php

namespace MacymedCacheBoost\Form;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class WarmerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('CACHEBOOST_AUTO_WARMUP', SwitchType::class, [
                'label' => 'Warm cache automatically after invalidation',
                'required' => false,
            ]);
    }
}
