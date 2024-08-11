<?php

namespace App\DataFixtures;

use App\Entity\Visite;
use App\Entity\Checklist;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class VisiteFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            ChecklistFixtures::class,
            SiteFixtures::class,
            UserFixtures::class,
        ];
    }


	public function load(ObjectManager $manager)
	{
        // Récupération des références aux checklists créées dans les fixtures précédentes
        $checklist1 = $this->getReference(ChecklistFixtures::CHECKLIST_1_REFERENCE);
        $checklist2 = $this->getReference(ChecklistFixtures::CHECKLIST_2_REFERENCE);
		
        // Récupération des références aux sites créées dans les fixtures précédentes
        $site1 = $this->getReference(SiteFixtures::SITE_1_REFERENCE);
        $site2 = $this->getReference(SiteFixtures::SITE_2_REFERENCE);
        $site3 = $this->getReference(SiteFixtures::SITE_3_REFERENCE);
        $site4 = $this->getReference(SiteFixtures::SITE_4_REFERENCE);		
        $site5 = $this->getReference(SiteFixtures::SITE_5_REFERENCE);
		
        // Récupération des références aux sites créées dans les fixtures précédentes
        $tech1 = $this->getReference(UserFixtures::TECHNICIEN_1_REFERENCE);
        $tech2 = $this->getReference(UserFixtures::TECHNICIEN_2_REFERENCE);
		
		$visite1 = new Visite();
		$visite1->setSite($site1);
		$visite1->setUser($tech1);
		$visite1->setChecklist($checklist1);
        $date = new \DateTime();
        $date->modify('+7 day');
		$visite1->setDate($date);
		
		$manager->persist($visite1);

		$visite2 = new Visite();
		$visite2->setSite($site2);
		$visite2->setUser($tech2);
		$visite2->setChecklist($checklist2);
        $date = new \DateTime();
        $date->modify('+7 day');
		$visite2->setDate($date);
		
		$manager->persist($visite2);

		$visite3 = new Visite();
		$visite3->setSite($site3);
		$visite3->setUser($tech1);
		$visite3->setChecklist($checklist2);
        $date = new \DateTime();
        $date->modify('+8 day');
		$visite3->setDate($date);
		
		$manager->persist($visite3);

		$visite4 = new Visite();
		$visite4->setSite($site4);
		$visite4->setUser($tech2);
		$visite4->setChecklist($checklist1);
        $date = new \DateTime();
        $date->modify('+8 day');
		$visite4->setDate($date);
		
		$manager->persist($visite4);

		$visite5 = new Visite();
		$visite5->setSite($site5);
		$visite5->setUser($tech1);
		$visite5->setChecklist($checklist1);
        $date = new \DateTime();
        $date->modify('+9 day');
		$visite5->setDate($date);
		
		$manager->persist($visite5);

		$manager->flush();
	}

}