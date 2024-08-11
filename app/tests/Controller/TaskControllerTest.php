<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\ChecklistsRepository;
use App\Repository\TachesRepository;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Repository\UserRepository;

class TaskControllerTest extends WebTestCase
{
   public function testIndexPage()
    {
        // Créer un client de test
        $client = static::createClient();
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->findOneBy(['login' => 'Manager']);

		$client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/task/');

        // Vérifier que la réponse est réussie (code HTTP 200)
		$this->assertResponseIsSuccessful();
				
        $titleTask = $crawler->filter('#form_task_1 #task_title')->attr('value');
		$this->assertEquals('Nettoyage physique des ordinateurs', $titleTask);

    }

   public function testNewTache()
    {
        // Créer un client de test
        $client = static::createClient();
		$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/task/');

        $pageContent = $client->getResponse()->getContent();
		$newTaskForm = $crawler->filter('form#new_task')->form();
        // Remplir le formulaire de création de tâche
        $newTaskForm['task[title]'] = 'Nouvelle tâche';
        $client->submit($newTaskForm);
	
        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertSame('/task/new', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
		$this->assertSame('/task/', $client->getRequest()->getRequestUri());
		$bodyNode = $crawler->filter('body');
		$bodyContent = $bodyNode->html();
	
		// Vérifier que la nouvelle tâche est présente dans le contenu de la page
		//$this->assertContains('Nouvelle tâche', $bodyContent);
		$this->assertTrue(strpos($bodyContent, 'Nouvelle tâche') !== false);
	}

	public function testNewTacheTitleTooShort()
    {
        // Créer un client de test
        $client = static::createClient();
		$userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/task/');

        $pageContent = $client->getResponse()->getContent();
		$newTaskForm = $crawler->filter('form#new_task')->form();
        // Remplir le formulaire de création de tâche
        $newTaskForm['task[title]'] = 'ta';
        $client->submit($newTaskForm);
	
        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertSame('/task/new', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
		$this->assertSame('/task/', $client->getRequest()->getRequestUri());
	
		// Vérifier que le message d'erreur
		$this->assertSelectorTextContains('.flash-error', 'Le titre doit comporter plus de 3 caractères.');
	}


	public function testNewTacheTitleTooLong()
    {
        // Créer un client de test
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/task/');

        $pageContent = $client->getResponse()->getContent();
		$newTaskForm = $crawler->filter('form#new_task')->form();
        // Remplir le formulaire de création de tâche
        $newTaskForm['task[title]'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
            Nullam eget velit urna. Vestibulum suscipit velit lectus, sed tristique metus ullamcorper vel. Vivamus id turpis vel sapien facilisis tristique. Integer id augue vel tortor laoreet vehicula nec ac erat. Aenean consequat mi nec luctus pharetra. Donec pharetra, risus et vestibulum tempus, quam quam hendrerit ex, quis lobortis turpis nisi nec metus. Donec non neque nec risus faucibus scelerisque ac nec libero. Mauris nec enim est.';   
        $client->submit($newTaskForm);
	
        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertSame('/task/new', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
		$this->assertSame('/task/', $client->getRequest()->getRequestUri());
		$bodyNode = $crawler->filter('body');
		$bodyContent = $bodyNode->html();
	
		// Vérifier que le message d'erreur
		$this->assertSelectorTextContains('.flash-error', 'Le titre ne peut pas faire plus de 255 caractères.');
	}

	public function testUpdateTache()
	{
       // Créer un client de test
	   $client = static::createClient();
       $userRepository = static::getContainer()->get(UserRepository::class);
	   $testUser = $userRepository->findOneBy(['login' => 'Manager']);
   
		$client->loginUser($testUser);
		$crawler = $client->request('GET', '/task/');

		$editForms = $crawler->filter('form.form-modification');
		// Sélectionner le dernier formulaire de modification
   
		$lastEdit = $editForms->last();
		$lastEditForm = $lastEdit->form();
   
		$values = $lastEditForm->getPhpValues();
		$values['task']['update'] = '';
		$values['task']['title'] = 'Nouveau titre';
   
		$client->request(
		    $lastEditForm->getMethod(),
		    $lastEditForm->getUri(),
		    $values
		);   

        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertMatchesRegularExpression('~^/task/handle/\d+$~', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
		$this->assertSame('/task/', $client->getRequest()->getRequestUri());
		$bodyNode = $crawler->filter('body');
		$bodyContent = $bodyNode->html();

		// Vérifier que le nouveau titre est présent dans le contenu de la page
		//$this->assertContains('Nouvelle tâche', $bodyContent);
		$this->assertTrue(strpos($bodyContent, 'Nouveau titre') !== false);
	}

	public function testUpdateTacheTitleTooShort()
	{
       // Créer un client de test
	   $client = static::createClient();
	   $userRepository = static::getContainer()->get(UserRepository::class);
	   $testUser = $userRepository->findOneBy(['login' => 'Manager']);
   
	   $client->loginUser($testUser);
		$crawler = $client->request('GET', '/task/');

		$editForms = $crawler->filter('form.form-modification');
		// Sélectionner le dernier formulaire de modification
   
		$lastEdit = $editForms->last();
		$lastEditForm = $lastEdit->form();
   
		$values = $lastEditForm->getPhpValues();
		$values['task']['update'] = '';
		$values['task']['title'] = 'Ti';
   
		$client->request(
		    $lastEditForm->getMethod(),
		    $lastEditForm->getUri(),
		    $values
		);   

        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertMatchesRegularExpression('~^/task/handle/\d+$~', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
		$this->assertSame('/task/', $client->getRequest()->getRequestUri());

		// Vérifier que le nouveau titre est présent dans le contenu de la page
		//$this->assertContains('Nouvelle tâche', $bodyContent);
		$this->assertSelectorTextContains('.flash-error', 'Le titre doit comporter plus de 3 caractères.');
	}

	public function testUpdateTacheTitleTooLong()
	{
       // Créer un client de test
	   $client = static::createClient();
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->findOneBy(['login' => 'Manager']);
   
		$client->loginUser($testUser);
		$crawler = $client->request('GET', '/task/');

		$editForms = $crawler->filter('form.form-modification');
		// Sélectionner le dernier formulaire de modification
   
		$lastEdit = $editForms->last();
		$lastEditForm = $lastEdit->form();
   
		$values = $lastEditForm->getPhpValues();
		$values['task']['update'] = '';
		$values['task']['title'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
            Nullam eget velit urna. Vestibulum suscipit velit lectus, sed tristique metus ullamcorper vel. Vivamus id turpis vel sapien facilisis tristique. Integer id augue vel tortor laoreet vehicula nec ac erat. Aenean consequat mi nec luctus pharetra. Donec pharetra, risus et vestibulum tempus, quam quam hendrerit ex, quis lobortis turpis nisi nec metus. Donec non neque nec risus faucibus scelerisque ac nec libero. Mauris nec enim est.';   
		$client->request(
		    $lastEditForm->getMethod(),
		    $lastEditForm->getUri(),
		    $values
		);   

        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertMatchesRegularExpression('~^/task/handle/\d+$~', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
		$this->assertSame('/task/', $client->getRequest()->getRequestUri());

		// Vérifier que le nouveau titre est présent dans le contenu de la page
		//$this->assertContains('Nouvelle tâche', $bodyContent);
		$this->assertSelectorTextContains('.flash-error', 'Le titre ne peut pas faire plus de 255 caractères.');
	}

	public function testDeleteTache(){
       // Créer un client de test
	   $client = static::createClient();
		$userRepository = static::getContainer()->get(UserRepository::class);
		$testUser = $userRepository->findOneBy(['login' => 'Manager']);
   
		$client->loginUser($testUser);
		$crawler = $client->request('GET', '/task/');		

		$editForms = $crawler->filter('form.form-modification');
		// Sélectionner le dernier formulaire de modification
   
		$lastEdit = $editForms->last();
		$lastEditForm = $lastEdit->form();

		// Sélectionner le dernier formulaire de modification

		$lastEdit = $editForms->last();
		$lastEditForm = $lastEdit->form();

		$values = $lastEditForm->getPhpValues();
		$values['task']['delete'] = '';

		$deleteTaskTitle = $values['task']['title'];

		$client->request(
			$lastEditForm->getMethod(),
			$lastEditForm->getUri(),
			$values
		);
        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertMatchesRegularExpression('~^/task/handle/\d+$~', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
		$this->assertSame('/task/', $client->getRequest()->getRequestUri());
		$bodyNode = $crawler->filter('body');
		$bodyContent = $bodyNode->html();		
		// Vérifier que le nouveau titre est absent dans le contenu de la page
		$this->assertTrue(strpos($bodyContent, $deleteTaskTitle) === false);

    }

}