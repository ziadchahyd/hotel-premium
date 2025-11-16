<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\ClassementH;
use App\Entity\Hotel;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChambreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number')
            ->add('floor')
            ->add('area')
            ->add('pricePerNight')
            ->add('isAvailable')
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'id',
            ])
            ->add('Classement', EntityType::class, [
                'class' => ClassementH::class,
                'choice_label' => 'id',
            ])
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chambre::class,
        ]);
    }
}
