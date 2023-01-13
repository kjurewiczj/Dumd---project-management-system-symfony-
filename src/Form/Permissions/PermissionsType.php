<?php

namespace App\Form\Permissions;

use App\Entity\Permissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermissionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('create_task', ChoiceType::class, [
                'label' => 'Tworzenie zadań',
                'choices' => [
                    'Nie' => false,
                    'Tak' => true,
                ]
            ])
            ->add('delete_task', ChoiceType::class, [
                'label' => 'Usuwanie zadań',
                'choices' => [
                    'Nie' => false,
                    'Tak' => true,
                ]
            ])
            ->add('statistics', ChoiceType::class, [
                'label' => 'Statystyki',
                'choices' => [
                    'Nie' => false,
                    'Tak' => true,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permissions::class,
        ]);
    }
}
