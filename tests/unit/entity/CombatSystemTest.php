<?php

namespace App\tests\unit\service;

use App\Entity\Character;
use App\Service\CombatSystem;
use PHPUnit\Framework\TestCase;

class CombatSystemTest extends TestCase
{
    private CombatSystem $combatSystem;
    
    protected function setUp(): void
    {
        $this->combatSystem = new CombatSystem();
    }
    
    public function testProcessTurn(): void
    {
        $attacker = new Character('Attaquant', 10, 5);
        $defender = new Character('Défenseur', 5, 5);
        
        $initialHp = $defender->getHp();
        $result = $this->combatSystem->processTurn($attacker, $defender);
        
        $this->assertEquals('Attaquant', $result['attacker']);
        $this->assertEquals('Défenseur', $result['defender']);
        $this->assertIsBool($result['hit']);
        
        if ($result['hit']) {
            $this->assertGreaterThan(0, $result['damage']);
            $this->assertLessThan($initialHp, $result['defenderRemaining']);
        } else {
            $this->assertEquals(0, $result['damage']);
            $this->assertEquals($initialHp, $result['defenderRemaining']);
        }
    }
    
    public function testSimulateCombat(): void
    {
        // Créer deux personnages avec des statistiques qui garantissent un résultat prévisible
        $character1 = new Character('Fort', 20, 20);
        $character2 = new Character('Faible', 3, 3);
        
        $result = $this->combatSystem->simulateCombat($character1, $character2);
        
        // Vérifier la structure du résultat
        $this->assertArrayHasKey('history', $result);
        $this->assertArrayHasKey('winner', $result);
        $this->assertArrayHasKey('loser', $result);
        $this->assertArrayHasKey('xpGained', $result);
        $this->assertArrayHasKey('winnerLevel', $result);
        $this->assertArrayHasKey('winnerXp', $result);
        
        // Avec cette configuration, le personnage fort devrait gagner
        $this->assertEquals('Fort', $result['winner']);
        $this->assertEquals('Faible', $result['loser']);
        
        // L'historique devrait contenir au moins un tour
        $this->assertNotEmpty($result['history']);
        
        // Vérifier qu'au moins un personnage a été tué
        $this->assertTrue($character2->isDead());
    }
    
    public function testCombatResultsInExperienceGain(): void
    {
        $character1 = new Character('Fort', 20, 20);
        $initialXp = $character1->getExperience();
        $character2 = new Character('Faible', 3, 3);
        
        $result = $this->combatSystem->simulateCombat($character1, $character2);
        
        // Le vainqueur devrait avoir gagné de l'XP
        $this->assertGreaterThan($initialXp, $character1->getExperience());
        $this->assertEquals($character1->getExperience(), $initialXp + $result['xpGained']);
    }
    
    public function testCombatResultsInXpLossForLoser(): void
    {
        // Dans ce test, on va s'assurer que le perdant perd bien 33% de son XP
        $character1 = new Character('Fort', 20, 20);
        $character2 = new Character('Faible', 3, 3);
        
        // Donner de l'XP au personnage faible
        $character2->gainExperience(60);
        $initialXp = $character2->getExperience();
        
        $this->combatSystem->simulateCombat($character1, $character2);
        
        // Le perdant devrait avoir perdu 33% de son XP
        $expectedXpAfterLoss = floor($initialXp * 0.67);
        $this->assertEquals($expectedXpAfterLoss, $character2->getExperience());
    }
}