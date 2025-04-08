<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class QuestObjective
{
    public const TYPE_KILL_ENEMY = 'kill_enemy';
    public const TYPE_COLLECT_ITEM = 'collect_item';
    public const TYPE_EXPLORE_LOCATION = 'explore_location';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $description;

    #[ORM\Column(length: 20)]
    private string $type;

    #[ORM\Column]
    private string $targetId; // ID de l'ennemi, de l'objet ou du lieu cible

    #[ORM\Column]
    private int $requiredAmount = 1;

    #[ORM\Column]
    private int $currentAmount = 0;

    #[ORM\Column]
    private bool $completed = false;

    #[ORM\ManyToOne(inversedBy: "objectives")]
    private ?Quest $quest = null;

    public function __construct(string $description, string $type, string $targetId, int $requiredAmount = 1)
    {
        $this->description = $description;
        $this->type = $type;
        $this->targetId = $targetId;
        $this->requiredAmount = $requiredAmount;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function setTargetId(string $targetId): self
    {
        $this->targetId = $targetId;
        return $this;
    }

    public function getRequiredAmount(): int
    {
        return $this->requiredAmount;
    }

    public function setRequiredAmount(int $requiredAmount): self
    {
        $this->requiredAmount = $requiredAmount;
        return $this;
    }

    public function getCurrentAmount(): int
    {
        return $this->currentAmount;
    }

    public function setCurrentAmount(int $currentAmount): self
    {
        $this->currentAmount = $currentAmount;
        $this->checkCompletion();
        return $this;
    }

    public function incrementProgress(int $amount = 1): self
    {
        $this->currentAmount += $amount;
        $this->checkCompletion();
        return $this;
    }

    private function checkCompletion(): void
    {
        $this->completed = $this->currentAmount >= $this->requiredAmount;
        
        // Si l'objectif est complété et fait partie d'une quête, vérifier si la quête est complétée
        if ($this->completed && $this->quest !== null) {
            $this->quest->checkCompletion();
        }
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function getQuest(): ?Quest
    {
        return $this->quest;
    }

    public function setQuest(?Quest $quest): self
    {
        $this->quest = $quest;
        return $this;
    }
}