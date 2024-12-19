<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotNull(message: 'Please enter a username')]
    #[Assert\NotBlank(message: 'Please enter a username')]
    #[Assert\Length(min: 3, minMessage: 'Your username must be at least {{ limit }} characters long')]
    #[Assert\Length(max: 15, maxMessage: 'Your username cannot be longer than {{ limit }} characters')]
    private ?string $username = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $avatar;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'players')]
    private ?Lobby $party = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isStarting = false;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?Hand $hand = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isFake = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'users')]
    private Collection $friends;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'friends')]
    private Collection $users;

    /**
     * @var Collection<int, FriendRequest>
     */
    #[ORM\OneToMany(targetEntity: FriendRequest::class, mappedBy: 'userSender')]
    private Collection $friendRequests;

    /**
     * @var Collection<int, FriendRequest>
     */
    #[ORM\OneToMany(targetEntity: FriendRequest::class, mappedBy: 'userReceiver')]
    private Collection $friendRequestsReceive;

    #[ORM\Column(nullable: true)]
    private ?int $trophy = null;

    #[ORM\OneToOne(mappedBy: 'chief', cascade: ['persist', 'remove'])]
    private ?Lobby $lobby = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isReady = null;

    public function __construct()
    {
        $this->friends = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->friendRequests = new ArrayCollection();
        $this->friendRequestsReceive = new ArrayCollection();
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): User
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getIsFake(): ?bool
    {
        return $this->isFake;
    }

    public function setIsFake(?bool $isFake): User
    {
        $this->isFake = $isFake;
        return $this;
    }
    public function isStarting(): bool
    {
        return $this->isStarting;
    }

    public function setIsStarting(bool $isStarting): User
    {
        $this->isStarting = $isStarting;
        return $this;
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getParty(): ?Lobby
    {
        return $this->party;
    }

    public function setParty(?Lobby $party): static
    {
        $this->party = $party;

        return $this;
    }

    public function getHand(): ?Hand
    {
        return $this->hand;
    }

    public function setHand(?Hand $hand): static
    {
        $this->hand = $hand;

        return $this;
    }

    public function isFake(): ?bool
    {
        return $this->isFake;
    }

    public function setFake(?bool $isFake): static
    {
        $this->isFake = $isFake;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(self $friend): static
    {
        if (!$this->friends->contains($friend)) {
            $this->friends->add($friend);
        }

        return $this;
    }

    public function removeFriend(self $friend): static
    {
        $this->friends->removeElement($friend);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(self $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFriend($this);
        }

        return $this;
    }

    public function removeUser(self $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeFriend($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, FriendRequest>
     */
    public function getFriendRequests(): Collection
    {
        return $this->friendRequests;
    }

    public function addFriendRequest(FriendRequest $friendRequest): static
    {
        if (!$this->friendRequests->contains($friendRequest)) {
            $this->friendRequests->add($friendRequest);
            $friendRequest->setUserSender($this);
        }

        return $this;
    }

    public function removeFriendRequest(FriendRequest $friendRequest): static
    {
        if ($this->friendRequests->removeElement($friendRequest)) {
            // set the owning side to null (unless already changed)
            if ($friendRequest->getUserSender() === $this) {
                $friendRequest->setUserSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FriendRequest>
     */
    public function getFriendRequestsReceive(): Collection
    {
        return $this->friendRequestsReceive;
    }

    public function addFriendRequestsReceive(FriendRequest $friendRequestsReceive): static
    {
        if (!$this->friendRequestsReceive->contains($friendRequestsReceive)) {
            $this->friendRequestsReceive->add($friendRequestsReceive);
            $friendRequestsReceive->setUserReceiver($this);
        }

        return $this;
    }

    public function removeFriendRequestsReceive(FriendRequest $friendRequestsReceive): static
    {
        if ($this->friendRequestsReceive->removeElement($friendRequestsReceive)) {
            // set the owning side to null (unless already changed)
            if ($friendRequestsReceive->getUserReceiver() === $this) {
                $friendRequestsReceive->setUserReceiver(null);
            }
        }

        return $this;
    }

    public function getTrophy(): ?int
    {
        return $this->trophy;
    }

    public function setTrophy(?int $trophy): static
    {
        $this->trophy = $trophy;

        return $this;
    }

    public function getLobby(): ?Lobby
    {
        return $this->lobby;
    }

    public function setLobby(?Lobby $lobby): static
    {
        // unset the owning side of the relation if necessary
        if ($lobby === null && $this->lobby !== null) {
            $this->lobby->setChief(null);
        }

        // set the owning side of the relation if necessary
        if ($lobby !== null && $lobby->getChief() !== $this) {
            $lobby->setChief($this);
        }

        $this->lobby = $lobby;

        return $this;
    }

    public function isReady(): ?bool
    {
        return $this->isReady;
    }

    public function setReady(?bool $isReady): static
    {
        $this->isReady = $isReady;

        return $this;
    }

}
