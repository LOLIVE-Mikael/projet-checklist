<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Task;
use DateInterval;

class TaskFixtures extends Fixture
{
    public const TASK_1_REFERENCE = 'TASK-1';
    public const TASK_2_REFERENCE = 'TASK-2';
    public const TASK_3_REFERENCE = 'TASK-3';
    public const TASK_4_REFERENCE = 'TASK-4';   
    public const TASK_5_REFERENCE = 'TASK-5';
    public const TASK_6_REFERENCE = 'TASK-6';
    public const TASK_7_REFERENCE = 'TASK-7';

    public function load(ObjectManager $manager): void
    {
        // Création des tâches
        $task1 = new Task();
        $task1->setTitle('Nettoyage physique des ordinateurs');
		$task1->setDuration(new DateInterval('PT1H30M'));
		$task1->setArchived(false);
        $manager->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Mettre à jour SAP');
		$task2->setDuration(new DateInterval('PT2H00M'));
		$task2->setArchived(false);
        $manager->persist($task2);

        $task3 = new Task();
        $task3->setTitle('Analyse antivirus');
		$task3->setDuration(new DateInterval('PT3H15M'));
		$task3->setArchived(false);
        $manager->persist($task3);

        $task4 = new Task();
        $task4->setTitle('Défragmentation du disque dur');
		$task4->setDuration(new DateInterval('PT1H00M'));
		$task4->setArchived(false);
        $manager->persist($task4);

        $task5 = new Task();
        $task5->setTitle('Analyse des pare-feu');
		$task5->setDuration(new DateInterval('PT0H30M'));
		$task5->setArchived(false);
        $manager->persist($task5);

        $task6 = new Task();
        $task6->setTitle('Nettoyer les fichiers temporaires');
		$task6->setDuration(new DateInterval('PT1H00M'));
		$task6->setArchived(false);
        $manager->persist($task6);

        $task7 = new Task();
        $task7->setTitle('tache archivée');
		$task7->setDuration(new DateInterval('PT1H00M'));
		$task7->setArchived(true);
        $manager->persist($task6);

        // Définition des références pour les tâches
        $this->addReference(self::TASK_1_REFERENCE, $task1);
        $this->addReference(self::TASK_2_REFERENCE, $task2);
        $this->addReference(self::TASK_3_REFERENCE, $task3);
        $this->addReference(self::TASK_4_REFERENCE, $task4);
        $this->addReference(self::TASK_5_REFERENCE, $task5);
        $this->addReference(self::TASK_6_REFERENCE, $task6);
        $this->addReference(self::TASK_7_REFERENCE, $task7);

        $manager->flush();
    }
}
