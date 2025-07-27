<?php

namespace MacymedCacheBoost\Form;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class BotsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('CACHEBOOST_BOT_CACHE_ENABLED', SwitchType::class, [
                'label' => 'Enable Bot Cache',
                'required' => false,
            ])
            ->add('CACHEBOOST_BOT_USER_AGENTS', TextareaType::class, [
                'label' => 'Bot User Agents',
                'required' => false,
            ]);
    }
}
