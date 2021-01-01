<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $introUser = $this->instantiateUser('intro-user@example.com', 'introuser', ['ROLE_INTRO']);
        $manager->persist($introUser);

        $bureauUser = $this->instantiateUser('bureau-user@example.com', 'bureauuser', ['ROLE_BUREAU']);
        $manager->persist($bureauUser);

        $adminUser = $this->instantiateUser('admin-user@example.com', 'adminuser', ['ROLE_ADMIN']);
        $manager->persist($adminUser);

        $superAdminUser = $this->instantiateUser('super-admin-user@example.com', 'superadminuser', ['ROLE_SUPER_ADMIN']);
        $manager->persist($superAdminUser);

        $manager->flush();
    }

    /**
     * @param string $email
     * @param string $username
     * @param array $roles
     * @param string $password
     * @return Users
     */
    private function instantiateUser(string $email, string $username, array $roles, string $password = 'secret'): Users
    {
        $user = new Users();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword($this->passwordEncoder
            ->encodePassword($user, $password));
        $user->setRoles($roles);
        return $user;
    }
}
