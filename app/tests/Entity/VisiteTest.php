<?php

namespace App\Tests\Entity;

use App\Entity\Visite;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\Checklist;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VisiteTest extends KernelTestCase
{

    private function getValidator(): ValidatorInterface
    {
        self::bootKernel();
        $container = self::getContainer();
        return $container->get('validator');
    }

    public function testGetId()
    {
        $visite = new Visite();
        $this->assertNull($visite->getId());
    }

    public function testSetSite()
    {
        $visite = new Visite();
        $site = new Site();
        $visite->setSite($site);
        $this->assertEquals($site, $visite->getSite());
    }

    public function testSetUser()
    {
        $visite = new Visite();
        $user = new User();
        $visite->setUser($user);
        $this->assertEquals($user, $visite->getUser());
    }

    public function testSetChecklist()
    {
        $visite = new Visite();
        $checklist = new Checklist();
        $visite->setChecklist($checklist);
        $this->assertEquals($checklist, $visite->getChecklist());
    }

    public function testValidDate()
    {
        $visite = new Visite();
        $today = new \DateTime('today');
        $visite->setDate($today); // Utiliser une date valide (par exemple, demain)

        $this->assertEquals($today, $visite->getDate());
    }

    public function testNullDate()
    {
        $validator = $this->getValidator();
        $visite = new Visite();
        $visite->setDate(null); // Date nulle

        $errors = $validator->validate($visite);
        $this->assertGreaterThan(0, count($errors)); // Devrait y avoir des erreurs    
    }

}
