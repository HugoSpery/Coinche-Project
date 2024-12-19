<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\Number;
use App\Enum\Type;
use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardRepository::class)]
#[ApiResource]
class Card
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?int $number;
    #[ORM\Column(length: 255)]
    private ?Type $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageBack = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageFront = null;

    #[ORM\Column(nullable: true)]
    private ?int $point = null;

    #[ORM\Column(nullable: true)]
    private ?int $pointAsset = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): Card
    {
        $this->number = $number;
        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): Card
    {
        $this->type = $type;
        return $this;
    }

    public function getImageBack(): ?string
    {
        return $this->imageBack;
    }

    public function setImageBack(?string $imageBack): static
    {
        $this->imageBack = $imageBack;

        return $this;
    }

    public function getImageFront(): ?string
    {
        return $this->imageFront;
    }

    public function setImageFront(?string $imageFront): static
    {
        $this->imageFront = $imageFront;

        return $this;
    }

    public function getPoint(): ?int
    {
        return $this->point;
    }

    public function setPoint(?int $point): static
    {
        $this->point = $point;

        return $this;
    }

    public function getPointAsset(): ?int
    {
        return $this->pointAsset;
    }

    public function setPointAsset(?int $pointAsset): static
    {
        $this->pointAsset = $pointAsset;

        return $this;
    }
}
