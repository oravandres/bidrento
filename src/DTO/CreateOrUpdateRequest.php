<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Data Transfer Object for creating or updating a property.
 */
class CreateOrUpdateRequest
{
    /**
     * The name of the property.
     *
     * @var string
     */
    #[Assert\Length(
        min: 1,
        max: 255,
        normalizer: 'trim',
        minMessage: 'Name must be at least {{ limit }} characters long',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    private string $name;

    /**
     * The type of the property.
     *
     * @var string
     */
    #[Assert\Choice(['property', 'parking_space'], message: 'Invalid property type: {{ value }}')]
    private string $type;

    /**
     * The ID of the parent property.
     *
     * @var int|null
     */
    #[SerializedName("parent_id")]
    private ?int $parentId = null;

    /**
     * Constructor to initialize the CreateOrUpdateRequest object.
     *
     * @param string $name The name of the property.
     * @param string $type The type of the property.
     * @param int|null $parentId The ID of the parent property.
     */
    public function __construct(string $name, string $type, ?int $parentId = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->parentId = $parentId;
    }

    /**
     * Gets the name of the property.
     *
     * @return string The property name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the type of the property.
     *
     * @return string The property type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the ID of the parent property.
     *
     * @return int|null The parent property ID.
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }
}
