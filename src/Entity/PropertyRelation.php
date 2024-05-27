<?php

namespace App\Entity;

use App\Enum\PropertyStatus;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PropertyRelationRepository;

/**
 * Represents a relation between properties.
 */
#[ORM\Entity(repositoryClass: PropertyRelationRepository::class)]
class PropertyRelation
{
    /**
     * The child property in the relation.
     *
     * @var Property|null
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Property::class)]
    #[ORM\JoinColumn(name: 'property_id', referencedColumnName: 'id')]
    private $property;

    /**
     * The parent property in the relation.
     *
     * @var Property|null
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Property::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    private $parent;

    /**
     * The date and time when the relation was created.
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $created;

    /**
     * The date and time when the relation was last modified.
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $modified;

    /**
     * The status of the relation.
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint')]
    private $status;

    /**
     * Constructor to initialize the creation and modification dates and set the status to active.
     */
    public function __construct()
    {
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
        $this->status = PropertyStatus::ACTIVE->value;
    }

    /**
     * Gets the child property of the relation.
     *
     * @return Property|null The child property.
     */
    public function getProperty(): ?Property
    {
        return $this->property;
    }

    /**
     * Sets the child property of the relation.
     *
     * @param Property $property The child property.
     * @return self
     */
    public function setProperty(Property $property): self
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Gets the parent property of the relation.
     *
     * @return Property|null The parent property.
     */
    public function getParent(): ?Property
    {
        return $this->parent;
    }

    /**
     * Sets the parent property of the relation.
     *
     * @param Property|null $parent The parent property.
     * @return self
     */
    public function setParent(?Property $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Gets the creation date of the relation.
     *
     * @return \DateTimeInterface|null The creation date.
     */
    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    /**
     * Gets the modification date of the relation.
     *
     * @return \DateTimeInterface|null The modification date.
     */
    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    /**
     * Sets the modification date of the relation.
     *
     * @param \DateTimeInterface $modified The modification date.
     * @return self
     */
    public function setModified(\DateTimeInterface $modified): self
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * Gets the status of the relation.
     *
     * @return PropertyStatus The relation status.
     */
    public function getStatus(): PropertyStatus
    {
        return PropertyStatus::from($this->status);
    }

    /**
     * Sets the status of the relation.
     *
     * @param PropertyStatus $status The relation status.
     * @return self
     */
    public function setStatus(PropertyStatus $status): self
    {
        $this->status = $status->value;
        return $this;
    }
}
