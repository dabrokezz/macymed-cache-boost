<?php

namespace MacymedCacheBoost\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RedisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('CACHEBOOST_REDIS_IP', TextType::class, [
                'label' => 'Redis IP',
                'required' => false,
            ])
            ->add('CACHEBOOST_REDIS_PORT', TextType::class, [
                'label' => 'Redis Port',
                'required' => false,
            ]);
    }
}
