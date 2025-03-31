<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $name;

    #[ORM\Column]
    private int $strength;

    #[ORM\Column]
    private int $constitution;

    #[ORM\Column]
    private int $level = 1;

    #[ORM\Column]
    private int $experience = 0;

    #[ORM\Column]
    private int $hp;

    public function __construct(string $name, int $strength, int $constitution)
    {
        $this->name = $name;
        $this->strength = $strength;
        $this->constitution = $constitution;
        $this->hp = $this->getHp();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): self
    {
        $this->strength = $strength;
        return $this;
    }

    public function getConstitution(): int
    {
        return $this->constitution;
    }

    public function setConstitution(int $constitution): self
    {
        $this->constitution = $constitution;
        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getExperience(): int
    {
        return $this->experience;
    }

    // Formule : HP = 10 + (Constitution * 2) + (Niveau * 2)
    public function getHp(): int
    {
        return 10 + ($this->constitution * 2) + ($this->level * 2);
    }

    // Formule : Attaque = 2 + (Force * 1) + (Niveau * 1)
    public function getAttack(): int
    {
        return 2 + ($this->strength * 1) + ($this->level * 1);
    }

    // Formule : Défense = 1 + (Constitution * 0.5) + (Niveau * 0.5)
    public function getDefense(): float
    {
        return 1 + ($this->constitution * 0.5) + ($this->level * 0.5);
    }

    // Attaque un autre personnage
    // Formule : Dégâts = max(1, Attaque de l'attaquant - Défense du défenseur)
    public function attack(Character $target): void
    {
        $damage = max(1, $this->getAttack() - $target->getDefense());
        $target->receiveDamage($damage);
    }

    // Réception des dégâts
    public function receiveDamage(int $damage): void
    {
        $this->hp -= $damage;
        if ($this->hp < 0) {
            $this->hp = 0;
        }
    }

    // Gagner de l'expérience et monter de niveau
    public function gainExperience(int $xp): void
    {
        $this->experience += $xp;
        
        while ($this->experience >= $this->getExperienceThreshold()) {
            $this->levelUp();
        }
    }

    // Monte de niveau si l'XP atteint le seuil
    private function levelUp(): void
    {
        $this->experience -= $this->getExperienceThreshold();
        $this->level++;
        $this->strength += 1;
        $this->constitution += 1;
        $this->hp = $this->getHp();
    }

    // Formule : Seuil d'XP pour niveau suivant = 100 * Niveau
    private function getExperienceThreshold(): int
    {
        return 100 * $this->level;
    }
}
    