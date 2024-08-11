<?php

namespace App\Form; 

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => $options['required'], 
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Entrez le titre de la tâche',
                ],
            ])
			
            ->add('duration', DateIntervalType::class, [
					'widget'      => 'integer', // render a text field for each part
					// 'input'    => 'string',  // if you want the field to return a ISO 8601 string back to you
					// customize which text boxes are shown
					'with_years'  => false,
					'with_months' => false,
					'with_days'   => false,
					'with_hours'  => true,
					'with_minutes'  => true,
					'required' => false,
                    'label' => 'Durée (HH:MM)',				
				])
			//->add('id', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'validation_groups' => ['Default'], // Utilise les groupes de validation par défaut
            'required' => true,
        ]);
    }
}
