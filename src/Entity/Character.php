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
    private int $currentHp;

    #[ORM\Column]
    private int $gold = 0;

    public const CLASS_WARRIOR = 'warrior';
    public const CLASS_ROGUE = 'rogue';
    public const CLASS_MAGE = 'mage';

    public function __construct(string $name, int $strength, int $constitution)
    {
        $this->name = $name;
        $this->strength = $strength;
        $this->constitution = $constitution;
        $this->currentHp = $this->getMaxHp();
    }

    /**
     * Créer un personnage à partir d'une classe prédéfinie
     */
    public static function createFromClass(string $name, string $class): self
    {
        return match ($class) {
            self::CLASS_WARRIOR => new self($name, 3, 3),
            self::CLASS_ROGUE => new self($name, 4, 2),
            self::CLASS_MAGE => new self($name, 5, 1),
            default => throw new \InvalidArgumentException("Classe inconnue: $class")
        };
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

    public function getGold(): int
    {
        return $this->gold;
    }

    public function setGold(int $gold): self
    {
        $this->gold = $gold;
        return $this;
    }

    public function addGold(int $amount): self
    {
        $this->gold += $amount;
        return $this;
    }

    // Formule : HP = 10 + (Constitution * 2) + (Niveau * 2)
    public function getMaxHp(): int
    {
        return 10 + ($this->constitution * 2) + ($this->level * 2);
    }

    // Alias pour compatibilité avec tests existants
    public function getHp(): int
    {
        return $this->currentHp;
    }

    public function getCurrentHp(): int
    {
        return $this->currentHp;
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

    /**
     * Calcule la chance de toucher contre un ennemi
     * Hit Chance = 75% + (STR - Enemy CON) × 3% + LVL
     * Min: 50% | Max: 95%
     */
    public function getHitChance(Character $target): float
    {
        $hitChance = 75 + ($this->strength - $target->getConstitution()) * 3 + $this->level;
        return max(50, min(95, $hitChance));
    }

    /**
     * Attaque un autre personnage
     * 1. Détermine si l'attaque touche
     * 2. Calcule les dégâts avec un multiplicateur aléatoire
     * 
     * @return array Information sur l'attaque [hit => bool, damage => int]
     */
    public function attack(Character $target): array
    {
        $hitChance = $this->getHitChance($target);
        $roll = mt_rand(1, 100);
        
        // L'attaque a-t-elle touché?
        if ($roll > $hitChance) {
            return ['hit' => false, 'damage' => 0];
        }
        
        // Calcul des dégâts avec multiplicateur aléatoire
        $randomMultiplier = mt_rand(80, 120) / 100; // 0.8 à 1.2
        $baseDamage = $this->getAttack() - $target->getDefense();
        $damage = max(1, ceil($baseDamage * $randomMultiplier));
        
        $target->receiveDamage($damage);
        
        return ['hit' => true, 'damage' => $damage];
    }

    /**
     * Réception des dégâts
     */
    public function receiveDamage(int $damage): void
    {
        $this->currentHp -= $damage;
        if ($this->currentHp < 0) {
            $this->currentHp = 0;
        }
    }

    /**
     * Vérifie si le personnage est mort
     */
    public function isDead(): bool
    {
        return $this->currentHp <= 0;
    }

    /**
     * Gagner de l'expérience et monter de niveau
     */
    public function gainExperience(int $xp): void
    {
        $this->experience += $xp;
        
        while ($this->experience >= $this->getExperienceThreshold()) {
            $this->levelUp();
        }
    }

    /**
     * Calcul l'XP obtenue en battant un ennemi
     */
    public function getExperienceForDefeating(Character $enemy): int
    {
        return ($enemy->getLevel() * 20) + mt_rand(10, 30);
    }

    /**
     * Perte d'XP à la mort (33%)
     */
    public function handleDeath(): void
    {
        $xpLoss = (int)($this->experience * 0.33);
        $this->experience = max(0, $this->experience - $xpLoss);
    }

    /**
     * Soigner le personnage
     */
    public function heal(int $amount): void
    {
        $this->currentHp = min($this->getMaxHp(), $this->currentHp + $amount);
    }

    /**
     * Restaurer le personnage à son maximum de HP
     */
    public function fullHeal(): void
    {
        $this->currentHp = $this->getMaxHp();
    }

    /**
     * Monte de niveau si l'XP atteint le seuil
     * Le joueur choisit d'augmenter STR ou CON
     */
    public function levelUp(string $attributeToIncrease = null): void
    {
        $this->experience -= $this->getExperienceThreshold();
        $this->level++;
        
        // Si l'attribut n'est pas spécifié, on augmente les deux (pour la compatibilité avec tests existants)
        if ($attributeToIncrease === null) {
            $this->strength += 1;
            $this->constitution += 1;
        } else {
            // Sinon, on augmente uniquement l'attribut choisi
            switch ($attributeToIncrease) {
                case 'strength':
                    $this->strength += 1;
                    break;
                case 'constitution':
                    $this->constitution += 1;
                    break;
                default:
                    throw new \InvalidArgumentException("Attribut invalide: $attributeToIncrease");
            }
        }
        
        // Mise à jour des HP actuels au nouveau maximum
        $this->currentHp = $this->getMaxHp();
    }

    /**
     * Formule : Seuil d'XP pour niveau suivant = 100 * Niveau
     */
    public function getExperienceThreshold(): int
    {
        return 100 * $this->level;
    }
}