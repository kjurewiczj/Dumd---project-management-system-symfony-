<?php

namespace App\Form\Task;

use App\Entity\Task;
use App\Entity\User;
use App\Project\Enum\PriorityEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa zadania'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis zadania'
            ])
            ->add('estimatedTime', NumberType::class, [
                'label' => 'Szacowany czas(w godzinach)',
                'required' => false,
            ])
            ->add('workedTime', NumberType::class, [
                'label' => 'Przepracowany czas(w godzinach), aktualnie przepracowany czas to ' . $options['data']->getWorkedTime() . ' godzin.',
                'mapped' => false,
                'required' => false,
            ])
            ->add('progress', ChoiceType::class, [
                'label' => 'Postęp pracy',
                'choices' => [
                    '0%' => 0,
                    '10%' => 10,
                    '20%' => 20,
                    '30%' => 30,
                    '40%' => 40,
                    '50%' => 50,
                    '60%' => 60,
                    '70%' => 70,
                    '80%' => 80,
                    '90%' => 90,
                    '100%' => 100,
                ],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Data planowanego rozpoczęcia zadania',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Data planowanego zakończenia zadania',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorytet',
                'choices' => array_flip(Task::getPriorities()),
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => array_flip(Task::getStatuses()),
            ])
            ->add('userAssigned', EntityType::class, [
                'label' => 'Przypisany do',
                'class' => User::class,
                'choice_label' => 'email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
