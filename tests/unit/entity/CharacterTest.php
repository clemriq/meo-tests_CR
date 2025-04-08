<?php

namespace App\tests\unit\entity;

use App\Entity\Character;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    private Character $character;

    protected function setUp(): void
    {
        $this->character = new Character('Clément', 15, 12);
    }

    public function testConstructorAndGetters(): void
    {
        $this->assertEquals('Clément', $this->character->getName());
        $this->assertEquals(15, $this->character->getStrength());
        $this->assertEquals(12, $this->character->getConstitution());
        $this->assertNull($this->character->getId());
    }

    public function testSetName(): void
    {
        $this->character->setName('Riquet');
        $this->assertEquals('Riquet', $this->character->getName());
    }

    public function testSetStrength(): void
    {
        $this->character->setStrength(18);
        $this->assertEquals(18, $this->character->getStrength());
    }

    public function testSetConstitution(): void
    {
        $this->character->setConstitution(16);
        $this->assertEquals(16, $this->character->getConstitution());
    }

    public function testFluentInterface(): void
    {
        $returnedCharacter = $this->character->setName('Clément Riquet');
        $this->assertSame($this->character, $returnedCharacter);
    }

    /**
     * @dataProvider hpDataProvider
     */
    public function testGetHp(int $constitution, int $level, int $expectedHp): void
    {
        $character = new Character('Test', 10, $constitution);

        // Simuler un niveau différent si nécessaire
        $reflection = new \ReflectionClass($character);
        $levelProperty = $reflection->getProperty('level');
        $levelProperty->setAccessible(true);
        $levelProperty->setValue($character, $level);

        $this->assertEquals($expectedHp, $character->getMaxHp());
    }

    public function hpDataProvider(): array
    {
        return [
            // Formule HP = 10 + (CON × 2) + (LVL x 2)
            'Niveau 1, Constitution basse' => [3, 1, 18],
            'Niveau 1, Constitution moyenne' => [10, 1, 32],
            'Niveau 1, Constitution haute' => [15, 1, 42],
            'Niveau 5, Constitution moyenne' => [10, 5, 40],
        ];
    }

    /**
     * @dataProvider attackDataProvider
     */
    public function testGetAttack(int $strength, int $level, int $expectedAttack): void
    {
        $character = new Character('Test', $strength, 10);

        // Simuler un niveau différent si nécessaire
        $reflection = new \ReflectionClass($character);
        $levelProperty = $reflection->getProperty('level');
        $levelProperty->setAccessible(true);
        $levelProperty->setValue($character, $level);

        $this->assertEquals($expectedAttack, $character->getAttack());
    }

    public function attackDataProvider(): array
    {
        return [
            // Formule Attack = 2 + (STR × 1) + (LVL x 1)
            'Niveau 1, Force basse' => [3, 1, 6],
            'Niveau 1, Force moyenne' => [10, 1, 13],
            'Niveau 1, Force haute' => [15, 1, 18],
            'Niveau 5, Force moyenne' => [10, 5, 17],
        ];
    }

    /**
     * @dataProvider defenseDataProvider
     */
    public function testGetDefense(int $constitution, int $level, float $expectedDefense): void
    {
        $character = new Character('Test', 10, $constitution);

        // Simuler un niveau différent si nécessaire
        $reflection = new \ReflectionClass($character);
        $levelProperty = $reflection->getProperty('level');
        $levelProperty->setAccessible(true);
        $levelProperty->setValue($character, $level);

        $this->assertEquals($expectedDefense, $character->getDefense());
    }

    public function defenseDataProvider(): array
    {
        return [
            // Formule Defense = 1 + (CON × 0,5) + (LVL x 0,5)
            'Niveau 1, Constitution basse' => [3, 1, 3.0],
            'Niveau 1, Constitution moyenne' => [10, 1, 6.5],
            'Niveau 1, Constitution haute' => [15, 1, 9.0],
            'Niveau 5, Constitution moyenne' => [10, 5, 8.5],
        ];
    }

    public function testCreateFromClass(): void
    {
        // Test pour la classe Warrior
        $warrior = Character::createFromClass('Warrior', Character::CLASS_WARRIOR);
        $this->assertEquals(3, $warrior->getStrength());
        $this->assertEquals(3, $warrior->getConstitution());
        $this->assertEquals(16, $warrior->getMaxHp());
        $this->assertEquals(6, $warrior->getAttack());
        $this->assertEquals(3.0, $warrior->getDefense());

        // Test pour la classe Rogue
        $rogue = Character::createFromClass('Rogue', Character::CLASS_ROGUE);
        $this->assertEquals(4, $rogue->getStrength());
        $this->assertEquals(2, $rogue->getConstitution());
        $this->assertEquals(14, $rogue->getMaxHp());
        $this->assertEquals(7, $rogue->getAttack());
        $this->assertEquals(2.5, $rogue->getDefense());

        // Test pour la classe Mage
        $mage = Character::createFromClass('Mage', Character::CLASS_MAGE);
        $this->assertEquals(5, $mage->getStrength());
        $this->assertEquals(1, $mage->getConstitution());
        $this->assertEquals(12, $mage->getMaxHp());
        $this->assertEquals(8, $mage->getAttack());
        $this->assertEquals(2.0, $mage->getDefense());
    }

    public function testHitChance(): void
    {
        $attacker = new Character('Attaquant', 10, 5);
        $defender = new Character('Défenseur', 5, 5);
        
        $hitChance = $attacker->getHitChance($defender);
        
        // Formule : 75% + (STR - Enemy CON) × 3% + LVL
        // 75 + (10 - 5) * 3 + 1 = 91%
        $this->assertEquals(91, $hitChance);
        
        // Test du minimum (50%)
        $weakAttacker = new Character('Faible', 1, 1);
        $strongDefender = new Character('Fort', 5, 20);
        $this->assertEquals(50, $weakAttacker->getHitChance($strongDefender));
        
        // Test du maximum (95%)
        $strongAttacker = new Character('Fort', 20, 10);
        $weakDefender = new Character('Faible', 5, 5);
        $this->assertEquals(95, $strongAttacker->getHitChance($weakDefender));
    }

    public function testAttack(): void
    {
        $attacker = new Character('Attaquant', 10, 5);
        $defender = new Character('Défenseur', 5, 5);

        $initialHp = $defender->getHp();
        $result = $attacker->attack($defender);

        if ($result['hit']) {
            $this->assertLessThan($initialHp, $defender->getHp());
            $this->assertEquals($initialHp - $result['damage'], $defender->getHp());
        } else {
            $this->assertEquals($initialHp, $defender->getHp());
        }
    }

    public function testGainExperience(): void
    {
        $character = new Character('Test', 10, 10);

        $character->gainExperience(200);

        $this->assertEquals(3, $character->getLevel());
    }

    public function testLevelUpWithChosenAttribute(): void
    {
        $character = new Character('Test', 10, 10);
        $initialStrength = $character->getStrength();
        $initialConstitution = $character->getConstitution();
        
        // Simuler une montée de niveau en choisissant d'augmenter la force
        $character->levelUp('strength');
        
        $this->assertEquals($initialStrength + 1, $character->getStrength());
        $this->assertEquals($initialConstitution, $character->getConstitution());
        // Test montée de niveau en choisissant la constitution
        $initialStrength = $character->getStrength();
        $initialConstitution = $character->getConstitution();
        
        $character->levelUp('constitution');
        
        $this->assertEquals($initialStrength, $character->getStrength());
        $this->assertEquals($initialConstitution + 1, $character->getConstitution());
    }
    
    public function testHandleDeath(): void
    {
        $character = new Character('Test', 10, 10);
        $character->gainExperience(200); // Niveau 3 avec 0 XP
        
        // Simuler une mort
        $character->handleDeath();
        
        // Devrait perdre 33% de son XP actuelle (0 dans ce cas)
        $this->assertEquals(0, $character->getExperience());
        
        // Ajoutons de l'XP et testons à nouveau
        $character->gainExperience(75); // 75 XP
        $character->handleDeath(); // Perte de 25 XP (33% de 75)
        $this->assertEquals(50, $character->getExperience());
    }
    
    public function testExperienceForDefeating(): void
    {
        $character = new Character('Test', 10, 10);
        $enemy = new Character('Ennemi', 5, 5);
        
        // Simuler un niveau différent pour l'ennemi
        $reflection = new \ReflectionClass($enemy);
        $levelProperty = $reflection->getProperty('level');
        $levelProperty->setAccessible(true);
        $levelProperty->setValue($enemy, 3);
        
        // Formule : (Niveau ennemi * 20) + Random(10-30)
        // Mais comme le random n'est pas testable, on utilise une réflexion pour vérifier
        
        $xp = $character->getExperienceForDefeating($enemy);
        
        // L'XP devrait être au moins (3 * 20) + 10 = 70
        $this->assertGreaterThanOrEqual(70, $xp);
        
        // Et au plus (3 * 20) + 30 = 90
        $this->assertLessThanOrEqual(90, $xp);
    }
    
    public function testIsDead(): void
    {
        $character = new Character('Test', 10, 10);
        $this->assertFalse($character->isDead());
        
        // Simuler des dégâts importants
        $reflection = new \ReflectionClass($character);
        $hpProperty = $reflection->getProperty('currentHp');
        $hpProperty->setAccessible(true);
        $hpProperty->setValue($character, 0);
        
        $this->assertTrue($character->isDead());
    }
    
    public function testHeal(): void
    {
        $character = new Character('Test', 10, 10);
        
        // Simuler des dégâts
        $reflection = new \ReflectionClass($character);
        $hpProperty = $reflection->getProperty('currentHp');
        $hpProperty->setAccessible(true);
        $hpProperty->setValue($character, 10);
        
        $character->heal(5);
        $this->assertEquals(15, $character->getHp());
        
        // Test de guérison ne dépassant pas le maximum
        $maxHp = $character->getMaxHp();
        $character->heal($maxHp * 2);
        $this->assertEquals($maxHp, $character->getHp());
    }
    
    public function testFullHeal(): void
    {
        $character = new Character('Test', 10, 10);
        
        // Simuler des dégâts
        $reflection = new \ReflectionClass($character);
        $hpProperty = $reflection->getProperty('currentHp');
        $hpProperty->setAccessible(true);
        $hpProperty->setValue($character, 1);
        
        $character->fullHeal();
        $this->assertEquals($character->getMaxHp(), $character->getHp());
    }
}