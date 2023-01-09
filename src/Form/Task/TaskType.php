<?php

namespace App\Form\Task;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
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
                'required' => false
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Data planowanego rozpoczęcia zadania',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Data planowanego zakończenia zadania',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorytet',
                'choices' => array_flip(Task::getPriorities()),
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
