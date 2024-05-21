<?php

namespace App\Entity;

use App\Enum\PropertyStatus;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PropertyRelationRepository;

#[ORM\Entity(repositoryClass: PropertyRelationRepository::class)]
class PropertyRelation
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Property::class)]
    #[ORM\JoinColumn(name: 'property_id', referencedColumnName: 'id')]
    private $property;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Property::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    private $parent;

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

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(Property $property): self
    {
        $this->property = $property;
        return $this;
    }

    public function getParent(): ?Property
    {
        return $this->parent;
    }

    public function setParent(?Property $parent): self
    {
        $this->parent = $parent;
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
