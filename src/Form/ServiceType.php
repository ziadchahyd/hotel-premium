<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('chambres', EntityType::class, [
                'class' => Chambre::class,
                
                'choice_label' => function (Chambre $chambre) {
                    
                    return sprintf(
                        'Chambre %d - %s (%.2f €/nuit)',
                        $chambre->getNumber(),
                        $chambre->getHotel()?->getName() ?? 'Sans hôtel',
                        $chambre->getPricePerNight()
                    );
                },
                'multiple' => true,
                'required' => false,      
                'by_reference' => false, 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
