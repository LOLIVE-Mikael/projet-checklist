<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public const SITE_1_REFERENCE = 'site-1';
    public const SITE_2_REFERENCE = 'site-2';
    public const SITE_3_REFERENCE = 'site-3';
    public const SITE_4_REFERENCE = 'site-4';   
    public const SITE_5_REFERENCE = 'site-5';
	
    public function load(ObjectManager $manager): void
    {
      
        $site1 = new Site(); 
        $site1->setName('Rennes');

        // Persist et flush
        $manager->persist($site1);

        $site2 = new Site(); 
        $site2->setName('Brest');

        // Persist et flush
        $manager->persist($site2);

        $site3 = new Site(); 
        $site3->setName('Nantes');

        // Persist et flush
        $manager->persist($site3);
		

        $site4 = new Site(); 
        $site4->setName('Tour');

        // Persist et flush
        $manager->persist($site4);


        $site5 = new Site(); 
        $site5->setName('Paris');

        // Persist et flush
        $manager->persist($site5);

        // Définition des références pour les sites
        $this->addReference(self::SITE_1_REFERENCE, $site1);
        $this->addReference(self::SITE_2_REFERENCE, $site2);
        $this->addReference(self::SITE_3_REFERENCE, $site3);
        $this->addReference(self::SITE_4_REFERENCE, $site4);
        $this->addReference(self::SITE_5_REFERENCE, $site5);

        $manager->flush();						
    }
}
