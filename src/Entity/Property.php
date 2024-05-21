<?php

namespace App\Entity;

use App\Enum\PropertyType;
use App\Enum\PropertyStatus;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PropertyRepository;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    // Name uniqueness is checked in the code to allow soft delete
    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'smallint')]
    private $type;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\Column(type: 'datetime')]
    private $modified;

    #[ORM\Column(type: 'smallint')]
    private $status;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
        $this->status = PropertyStatus::ACTIVE->value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): PropertyType
    {
        return PropertyType::from($this->type);
    }

    public function setType(PropertyType $type): self
    {
        $this->type = $type->value;
        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): self
    {
        $this->modified = $modified;
        return $this;
    }

    public function getStatus(): PropertyStatus
    {
        return PropertyStatus::from($this->status);
    }

    public function setStatus(PropertyStatus $status): self
    {
        $this->status = $status->value;
        return $this;
    }
}
