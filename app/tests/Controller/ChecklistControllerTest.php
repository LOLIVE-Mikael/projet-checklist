<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\ChecklistRepository;
use App\Repository\TaskRepository;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Repository\UserRepository;

class ChecklistControllerTest extends WebTestCase
{

	public function testIndexPage()
    {
        // Créer un client de test
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/checklist');

        // Vérifier que la réponse est réussie (code HTTP 200)
        $this->assertResponseIsSuccessful();

        // Vérifier que le sélecteur pour le formulaire de checklist existe sur la page
        $this->assertSelectorExists('select[name="form[checklist]"]');
    }

    public function testReadNoChecklist()
    {
		$client = static::createClient();

    	$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/checklist');
		$form = $crawler->filter('#form-select-checklist')->form();
        $client->submit($form);
		$this->assertResponseRedirects('/checklist');
		$crawler = $client->followRedirect();		
        $this->assertResponseIsSuccessful();
	    $this->assertSelectorExists('select[name="form[checklist]"]');
		$selectedValue = $crawler->filter('select[name="form[checklist]"]')->attr('value');
				
		$this->assertEquals('', $selectedValue);
		
    }

	public function testViewNonExistentChecklist(){
        $client = static::createClient();

    	$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET avec une checklist ID qui n'existe pas
        $crawler = $client->request('GET', '/checklist/999999'); // 999999 est un ID qui n'existe pas
        
        // Vérifier la redirection vers le tableau de bord
        $this->assertResponseRedirects('/checklist');

        // Suivre la redirection
        $crawler = $client->followRedirect();

        // Vérifier le message d'erreur affiché
        $this->assertSelectorTextContains('.flash-error', 'Checklist inexistante.');
   }
	
	public function testCreateChecklist(){
		$client = static::createClient();

    	$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);
        $client->loginUser($testUser);
		

		$newChecklistTitle = 'Nouvelle tâche ' . uniqid();

		// Accéder à la page principale pour récupérer le formulaire
		$crawler = $client->request('GET', '/checklist');
		
		// Remplir le formulaire de création de checklist
		$form = $crawler->filter('#form-ajout-checklist')->form([
			'checklist[title]' => 'Test Checklist',
		]);
		
		// Soumettre le formulaire
		$client->submit($form);
		
		// Vérifier la redirection après la soumission du formulaire
		$this->assertTrue($client->getResponse()->isRedirect());

		// Récupérer l'URL cible de la redirection
		$redirectTargetUrl = $client->getResponse()->getTargetUrl();

		// Vérifier que l'URL de redirection commence par '/checklist/'
		$this->assertStringStartsWith('/checklist/', $redirectTargetUrl);

		// Suivre la redirection
		$client->followRedirect();

		// Vérifier que le message de succès est affiché
		$this->assertSelectorTextContains('.flash-success', 'Checklist créée avec succès.');

