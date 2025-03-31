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
        if ($reflection->hasProperty('level')) {
            $levelProperty = $reflection->getProperty('level');
            $levelProperty->setAccessible(true);
            $levelProperty->setValue($character, $level);
        } else {
            $this->markTestSkipped("La propriété 'level' n'existe pas encore dans la classe Character");
        }

        $this->assertEquals($expectedHp, $character->getHp());
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
        if ($reflection->hasProperty('level')) {
            $levelProperty = $reflection->getProperty('level');
            $levelProperty->setAccessible(true);
            $levelProperty->setValue($character, $level);
        } else {
            $this->markTestSkipped("La propriété 'level' n'existe pas encore dans la classe Character");
        }

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
        if ($reflection->hasProperty('level')) {
            $levelProperty = $reflection->getProperty('level');
            $levelProperty->setAccessible(true);
            $levelProperty->setValue($character, $level);
        } else {
            $this->markTestSkipped("La propriété 'level' n'existe pas encore dans la classe Character");
        }

        $this->assertEquals($expectedDefense, $character->getDefense());
    }

    public function defenseDataProvider(): array
    {
        return [
            // Formule Defense = 1 + (CON × 0.5) + (LVL x 0.5)
            'Niveau 1, Constitution basse' => [3, 1, 3.0],
            'Niveau 1, Constitution moyenne' => [10, 1, 6.5],
            'Niveau 1, Constitution haute' => [15, 1, 9.0],
            'Niveau 5, Constitution moyenne' => [10, 5, 8.5],
        ];
    }

    public function testClassPresets(): void
    {
        // Test pour la classe Warrior (STR=3, CON=3) au niveau 1
        $warrior = new Character('Warrior', 3, 3);
        $this->assertEquals(18, $warrior->getHp());
        $this->assertEquals(6, $warrior->getAttack());
        $this->assertEquals(3, $warrior->getDefense());

        // Test pour la classe Rogue (STR=4, CON=2) au niveau 1
        $rogue = new Character('Rogue', 4, 2);
        $this->assertEquals(16, $rogue->getHp());
        $this->assertEquals(7, $rogue->getAttack());
        $this->assertEquals(2.5, $rogue->getDefense());

        // Test pour la classe Mage (STR=5, CON=1) au niveau 1
        $mage = new Character('Mage', 5, 1);
        $this->assertEquals(14, $mage->getHp());
        $this->assertEquals(8, $mage->getAttack());
        $this->assertEquals(2.0, $mage->getDefense());
    }

    public function testAttack(): void
    {
        $attacker = new Character('Attaquant', 10, 5);
        $defender = new Character('Défenseur', 5, 5);

        $initialHp = $defender->getHp();
        $attacker->attack($defender);

        $this->assertLessThan($initialHp, $defender->getHp());
    }

    public function testGainExperience(): void
    {
        $character = new Character('Test', 10, 10);

        $character->gainExperience(200);

        $this->assertEquals(3, $character->getLevel());
    }
}

