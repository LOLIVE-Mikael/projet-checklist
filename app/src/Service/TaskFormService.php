<?php
namespace App\Service;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use App\Repository\TaskRepository;
use App\Entity\Checklist;
use App\Entity\Task;
use App\Form\TaskType;
 
class TaskFormService
{
    private $formFactory;
    private $taskRepository;

    public function __construct(FormFactoryInterface $formFactory, TaskRepository $taskRepository)
    {
        $this->formFactory = $formFactory;
        $this->taskRepository = $taskRepository;
    }

    /**
     * Créer un formulaire pour une tâche existante avec un bouton de suppression
     *
     * @param Task $task
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createEditTaskForm(Task $task)
    {
		
        // Création du formulaire pour la visite existante
        $form = $this->formFactory->create(TaskType::class, $task);

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
     * Créer un formulaire pour l'ajout d'une nouvelle tâche
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createNewTaskForm(Task $task = null)
    {		
        // Création du formulaire pour une nouvelle visite
        $form = $this->formFactory->create(TaskType::class, $task);

        // Ajout du bouton d'ajout
        $form->add('save', SubmitType::class, [
            'label' => 'Ajouter',
            'attr' => ['class' => 'add_button'],
        ]);

        return $form;
    }

    public function createAddTaskForChecklistForm(Checklist $checklist = null): FormInterface
    {
        $choices = $checklist 
            ? $this->taskRepository->findTasksNotInChecklist($checklist) 
            : $this->taskRepository->findAll();

        $task = new Task();

        $form = $this->formFactory->createBuilder(TaskType::class, $task, [
            'required' => false,
        ])
            ->add('task', ChoiceType::class, [
                'choices' => $choices,
                'choice_value' => 'id',
                'choice_label' => 'title',
                'placeholder' => 'Choisir une tâche',
                'mapped' => false,
                'required' => false,
            ])
            ->add('checklist_id', HiddenType::class, [
                'data' => $checklist ? $checklist->getId() : null,
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
            ])

            ->getForm();

        return $form;
    }
}

