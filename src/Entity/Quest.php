<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Quest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column]
    private int $experienceReward;

    #[ORM\Column]
    private int $goldReward;

    #[ORM\Column]
    private int $requiredLevel;

    #[ORM\Column]
    private bool $completed = false;

    #[ORM\ManyToMany(targetEntity: "Character")]
    private Collection $assignedCharacters;

    #[ORM\OneToMany(mappedBy: "quest", targetEntity: "QuestObjective")]
    private Collection $objectives;

    public function __construct(string $title, string $description, int $experienceReward, int $goldReward, int $requiredLevel)
    {
        $this->title = $title;
        $this->description = $description;
        $this->experienceReward = $experienceReward;
        $this->goldReward = $goldReward;
        $this->requiredLevel = $requiredLevel;
        $this->assignedCharacters = new ArrayCollection();
        $this->objectives = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
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

    public function getExperienceReward(): int
    {
        return $this->experienceReward;
    }

    public function setExperienceReward(int $experienceReward): self
    {
        $this->experienceReward = $experienceReward;
        return $this;
    }

    public function getGoldReward(): int
    {
        return $this->goldReward;
    }

    public function setGoldReward(int $goldReward): self
    {
        $this->goldReward = $goldReward;
        return $this;
    }

    public function getRequiredLevel(): int
    {
        return $this->requiredLevel;
    }

    public function setRequiredLevel(int $requiredLevel): self
    {
        $this->requiredLevel = $requiredLevel;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }

    public function getAssignedCharacters(): Collection
    {
        return $this->assignedCharacters;
    }

    public function assignCharacter(Character $character): self
    {
        if (!$this->assignedCharacters->contains($character)) {
            $this->assignedCharacters->add($character);
        }
        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        $this->assignedCharacters->removeElement($character);
        return $this;
    }

    public function getObjectives(): Collection
    {
        return $this->objectives;
    }

    public function addObjective(QuestObjective $objective): self
    {
        if (!$this->objectives->contains($objective)) {
            $this->objectives->add($objective);
            $objective->setQuest($this);
        }
        return $this;
    }

    public function removeObjective(QuestObjective $objective): self
    {
        if ($this->objectives->removeElement($objective)) {
            if ($objective->getQuest() === $this) {
                $objective->setQuest(null);
            }
        }
        return $this;
    }

    /**
     * Vérifie si tous les objectifs de la quête sont complétés
     */
    public function checkCompletion(): bool
    {
        foreach ($this->objectives as $objective) {
            if (!$objective->isCompleted()) {
                return false;
            }
        }

        $this->completed = true;
        return true;
    }

    /**
     * Attribue les récompenses de quête au personnage
     */
    public function completeQuest(Character $character): void
    {
        if (!$this->completed) {
            $this->checkCompletion();
        }

        if ($this->completed) {
            $character->gainExperience($this->experienceReward);
            $character->addGold($this->goldReward);
        }
    }
}