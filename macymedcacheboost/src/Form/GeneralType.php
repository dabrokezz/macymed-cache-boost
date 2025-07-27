<?php

namespace MacymedCacheBoost\Form;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GeneralType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('CACHEBOOST_ENABLED', SwitchType::class, [
                'label' => 'Enable Cache',
                'required' => false,
            ])
            ->add('CACHEBOOST_ENABLE_DEV_MODE', SwitchType::class, [
                'label' => 'Enable Dev Mode',
                'required' => false,
            ])
            ->add('CACHEBOOST_DURATION', IntegerType::class, [
                'label' => 'Cache Duration (seconds)',
                'required' => false,
            ])
            ->add('CACHEBOOST_EXCLUDE', TextType::class, [
                'label' => 'Excluded URLs',
                'required' => false,
            ])
            ->add('CACHEBOOST_ENGINE', ChoiceType::class, [
                'label' => 'Cache Engine',
                'choices' => [
                    'File System' => 'filesystem',
                    'Redis' => 'redis',
                    'Memcached' => 'memcached',
                ],
            ])
            ->add('CACHEBOOST_PURGE_AGE', IntegerType::class, [
                'label' => 'Purge files older than (days)',
                'required' => false,
            ])
            ->add('CACHEBOOST_PURGE_SIZE', IntegerType::class, [
                'label' => 'Purge files if cache size exceeds (MB)',
                'required' => false,
            ]);
    }
}
