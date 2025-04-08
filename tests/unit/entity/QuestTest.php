<?php

namespace App\tests\unit\entity;

use App\Entity\Character;
use App\Entity\Quest;
use App\Entity\QuestObjective;
use PHPUnit\Framework\TestCase;

class QuestTest extends TestCase
{
    private Quest $quest;
    private Character $character;
    
    protected function setUp(): void
    {
        $this->quest = new Quest('Quête de test', 'Description de la quête', 100, 50, 1);
        $this->character = new Character('Aventurier', 10, 10);
    }
    
    public function testConstructorAndGetters(): void
    {
        $this->assertEquals('Quête de test', $this->quest->getTitle());
        $this->assertEquals('Description de la quête', $this->quest->getDescription());
        $this->assertEquals(100, $this->quest->getExperienceReward());
        $this->assertEquals(50, $this->quest->getGoldReward());
        $this->assertEquals(1, $this->quest->getRequiredLevel());
        $this->assertFalse($this->quest->isCompleted());
    }
    
    public function testSetters(): void
    {
        $this->quest->setTitle('Nouvelle quête')
            ->setDescription('Nouvelle description')
            ->setExperienceReward(200)
            ->setGoldReward(100)
            ->setRequiredLevel(2)
            ->setCompleted(true);
            
        $this->assertEquals('Nouvelle quête', $this->quest->getTitle());
        $this->assertEquals('Nouvelle description', $this->quest->getDescription());
        $this->assertEquals(200, $this->quest->getExperienceReward());
        $this->assertEquals(100, $this->quest->getGoldReward());
        $this->assertEquals(2, $this->quest->getRequiredLevel());
        $this->assertTrue($this->quest->isCompleted());
    }
    
    public function testAssignCharacter(): void
    {
        $this->quest->assignCharacter($this->character);
        
        $this->assertCount(1, $this->quest->getAssignedCharacters());
        $this->assertTrue($this->quest->getAssignedCharacters()->contains($this->character));
        
        // Tester qu'on ne peut pas ajouter deux fois le même personnage
        $this->quest->assignCharacter($this->character);
        $this->assertCount(1, $this->quest->getAssignedCharacters());
    }
    
    public function testRemoveCharacter(): void
    {
        $this->quest->assignCharacter($this->character);
        $this->quest->removeCharacter($this->character);
        
        $this->assertCount(0, $this->quest->getAssignedCharacters());
    }
    
    public function testAddObjective(): void
    {
        $objective = new QuestObjective(
            'Tuer 5 rats', 
            QuestObjective::TYPE_KILL_ENEMY, 
            'rat', 
            5
        );
        
        $this->quest->addObjective($objective);
        
        $this->assertCount(1, $this->quest->getObjectives());
        $this->assertSame($this->quest, $objective->getQuest());
    }
    
    public function testRemoveObjective(): void
    {
        $objective = new QuestObjective(
            'Tuer 5 rats', 
            QuestObjective::TYPE_KILL_ENEMY, 
            'rat', 
            5
        );
        
        $this->quest->addObjective($objective);
        $this->quest->removeObjective($objective);
        
        $this->assertCount(0, $this->quest->getObjectives());
        $this->assertNull($objective->getQuest());
    }
    
    public function testCheckCompletion(): void
    {
        $objective1 = new QuestObjective(
            'Tuer 5 rats', 
            QuestObjective::TYPE_KILL_ENEMY, 
            'rat', 
            5
        );
        
        $objective2 = new QuestObjective(
            'Collecter 3 herbes', 
            QuestObjective::TYPE_COLLECT_ITEM, 
            'herb', 
            3
        );
        
        $this->quest->addObjective($objective1);
        $this->quest->addObjective($objective2);
        
        // La quête ne devrait pas être complétée tant que tous les objectifs ne sont pas complétés
        $this->assertFalse($this->quest->checkCompletion());
        $this->assertFalse($this->quest->isCompleted());
        
        // Compléter le premier objectif
        $objective1->incrementProgress(5);
        $this->assertFalse($this->quest->isCompleted());
        
        // Compléter le deuxième objectif
        $objective2->incrementProgress(3);
        
        // Maintenant la quête devrait être complétée
        $this->assertTrue($this->quest->checkCompletion());
        $this->assertTrue($this->quest->isCompleted());
    }
    
    public function testCompleteQuest(): void
    {
        $objective = new QuestObjective(
            'Tuer 5 rats', 
            QuestObjective::TYPE_KILL_ENEMY, 
            'rat', 
            5
        );
        
        $this->quest->addObjective($objective);
        
        // Compléter l'objectif
        $objective->incrementProgress(5);
        
        $initialXp = $this->character->getExperience();
        
        // Compléter la quête
        $this->quest->completeQuest($this->character);
        
        // Le personnage devrait avoir gagné l'XP de la quête
        $this->assertEquals($initialXp + 100, $this->character->getExperience());
    }
}