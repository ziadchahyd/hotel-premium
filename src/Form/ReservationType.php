<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Hotel;
use App\Entity\Reservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir un hôtel',
                'mapped' => false,
                'required' => true,
                'attr' => ['id' => 'reservation_hotel']
            ])
            ->add('checkIn', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date d\'arrivée',
                'html5' => true,
            ])
            ->add('checkOut', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date de départ',
                'html5' => true,
            ])
            ->add('chambre', EntityType::class, [
                'class' => Chambre::class,
                'choice_label' => function (Chambre $chambre) {
                    return sprintf('Chambre %d - %.2f€/nuit', $chambre->getNumber(), $chambre->getPricePerNight());
                },
                'placeholder' => 'Choisir d\'abord un hôtel',
                'required' => true,
                'attr' => ['id' => 'reservation_chambre'],
                'choices' => [],
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['hotel']) && $data['hotel']) {
                $form->add('chambre', EntityType::class, [
                    'class' => Chambre::class,
                    'choice_label' => function (Chambre $chambre) {
                        return sprintf('Chambre %d - %.2f€/nuit', $chambre->getNumber(), $chambre->getPricePerNight());
                    },
                    'placeholder' => 'Choisir une chambre',
                    'query_builder' => function ($er) use ($data) {
                        return $er->createQueryBuilder('c')
                            ->where('c.hotel = :hotel')
                            ->andWhere('c.isAvailable = :available')
                            ->setParameter('hotel', $data['hotel'])
                            ->setParameter('available', true)
                            ->orderBy('c.number', 'ASC');
                    },
                    'required' => true,
                    'attr' => ['id' => 'reservation_chambre'],
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}