<?php

/**
 * User Fixtures
 */

namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use AppBundle\DataFixtures\TokenFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use AppBundle\DataFixtures\UserImageFixtures;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * UserFixtures
 */
class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Load fixtures
     * @access public
     * @param ObjectManager $manager
     * 
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $user = new User;
        $user->setUsername('GoodUser');
        $user->setEmail('good@email.com');
        $user->setPassword('verystrongpassword12');
        $user->setName('Test');
        $user->setFirstName('Bryan');
        $user->setToken($this->getReference(TokenFixtures::VALID_TOKEN_REGISTRATION_REFERENCE));
        $user->setImage($this->getReference(UserImageFixtures::USERIMAGE_REFERENCE));

        $inactiveUser = new User;
        $inactiveUser->setUsername('InactiveUser');
        $inactiveUser->setEmail('bad@email.com');
        $inactiveUser->setPassword('verystrongpassword1222');
        $inactiveUser->setName('TestInactive');
        $inactiveUser->setFirstName('BryanDisabled');
        $inactiveUser->setToken($this->getReference(TokenFixtures::TOKEN_INACTIVE_USER_REFERENCE));
        $inactiveUser->setImage($this->getReference(UserImageFixtures::USERIMAGE_INACTIVE_USER_REFERENCE));

        $userEnabled = new User;
        $userEnabled->setUsername('EnabledUser');
        $userEnabled->setEmail('goodie@email.com');
        $userEnabled->setPassword('verystrongpassword123');
        $userEnabled->setName('TestEnabled');
        $userEnabled->setFirstName('BryanEnabled');
        $userEnabled->setIsActive(true);
        $userEnabled->setToken($this->getReference(TokenFixtures::TOKEN_ENABLED_USER_REFERENCE));
        $userEnabled->setImage($this->getReference(UserImageFixtures::USERIMAGE_ENABLED_USER_REFERENCE));

        $userResetPass = new User;
        $userResetPass->setUsername('ResetPassUser');
        $userResetPass->setEmail('resetpass@email.com');
        $userResetPass->setPassword('verystrongpassword1234');
        $userResetPass->setName('TestResetPass');
        $userResetPass->setFirstName('BryanResetPass');
        $userResetPass->setIsActive(true);
        $userResetPass->setToken($this->getReference(TokenFixtures::TOKEN_RESET_PASS_USER_REFERENCE));
        $userResetPass->setImage($this->getReference(UserImageFixtures::USERIMAGE_RESET_PASS_USER_REFERENCE));

        $otherUserResetPass = new User;
        $otherUserResetPass->setUsername('ResetPassOtherUser');
        $otherUserResetPass->setEmail('resetpasss@email.com');
        $otherUserResetPass->setPassword('verystrongpassword12345');
        $otherUserResetPass->setName('TestOtherResetPass');
        $otherUserResetPass->setFirstName('BryanOtherResetPass');
        $otherUserResetPass->setIsActive(true);
        $otherUserResetPass->setToken($this->getReference(TokenFixtures::TOKEN_RESET_PASS_OTHER_USER_REFERENCE));
        $otherUserResetPass->setImage($this->getReference(UserImageFixtures::USERIMAGE_RESET_PASS_OTHER_USER_REFERENCE));

        $manager->persist($user);
        $manager->persist($userEnabled);
        $manager->persist($inactiveUser);
        $manager->persist($userResetPass);
        $manager->persist($otherUserResetPass);
        $manager->flush();
    }

    /**
     * Get dependencies fixtures
     * @access public
     * 
     * @return array
     */
    public function getDependencies()
    {
        return array(
            TokenFixtures::class,
            UserImageFixtures::class
        );
    }
}