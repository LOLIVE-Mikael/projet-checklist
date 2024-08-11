<?php

namespace App\Service;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\VisiteType;
use App\Form\VisiteSelectionType;
use App\Entity\Visite;
use App\Entity\User;

class VisiteFormService
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }


    /**
     * Créer un formulaire pour selectionner une visite
     *
     * @param User|null $user
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSelectVisiteForm(?Visite $visite= null, ?User $user = null)
    {

        // Création du formulaire pour la visite existante
        $form = $this->formFactory->create(VisiteSelectionType::class, null, ['user' => $user, 'visite' => $visite]);

        // Ajout du bouton d'ajout
        $form->add('submit', SubmitType::class, [
            'label' => 'Voir',
            'attr' => ['class' => 'select_button'],        
        ]);

        return $form;
    }

    /**
     * Créer un formulaire pour une visite existante avec un bouton de suppression
     *
     * @param Visite $visite
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createEditVisiteForm(Visite $visite)
    {
		
        // Création du formulaire pour la visite existante
        $form = $this->formFactory->create(VisiteType::class, $visite);

        // Ajout du bouton d'ajout
        $form->add('update', SubmitType::class, [
            'label' => 'Modifier',
            'attr' => ['class' => 'update_button'],
        ]);

        // Ajout du bouton de suppression
        $form->add('delete', SubmitType::class, [
            'label' => 'Supprimer',
            'attr' => ['class' => 'delete_button'],
        ]);

        return $form;
    }

    /**
     * Créer un formulaire pour l'ajout d'une nouvelle visite
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createNewVisiteForm(Visite $visite = null)
    {		
        // Création du formulaire pour une nouvelle visite
        $form = $this->formFactory->create(VisiteType::class, $visite);

        // Ajout du bouton d'ajout
        $form->add('save', SubmitType::class, [
            'label' => 'Ajouter',
            'attr' => ['class' => 'add_button'],
        ]);

        return $form;
    }
}
