<?php

namespace App\Form;

use App\Entity\Checklist;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\Visite;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\ChecklistRequired;

class VisiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
			->add('date', DateTimeType::class, [
				'widget' => 'single_text',
		        'format' => 'dd/MM/yyyy',
				'html5' => false, 
                'attr' => ['class' => 'custom-date-class'],
			])
            ->add('site', EntityType::class, [
                'class' => Site::class,
				'choice_label' => 'Name',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
				'choice_label' => 'login',
				'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%"ROLE_USER"%');
                }
            ])
            ->add('checklist', EntityType::class, [
                'class' => Checklist::class,
				'choice_label' => 'title',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visite::class,
            'validation_groups' => ['Default'],
        ]);
    }
}
