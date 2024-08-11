<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\ChecklistsRepository;
use App\Repository\TachesRepository;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Repository\UserRepository;

class VisiteControllerTest extends WebTestCase
{
   public function testIndexPage()
    {
        // Créer un client de test
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/visite/');

        // Vérifier que la réponse est réussie (code HTTP 200)
		$this->assertResponseIsSuccessful();

		$visiteSite = $crawler->filter('#form_visite_1 select[name="visite[site]"] option[selected]')->attr('value');
		$this->assertEquals(1, $visiteSite); 
		$visiteUser = $crawler->filter('#form_visite_1 select[name="visite[user]"] option[selected]')->attr('value');
		$this->assertEquals(1, $visiteUser); 
		$visiteChecklist = $crawler->filter('#form_visite_1 select[name="visite[checklist]"] option[selected]')->attr('value');
		$this->assertEquals(1, $visiteChecklist); 
    }

	public function testNewVisite()
    {
        // Créer un client de test
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/visite/');

        $pageContent = $client->getResponse()->getContent();
		$newTaskForm = $crawler->filter('form#new_visite')->form();
        // Remplir le formulaire de création de visite
		$date = (new \DateTime())->modify('+1 day')->format('d/m/Y');
        $newTaskForm['visite[date]'] = $date;
		$newTaskForm['visite[site]'] = 1; 
        $newTaskForm['visite[user]'] = 1; 
        $newTaskForm['visite[checklist]'] = 1; 
        $client->submit($newTaskForm);
	
        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertSame('/visite/new', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
		$this->assertSame('/visite/', $client->getRequest()->getRequestUri());
	
	}

	public function testNewVisiteNoDate()
    {
        // Créer un client de test
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/visite/');

        $pageContent = $client->getResponse()->getContent();
		$newTaskForm = $crawler->filter('form#new_visite')->form();
        // Remplir le formulaire de création de visite
        $newTaskForm['visite[date]'] = '';
		$newTaskForm['visite[site]'] = 1; 
        $newTaskForm['visite[user]'] = 1; 
        $newTaskForm['visite[checklist]'] = 1; 
        $client->submit($newTaskForm);
	
        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertSame('/visite/new', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
      /*  $htmlContent = $client->getResponse()->getContent();
        echo $htmlContent;  // Ceci affichera le HTML dans la console lorsque vous exécuterez le test */

		$this->assertSame('/visite/', $client->getRequest()->getRequestUri());

		// Vérifier que le message d'erreur
		$this->assertSelectorTextContains('.flash-error', 'La date ne peut pas être vide.');

	}

	public function testNewVisiteWrongDate()
    {
        // Créer un client de test
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['login' => 'Manager']);

        $client->loginUser($testUser);

        // Faire une requête GET vers la page du manager
        $crawler = $client->request('GET', '/visite/');

        $pageContent = $client->getResponse()->getContent();
		$newTaskForm = $crawler->filter('form#new_visite')->form();
        // Remplir le formulaire de création de visite
		$date = (new \DateTime())->modify('-1 day')->format('d/m/Y');
        $newTaskForm['visite[date]'] = $date;
		$newTaskForm['visite[site]'] = 1; 
        $newTaskForm['visite[user]'] = 1; 
        $newTaskForm['visite[checklist]'] = 1; 
        $client->submit($newTaskForm);
	
        // Vérifier si la redirection vers la page de création a eu lieu
        $this->assertSame('/visite/new', $client->getRequest()->getRequestUri());
		$crawler = $client->followRedirect();
      /*  $htmlContent = $client->getResponse()->getContent();
        echo $htmlContent;  // Ceci affichera le HTML dans la console lorsque vous exécuterez le test */

		$this->assertSame('/visite/', $client->getRequest()->getRequestUri());

		// Vérifier que le message d'erreur
		$this->assertSelectorTextContains('.flash-error', 'This value should be greater than or equal to ');

	}

}