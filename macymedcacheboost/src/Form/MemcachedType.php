<?php

namespace MacymedCacheBoost\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MemcachedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('CACHEBOOST_MEMCACHED_IP', TextType::class, [
                'label' => 'Memcached IP',
                'required' => false,
            ])
            ->add('CACHEBOOST_MEMCACHED_PORT', TextType::class, [
                'label' => 'Memcached Port',
                'required' => false,
            ]);
    }
}
