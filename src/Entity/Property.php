<?php

namespace App\Entity;

use App\Enum\PropertyType;
use App\Enum\PropertyStatus;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PropertyRepository;

/**
 * Represents a property entity.
 */
#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{
    /**
     * The unique identifier of the property.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * The name of the property.
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    /**
     * The type of the property.
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint')]
    private $type;

    /**
     * The date and time when the property was created.
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $created;

    /**
     * The date and time when the property was last modified.
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $modified;

    /**
     * The status of the property.
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint')]
    private $status;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->modified = new \DateTime();
        $this->status = PropertyStatus::ACTIVE->value;
    }

    /**
     * Gets the ID of the property.
     *
     * @return int|null The property ID.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the name of the property.
     *
     * @return string|null The property name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the name of the property.
     *
     * @param string $name The property name.
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets the type of the property.
     *
     * @return PropertyType The property type.
     */
    public function getType(): PropertyType
    {
        return PropertyType::from($this->type);
    }

    /**
     * Sets the type of the property.
     *
     * @param PropertyType $type The property type.
     * @return self
     */
    public function setType(PropertyType $type): self
    {
        $this->type = $type->value;
        return $this;
    }

    /**
     * Gets the creation date of the property.
     *
     * @return \DateTimeInterface|null The creation date.
     */
    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    /**
     * Gets the modification date of the property.
     *
     * @return \DateTimeInterface|null The modification date.
     */
    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    /**
     * Sets the modification date of the property.
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
     * Gets the status of the property.
     *
     * @return PropertyStatus The property status.
     */
    public function getStatus(): PropertyStatus
    {
        return PropertyStatus::from($this->status);
    }

    /**
     * Sets the status of the property.
     *
     * @param PropertyStatus $status The property status.
     * @return self
     */
    public function setStatus(PropertyStatus $status): self
    {
        $this->status = $status->value;
        return $this;
    }
}
