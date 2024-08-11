<?php

namespace App\Form;

use DateTime;
use App\Entity\Visite;
use App\Entity\Users;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisiteSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
		
	    $user = $options['user'] ?? null;
	    $visite = $options['visite'] ?? null;
				
        // Ajoutez le champ de la liste déroulante pour les visites
        $builder->add('visite', EntityType::class, [
            'class' => Visite::class,
			'query_builder' => function (EntityRepository $er) use ($user)  {
                $yesterday = new DateTime('yesterday');
				$qb = $er->createQueryBuilder('v')
                    ->where('v.date >= :yesterday')
					->orderBy('v.date', 'ASC') // Tri par date croissante (plus ancienne en premier)
                    ->setParameter('yesterday', $yesterday);
					
				if ($user !== null) {
					$qb->andWhere('v.user = :user')
					   ->setParameter('user', $user);
				}

				return $qb;
			},
			'choice_label'  => function ($visite) {
				return $visite->getDate()->format('d/m/Y') . ' - ' . $visite->getSite()->getName();
			},
            'placeholder' => 'Choisir une visite',
            'data' => $visite,
            'mapped' => false,
            'required' => false
		]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visite::class,
			'user' => null,
            'visite' => null,
            'validation_groups' => false,
        ]);
    }
}
