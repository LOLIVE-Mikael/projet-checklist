<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class TechnicianControllerTest extends WebTestCase
{

	public function testIndexPage() {
        // Créer un client de test
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Technicien1']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/technician');

        // Vérifier que la réponse est réussie (code HTTP 200)
        $this->assertResponseIsSuccessful();

        // Vérifier que le sélecteur pour le formulaire de checklist existe sur la page
        $this->assertSelectorExists('select[name="visite_selection[visite]"]');
    }

    public function testReadNoVisite() {
		$client = static::createClient();

    	$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Technicien1']);

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/technician');
		$form = $crawler->filter('#form-select-visite')->form();
        $client->submit($form);
		$this->assertResponseRedirects('/technician');
		$crawler = $client->followRedirect();		
        $this->assertResponseIsSuccessful();
	    $this->assertSelectorExists('select[name="visite_selection[visite]"]');
        // Vérifier que la valeur vide est sélectionnée dans la liste
        $selectedValue = $crawler->filter('select[name="visite_selection[visite]"]')->attr('value');
		$this->assertEquals('', $selectedValue);
		
    }

	public function testViewNonExistentVisite(){
        $client = static::createClient();

    	$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Technicien1']);

        $client->loginUser($testUser);

        // Faire une requête GET avec une checklist ID qui n'existe pas
        $crawler = $client->request('GET', '/technician/999999'); // 999999 est un ID qui n'existe pas

        // Vérifier le message d'erreur affiché
        $this->assertSelectorTextContains('.flash-error', 'Visite inexistante.');
   }

    public function testViewUnauthorizedVisite(){
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Technicien1']);

        $client->loginUser($testUser);

        // Faire une requête GET avec une checklist ID qui n'existe pas
        $crawler = $client->request('GET', '/technician/2'); // 999999 est un ID qui n'existe pas

        // Vérifier le message d'erreur affiché
        $this->assertSelectorTextContains('.flash-error', "Vous n'avez pas access à cette visite.");
    }

	public function testDisplayTasksForSelectedChecklist() {
		$client = static::createClient();
		
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Technicien1']);

        $client->loginUser($testUser);
		
        $crawler = $client->request('GET', '/technician');
		// Soumission du formulaire avec une visite sélectionnée
        $form = $crawler->filter('#form-select-visite')->form();
        $client->submitForm('Voir', [
            'visite_selection[visite]' => '1',
        ]);
	    // Vérification de la redirection
		$this->assertResponseRedirects('/technician/1');
		$crawler = $client->followRedirect();		

		// Vérification de la réussite de la réponse
        $this->assertResponseIsSuccessful();

		// Vérification de la checklist sélectionnée		
		$selectedValue = $crawler->filter('select[name="visite_selection[visite]"] option[selected]')->attr('value');
		$this->assertEquals('1', $selectedValue);
        // Vérification de la liste des tâches
		$taskList = $crawler->filter('#task-list li');

        $expectedTasks = [
            'Nettoyage physique des ordinateurs',
            'Mettre à jour SAP',
            'Analyse antivirus',
        ];
        
        foreach ($taskList as $index => $task) {
            $this->assertEquals(trim($expectedTasks[$index]), trim($task->textContent), 'Task content does not match.');
        }

        $unexpectedTasks = [
            'Défragmentation du disque dur',
            'Analyse des pare-feu',
            'Nettoyer les fichiers temporaires',
        ];
        
        foreach ($unexpectedTasks as $unexpectedTask) {
            $taskTextFound = false;
            foreach ($taskList as $task) {
                if (trim($task->textContent) === $unexpectedTask) {
                    $taskTextFound = true;
                    break;
                }
            }
            $this->assertFalse($taskTextFound, 'Unexpected task found: ' . $unexpectedTask);
        }
    }
}