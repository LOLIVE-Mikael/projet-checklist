<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
	private $passwordHasher;
    public const TECHNICIEN_1_REFERENCE = 'technicien-1';
    public const TECHNICIEN_2_REFERENCE = 'technicien-2';

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher  = $passwordHasher;
    }
	
	
	public function load(ObjectManager $manager)
	{
		// Créer l'utilisateur Technicien1
		$technicien1 = new User();
		$technicien1->setLogin('Technicien1');
		$technicien1->setRoles(['ROLE_USER']);
		$technicien1->setPassword($this->passwordHasher->hashPassword($technicien1, 'technicien1'));
		$manager->persist($technicien1);

		// Créer l'utilisateur Technicien2
		$technicien2 = new User();
		$technicien2->setLogin('Technicien2');
		$technicien2->setRoles(['ROLE_USER']);
		$technicien2->setPassword($this->passwordHasher->hashPassword($technicien2, 'technicien2'));
		$manager->persist($technicien2);

		// Créer l'utilisateur Manager
		$managerUser = new User();
		$managerUser->setLogin('Manager');
		$managerUser->setRoles(['ROLE_MANAGER']);
		$managerUser->setPassword($this->passwordHasher->hashPassword($managerUser, 'manager'));
		$manager->persist($managerUser);

		// Créer l'utilisateur Admin
		$adminUser = new User();
		$adminUser->setLogin('Admin');
		$adminUser->setRoles(['ROLE_ADMIN']);
		$adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin'));
		$manager->persist($adminUser);

        // Définition des références pour les utilisateurs
        $this->addReference(self::TECHNICIEN_1_REFERENCE, $technicien1);
        $this->addReference(self::TECHNICIEN_2_REFERENCE, $technicien2);

		$manager->flush();
	}

}