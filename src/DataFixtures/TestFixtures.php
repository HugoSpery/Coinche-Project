<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\User;
use App\Service\User\UserSearchProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture
{

    public function __construct(private UserSearchProvider $userSearchProvider,private UserPasswordHasherInterface $userPasswordHasher){

    }

    public function load(ObjectManager $manager): void
    {
        $users = [];
        for ($i = 0; $i < 4; $i++) {
            $user = new User();
            $user->setUsername('user'.$i);
            $user->setTrophy(0);
            $user->setFake(false);
            $user->setAvatar('/images/avatar.png');
            $user->setRoles(['ROLE_SUPERUSER']);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
            $users[] = $user;
            $manager->persist($user);
        }
        $manager->flush();

        for($i=0; $i<100; $i++){
            $game = new Game();
            $game->setPointsBlue(random_int(0,1000));
            $game->setPointsRed(random_int(0,1000));
            $game->setTurnBlue(random_int(0,1));
            $game->setCode('code'.$i);
            $game->setIsEnd(true);
            $game->setPlayers(new ArrayCollection($users));
            shuffle($users);
            $game->setTeamRed(new ArrayCollection([$users[0],$users[1]]));
            $game->setTeamBlue(new ArrayCollection([$users[2],$users[3]]));

            if ($game->getPointsRed() > $game->getPointsBlue()){

                if ($game->getPointsRed() - $game->getPointsBlue() >= 600){
                      $points = 50;
                }else if($game->getPointsRed() - $game->getPointsBlue() >= 300) {
                    $points = 25;
                }else{
                    $points = 15;
                }
                $users[0]->setTrophy($users[0]->getTrophy() + $points);
                $users[1]->setTrophy($users[1]->getTrophy() + $points);
                $users[2]->setTrophy($users[2]->getTrophy() - $points);
                $users[3]->setTrophy($users[3]->getTrophy() - $points);
                if ($users[2]->getTrophy() < 0){
                    $users[2]->setTrophy(0);
                }
                if ($users[3]->getTrophy() < 0){
                    $users[3]->setTrophy(0);
                }

            } else{
                if ($game->getPointsBlue() - $game->getPointsRed() >= 600){
                    $points = 50;
                }else if($game->getPointsBlue() - $game->getPointsRed() >= 300) {
                    $points = 25;
                }
                else{
                    $points = 15;
                }
                $users[2]->setTrophy($users[2]->getTrophy() + $points);
                $users[3]->setTrophy($users[3]->getTrophy() + $points);


                $users[0]->setTrophy($users[0]->getTrophy() - $points);

                if ($users[0]->getTrophy() < 0){
                    $users[0]->setTrophy(0);
                }

                $users[1]->setTrophy($users[1]->getTrophy() - $points);
                if ($users[1]->getTrophy() < 0){
                    $users[1]->setTrophy(0);
                }

            }



            $manager->persist($users[0]);
            $manager->persist($users[1]);
            $manager->persist($users[2]);
            $manager->persist($users[3]);
            $manager->persist($game);

        }

        $manager->flush();

    }
}
