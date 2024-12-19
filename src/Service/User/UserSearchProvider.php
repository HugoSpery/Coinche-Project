<?php

namespace App\Service\User;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\PartyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

class UserSearchProvider
{
    public function __construct(private Security $security,private UserManager $userManager,private UserRepository $userRepository,private GameRepository $gameRepository)
    {

    }

    public function generateName(){
        $animals = [
            'aardvark','albatross','alligator','alpaca','ant','anteater','antelope','ape','armadillo','donkey','baboon','badger','barracuda','bat','bear','beaver','bee','bison','boar','buffalo','galago','butterfly','camel','caribou','cat','caterpillar','cattle','chamois','cheetah','chicken','chimpanzee','chinchilla','chough','clam','cobra','cockroach','cod','cormorant','coyote','crab','crane','crocodile','crow','curlew','deer','dinosaur','dog','dogfish','dolphin','donkey','dotterel','dove','dragonfly','duck','dugong','dunlin','eagle','echidna','eel','eland','elephant','elephant-seal','elk','emu','falcon','ferret','finch','fish','flamingo','fly','fox','frog','gaur','gazelle','gerbil','giant-panda','giraffe','gnat','gnu','goat','goose','goldfinch','goldfish','gorilla','goshawk','grasshopper','grouse','guanaco','guinea-fowl','guinea-pig','gull','hamster','hare','hawk','hedgehog','heron','herring','hippopotamus','hornet','horse','human','hummingbird','hyena','jackal','jaguar','jay','jellyfish','kangaroo','koala','komodo-dragon','kouprey','kudu','lapwing','lark','lemur','leopard','lion','llama','lobster','locust','loris','louse','lyrebird','magpie','mallard','manatee','marten','meerkat','mink','mole','monkey','moose','mouse','mosquito','mule','narwhal','newt','nightingale','octopus','okapi','opossum','oryx','ostrich','otter','owl','ox','oyster','panther','parrot','partridge','peafowl','pelican','penguin','pheasant','pig','pigeon','pony','porcupine','porpoise','prairie-dog','quail','quelea','rabbit','raccoon','rail','ram','rat','raven','red-deer','red-panda','reindeer','rhinoceros','rook','ruff','salamander','salmon','sand-dollar','sandpiper','sardine','scorpion','sea-lion','sea-urchin','seahorse','seal','shark','sheep','shrew','shrimp','skunk','snail','snake','spider','squid','squirrel','starling','stingray','stinkbug','stork','swallow','swan','tapir','tarsier','termite','tiger','toad','trout','turkey','turtle','vicuña','viper','vulture','wallaby','walrus','wasp','water-buffalo','weasel','whale','wolf','wolverine','wombat','woodcock','woodpecker','worm','wren','yak','zebra'
        ];

        $random_key = array_rand($animals);

        $picked_name = $animals[$random_key];

        $random_number = rand(1000, 9999);


        return $picked_name . '-' . $random_number;
    }
    public function createRandomUser(){
        $user = new User();
        $user->setUsername($this->generateName());
        $user->setPassword('password');
        $user->setFake(true);
        $user->setRoles(['ROLE_USERTEMPORARY']);
        $user->setAvatar('/images/avatar.png');
        $user->setIsStarting(false);
        $this->userManager->save($user);
        $this->security->login($user);
    }

    public function getStatInfo(User $user){
        $nbGamePlayed = $this->gameRepository->getNbGamePlayed($user);
        $nbGameWins = $this->gameRepository->getWinGamePlayed($user);
        $nbGameLoose =  $nbGamePlayed - $nbGameWins;

        $gameWonEvolution = $this->gameRepository->gameWonEvolution($user);
        $trophyEvolution = $this->gameRepository->trophyEvolution($user);

        return [
            'nbGamePlayed' => $nbGamePlayed,
            'nbGameWon' => $nbGameWins,
            'nbGameLost' => $nbGameLoose,
            'gameWonEvolution' => $gameWonEvolution,
            'trophyEvolution' => $trophyEvolution
        ];
    }

    public function getFriendList(User $user,string $query){
        $friends = [];
        foreach ($user->getFriends() as $friend){
            if (str_contains(strtolower($friend->getUsername()),strtolower($query))){
                $friends[] = $friend;
            }
        }
        return $friends;
    }


}