<?php

namespace App\DataFixtures;

use App\Entity\Checklist;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\DataFixtures\TaskFixtures;

class ChecklistFixtures extends Fixture implements DependentFixtureInterface
{
    public const CHECKLIST_1_REFERENCE = 'checklist-1';
    public const CHECKLIST_2_REFERENCE = 'checklist-2';
	
    public function getDependencies()
    {
        return [
            TaskFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // Récupération des références aux tâches créées dans les fixtures précédentes
        $task1 = $this->getReference(TaskFixtures::TASK_1_REFERENCE);
        $task2 = $this->getReference(TaskFixtures::TASK_2_REFERENCE);
        $task3 = $this->getReference(TaskFixtures::TASK_3_REFERENCE);
        $task4 = $this->getReference(TaskFixtures::TASK_4_REFERENCE);
        $task5 = $this->getReference(TaskFixtures::TASK_5_REFERENCE);
        $task6 = $this->getReference(TaskFixtures::TASK_6_REFERENCE);
        $task7 = $this->getReference(TaskFixtures::TASK_7_REFERENCE);
       
        // Création d'une nouvelle checklist
        $checklist = new Checklist(); 
        $checklist->setTitle('Maintenance préventive');

        // Ajout des tâches à la checklist
        $checklist->addTask($task1);
        $checklist->addTask($task2);
        $checklist->addTask($task3);
        $checklist->addTask($task7);

        // Persist et flush
        $manager->persist($checklist);

        // Création d'une nouvelle checklist
        $checklist2 = new Checklist(); 
        $checklist2->setTitle('Vérification de sécurité'); // Correction du titre

        // Ajout des tâches à la checklist
        $checklist2->addTask($task4);
        $checklist2->addTask($task5);
        $checklist2->addTask($task6);
        $checklist2->addTask($task7);


        // Persist et flush
        $manager->persist($checklist2);

        // Définition des références pour les tâches
        $this->addReference(self::CHECKLIST_1_REFERENCE, $checklist);
        $this->addReference(self::CHECKLIST_2_REFERENCE, $checklist2);

        $manager->flush();
    }
}