		// Vérifier que la nouvelle checklist existe dans la base de données
		$checklistRepository = $client->getContainer()->get(ChecklistRepository::class);
		$checklist = $checklistRepository->findOneBy(['title' => $newChecklistTitle]);
		$this->assertNull($checklist);
	}

	public function testDeleteChecklist(){
		$client = static::createClient();

    	$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);
        $client->loginUser($testUser);

		$entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
		$checklistRepository = $client->getContainer()->get(ChecklistRepository::class);
		
		// Créer une checklist pour tester la suppression
		$checklist = new \App\Entity\Checklist();
		$checklist->setTitle('Checklist à supprimer');
		$entityManager->persist($checklist);
		$entityManager->flush();
 
		// Assurer que la checklist a été bien créée
		$this->assertNotNull($checklistRepository->findOneBy(['title' => 'Checklist à supprimer']));
		
		// Accéder à la page principale pour récupérer le formulaire
		$crawler = $client->request('GET', '/checklist');
		
		// Sélectionner la checklist à supprimer et soumettre le formulaire de suppression
		$form = $crawler->selectButton('Supprimer')->form([
			'form[checklist]' => $checklist->getId(),
		]);
		
		$form['form[delete]']->setValue(true);
		$client->submit($form);
		
		// Vérifier la redirection après la soumission du formulaire
		$this->assertResponseRedirects('/checklist');
		
		// Suivre la redirection
		$client->followRedirect();
		
		// Vérifier que le message de succès est affiché
		$this->assertSelectorTextContains('.flash-success', 'Checklist effacée avec succes.');
 
		// Vérifier que la checklist n'existe plus dans la base de données
		$this->assertNull($checklistRepository->findOneBy(['title' => 'Checklist à supprimer']));
	}

	public function testCreateChecklistNoTitle() {
		$client = static::createClient();

		$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);
        $client->loginUser($testUser);

		// Accéder à la page principale pour récupérer le formulaire
		$crawler = $client->request('GET', '/checklist');
		
		// Remplir le formulaire de création de checklist
		$form = $crawler->filter('#form-ajout-checklist')->form([
			'checklist[title]' => '',
		]);
		
		// Soumettre le formulaire
		$client->submit($form);
		
		// Vérifier la redirection après la soumission du formulaire
		$this->assertTrue($client->getResponse()->isRedirect());

		// Récupérer l'URL cible de la redirection
		$redirectTargetUrl = $client->getResponse()->getTargetUrl();

		// Vérifier que l'URL de redirection commence par '/checklist/'
		$this->assertResponseRedirects('/checklist');

		// Suivre la redirection
		$client->followRedirect();

		// Vérifier que le message de succès est affiché
		$this->assertSelectorTextContains('.flash-error', 'Le titre ne peut pas être vide.');

		// Vérifier que la checklist n'existe pas dans la base de données
		$checklistRepository = $client->getContainer()->get(ChecklistRepository::class);
		$this->assertNull($checklistRepository->findOneBy(['title' => '']));

	}

	public function testCreateChecklistTitleTooShort(){
		$client = static::createClient();

    	$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);
        $client->loginUser($testUser);

		// Accéder à la page principale pour récupérer le formulaire
		$crawler = $client->request('GET', '/checklist');
		
		// Remplir le formulaire de création de checklist
		$form = $crawler->filter('#form-ajout-checklist')->form([
			'checklist[title]' => 'Ch',
		]);
		
		// Soumettre le formulaire
		$client->submit($form);
		
		// Vérifier la redirection après la soumission du formulaire
		$this->assertTrue($client->getResponse()->isRedirect());

		// Récupérer l'URL cible de la redirection
		$redirectTargetUrl = $client->getResponse()->getTargetUrl();

		// Vérifier que l'URL de redirection commence par '/checklist/'
		$this->assertResponseRedirects('/checklist');

		// Suivre la redirection
		$client->followRedirect();

		// Vérifier que le message de succès est affiché
		$this->assertSelectorTextContains('.flash-error', 'Le titre doit comporter plus de 3 caracteres.');

		// Vérifier que la checklist n'existe pas dans la base de données
		$checklistRepository = $client->getContainer()->get(ChecklistRepository::class);
		$this->assertNull($checklistRepository->findOneBy(['title' => 'Ch']));
	}

	public function testCreateChecklistTitleTooLong(){
		$client = static::createClient();

    	$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);
        $client->loginUser($testUser);


        $titleTooLong = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
            Nullam eget velit urna. Vestibulum suscipit velit lectus, sed tristique metus ullamcorper vel. Vivamus id turpis vel sapien facilisis tristique. Integer id augue vel tortor laoreet vehicula nec ac erat. Aenean consequat mi nec luctus pharetra. Donec pharetra, risus et vestibulum tempus, quam quam hendrerit ex, quis lobortis turpis nisi nec metus. Donec non neque nec risus faucibus scelerisque ac nec libero. Mauris nec enim est.";
		// Accéder à la page principale pour récupérer le formulaire
		$crawler = $client->request('GET', '/checklist');
		
		// Remplir le formulaire de création de checklist
		$form = $crawler->filter('#form-ajout-checklist')->form([
			'checklist[title]' => $titleTooLong,
		]);
		
		// Soumettre le formulaire
		$client->submit($form);
		
		// Vérifier la redirection après la soumission du formulaire
		$this->assertTrue($client->getResponse()->isRedirect());

		// Récupérer l'URL cible de la redirection
		$redirectTargetUrl = $client->getResponse()->getTargetUrl();

		// Vérifier que l'URL de redirection commence par '/checklist/'
		$this->assertResponseRedirects('/checklist');

		// Suivre la redirection
		$client->followRedirect();

		// Vérifier que le message de succès est affiché
		$this->assertSelectorTextContains('.flash-error', 'Le titre ne peux pas faire plus de 255 caracteres.');

		// Vérifier que la checklist n'existe pas dans la base de données
		$checklistRepository = $client->getContainer()->get(ChecklistRepository::class);
		$this->assertNull($checklistRepository->findOneBy(['title' => $titleTooLong]));
    }

	public function testDisplayTasksForSelectedChecklist() {
		$client = static::createClient();
		
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);
		
        $crawler = $client->request('GET', '/checklist');
		// Soumission du formulaire avec une checklist sélectionnée
		$form = $crawler->filter('#form-select-checklist')->form();
        $client->submitForm('Voir', [
            'form[checklist]' => '1',
        ]);
	    // Vérification de la redirection
		$this->assertResponseRedirects('/checklist/1');
		$crawler = $client->followRedirect();		

		// Vérification de la réussite de la réponse
        $this->assertResponseIsSuccessful();
		
		// Vérification de la checklist sélectionnée		
		$selectedValue = $crawler->filter('select[name="form[checklist]"] option[selected]')->attr('value');
		$this->assertEquals('1', $selectedValue);

        // Vérification de la liste des tâches
		$taskList = $crawler->filter('#task-list li');
		//récupération des taches affichées
		$taskListTexts = $taskList->each(function ($node) {
			    $text = $node->text();
				// Supprimer le texte "dissocier" du bouton
				$textWithoutButton = str_replace('Dissocier', '', $text);
				return $textWithoutButton;
		});
		$taskListTexts = array_map('trim', $taskListTexts);
				
        // Vérification des éléments de la liste des tâches
		$filteredResults = array_filter($taskListTexts, function($text) {
			return preg_match('/^Défragmentation du disque dur*/', $text);
		});
		$this->assertEmpty($filteredResults);	
		$filteredResults = array_filter($taskListTexts, function($text) {
			return preg_match('/^Analyse des pare-feu*/', $text);
		});
		$this->assertEmpty($filteredResults);
		$filteredResults = array_filter($taskListTexts, function($text) {
			return preg_match('/^Nettoyer les fichiers temporaires*/', $text);
		});
		$this->assertEmpty($filteredResults); 

		$filteredResults = array_filter($taskListTexts, function($text) {
			return preg_match('/^Nettoyage physique des ordinateurs*/', $text);
		});
		$this->assertNotEmpty($filteredResults);		

		$filteredResults = array_filter($taskListTexts, function($text) {
			return preg_match('/^Mettre à jour SAP*/', $text);
		});
		$this->assertNotEmpty($filteredResults);		

		$filteredResults = array_filter($taskListTexts, function($text) {
			return preg_match('/^Analyse antivirus*/', $text);
		});
		$this->assertNotEmpty($filteredResults);

		// vérification que la tache archivée n'est pas affichée

		$filteredResults = array_filter($taskListTexts, function($text) {
			return preg_match('/^tache archivée*/', $text);
		});
		$this->assertEmpty($filteredResults);

		// Vérification de l'existence du formulaire des tâches 
        $form = $crawler->filter('#form-add-task')->form();
		$this->assertNotEmpty($form);
				
		// Vérification de la présence des tâches dans la liste déroulante		
		$taskDropdown = $crawler->filter('#task_task');
		$taskDropdownValues = $taskDropdown->filter('option')->extract(['_text']);
		
		//vérification que les tâches non rattachées à la checklist sont présentes dans la liste déroulante
		$this->assertContains('Défragmentation du disque dur', $taskDropdownValues);
		$this->assertContains('Analyse des pare-feu', $taskDropdownValues);
		$this->assertContains('Nettoyer les fichiers temporaires', $taskDropdownValues);
		$this->assertNotContains('Nettoyage physique des ordinateurs', $taskDropdownValues);
		$this->assertNotContains('Mettre à jour SAP', $taskDropdownValues);
		$this->assertNotContains('Analyse antivirus', $taskDropdownValues);
		// vérification que la tache archivée n'est pas dans la liste déroulante
		$this->assertNotContains('tache archivée', $taskListTexts);

    }

	public function testAddTaskFromChecklist()
    {
        $client = static::createClient();
		
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);
		
        $crawler = $client->request('GET', '/checklist/1');
		$form = $crawler->filter('#form-add-task')->form();
        $client->submitForm('Ajouter', [
            'task[task]' => '4',
			'task[checklist_id]' => '1' 
        ]);
	    // Vérification de la redirection
		$this->assertResponseRedirects('/checklist/1');
		$crawler = $client->followRedirect();

        // Vérification que la tache nouvellement ajouté est bien affichée dans la liste des tâches
		$taskList = $crawler->filter('#task-list li');
		
		//récupération des taches affichées
		$taskListTexts = $taskList->each(function ($node) {
			    $text = $node->text();
				// Supprimer le texte "dissocier" du bouton
				$textWithoutButton = str_replace('Dissocier', '', $text);
				return $textWithoutButton;
		});
				
		$taskListTexts = array_map('trim', $taskListTexts);
				
        // Vérification des éléments de la liste des tâches
		$filteredResults = array_filter($taskListTexts, function($text) {
			return preg_match('/^Défragmentation du disque dur*/', $text);
		});		
		$this->assertNotEmpty($filteredResults);	

		// Vérification de l'absence de la tâches d'ajout des taches		
		$taskDropdown = $crawler->filter('#tache_task');
		$taskDropdownValues = $taskDropdown->filter('option')->extract(['_text']);
		
		//vérification que les tâches non rattachées à la checklist sont présentes dans la liste déroulante
		$this->assertNotContains('Défragmentation du disque dur', $taskDropdownValues);
		
		//la tache sera supprimé dans un autre test
    }

	public function testRemoveTaskFromChecklist()
    {
        $client = static::createClient();
		
  	    $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);
		
        $crawler = $client->request('GET', '/checklist/1');
		$form = $crawler->filter('#form-task-4')->form();		
			
        $client->submit($form);
	    // Vérification de la redirection
		$this->assertResponseRedirects('/checklist/1');
		$crawler = $client->followRedirect();

        // Vérification que la tache nouvellement retirée est bien absente de la liste des tâches
		$taskList = $crawler->filter('#task-list li');
		//récupération des taches affichées
		$taskListTexts = $taskList->each(function ($node) {
			    $text = $node->text();
				// Supprimer le texte "dissocier" du bouton
				$textWithoutButton = str_replace('Dissocier', '', $text);
				return $textWithoutButton;
		});
		$taskListTexts = array_map('trim', $taskListTexts);
        // Vérification des éléments de la liste des tâches
		$this->assertNotContains('Défragmentation du disque dur', $taskListTexts);

		// Vérification de l'absence de la tâches d'ajout des taches		
		$taskDropdown = $crawler->filter('#task_task');
		$taskDropdownValues = $taskDropdown->filter('option')->extract(['_text']);

		//vérification que les tâches non rattachées à la checklist sont présentes dans la liste déroulante
		$this->assertContains('Défragmentation du disque dur', $taskDropdownValues);
		
    }

	public function testAddTaskToNonExistantChecklist()
    {
        $client = static::createClient();
		
       $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);
		
        $crawler = $client->request('GET', '/checklist/1');
		$form = $crawler->filter('#form-add-task')->form();
        $client->submitForm('Ajouter', [
            'task[task]' => '4',
			'task[checklist_id]' => '999999' 
        ]);
	    // Vérification de la redirection
		$this->assertResponseRedirects('/checklist');
		$crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.flash-error', 'Checklist non trouvée.');

    }

    public function testAddNewTask()
    { 
		// Créer un titre aléatoire pour la nouvelle tâche
		$newTaskTitle = 'Nouvelle tâche ' . uniqid();
		
		$client = static::createClient();

	    $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

		$crawler = $client->request('GET', '/checklist/1');
		$form = $crawler->filter('#form-add-task')->form();
		$client->submitForm('Ajouter', [
			'task[title]' => $newTaskTitle,
			'task[duration][hours]' => 2,
			'task[duration][minutes]' => 30,
		]);

		$crawler = $client->followRedirect();	
		// Vérification que la tache nouvellement créé est bien affichée dans la liste des tâches
		$taskList = $crawler->filter('#task-list li');
		//récupération des taches affichées
		$taskListTexts = $taskList->each(function ($node) {
			$text = $node->text();
			// Supprimer le texte "dissocier" du bouton
			$textWithoutButton = str_replace('Dissocier', '', $text);
			return $textWithoutButton;
		});
		$taskListTexts = array_map('trim', $taskListTexts);
		// Vérification des éléments de la liste des tâches
				
		$this->assertContains($newTaskTitle." Durée : 2 H 30 min.", $taskListTexts);

		// Retire la tache de la checklist (pas utile mais plus propre)
		$taskToRemoveLi = $crawler->filter("#task-list li:contains('$newTaskTitle')")->first();

		// Cibler le bouton "Dissocier" à l'intérieur du <li>
		$form = $taskToRemoveLi->selectButton('Dissocier')->form();
		$client->submit($form);
		$crawler = $client->followRedirect();	
    }  

    public function testAddNewTaskNoTitle()
    { 
		// Créer un titre aléatoire pour la nouvelle tâche
		
		$client = static::createClient();

 	    $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

		$crawler = $client->request('GET', '/checklist/1');
		$form = $crawler->filter('#form-add-task')->form();
		$client->submitForm('Ajouter', [
			'task[title]' => '',
			'task[duration][hours]' => 2,
			'task[duration][minutes]' => 30,
		]);

		$crawler = $client->followRedirect();	
		
        // Vérifier que le message d'erreur est affiché
		$this->assertSelectorTextContains('.flash-error', 'Le titre ne peut pas être vide.');

		// Vérifier que la tache n'existe pas dans la base de données
		$taskRepository = $client->getContainer()->get(TaskRepository::class);
		$this->assertNull($taskRepository->findOneBy(['title' => '']));
    }

    public function testAddNewTaskTitleTooShort()
    { 
		// Créer un titre aléatoire pour la nouvelle tâche
		
		$client = static::createClient();

 	    $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

		$crawler = $client->request('GET', '/checklist/1');
		$form = $crawler->filter('#form-add-task')->form();
		$client->submitForm('Ajouter', [
			'task[title]' => 'Ta',
			'task[duration][hours]' => 2,
			'task[duration][minutes]' => 30,
		]);

		$crawler = $client->followRedirect();	
		
        // Vérifier que le message d'erreur est affiché

        $flashErrors = $crawler->filter('.flash-error');
        $found = false;
        foreach ($flashErrors as $flashError) {
            if (strpos($flashError->textContent, 'Le titre doit comporter plus de 3 caractères.') !== false) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Message d\'erreur attendu non trouvé.');

		// Vérifier que la tache n'existe pas dans la base de données
		$taskRepository = $client->getContainer()->get(TaskRepository::class);
		$this->assertNull($taskRepository->findOneBy(['title' => 'Ta']));

    }  
}
