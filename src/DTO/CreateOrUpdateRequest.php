<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CreateOrUpdateRequest
{
    #[Assert\Length(
        min: 1,
        max: 255,
        normalizer: 'trim',
        minMessage: 'Name must be at least {{ limit }} characters long',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    private string $name;

    #[Assert\Choice(['property', 'parking_space'], message: 'Invalid property type: {{ value }}')]
    private string $type;

    #[SerializedName("parent_id")]
    private ?int $parentId = null;

    public function __construct(string $name, string $type, ?int $parentId = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->parentId = $parentId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }
}
