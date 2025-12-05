<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\ClassementH; // ou App\Entity\Classement si ton entité s'appelle comme ça
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

            // Hôtel de la chambre
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                // On affiche le nom de l'hôtel, pas l'id
                'choice_label' => function (Hotel $hotel) {
                    return $hotel->getName();
                },
                'placeholder' => 'Choisir un hôtel',
            ])

            // Classement de la chambre
            ->add('classement', EntityType::class, [
                'class' => ClassementH::class,
                // pareil, on montre quelque chose de lisible
                'choice_label' => function (ClassementH $classement) {
                    return $classement->getName();
                },
                'placeholder' => 'Choisir un classement',
            ])

            // Services associés à la chambre (ManyToMany)
            ->add('services', EntityType::class, [
                'class' => Service::class,
                'choice_label' => function (Service $service) {
                    return $service->getName();
                },
                'multiple' => true,
                'required' => false,      // ✅ on peut créer la chambre sans services au début
                'by_reference' => false,  // ✅ gère bien le ManyToMany (addService/removeService)
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
