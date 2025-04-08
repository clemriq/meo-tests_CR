<?php

namespace App\tests\unit\entity;

use App\Entity\Quest;
use App\Entity\QuestObjective;
use PHPUnit\Framework\TestCase;

class QuestObjectiveTest extends TestCase
{
    private QuestObjective $objective;
    
    protected function setUp(): void
    {
        $this->objective = new QuestObjective(
            'Tuer 5 rats',
            QuestObjective::TYPE_KILL_ENEMY,
            'rat',
            5
        );
    }
    
    public function testConstructorAndGetters(): void
    {
        $this->assertEquals('Tuer 5 rats', $this->objective->getDescription());
        $this->assertEquals(QuestObjective::TYPE_KILL_ENEMY, $this->objective->getType());
        $this->assertEquals('rat', $this->objective->getTargetId());
        $this->assertEquals(5, $this->objective->getRequiredAmount());
        $this->assertEquals(0, $this->objective->getCurrentAmount());
        $this->assertFalse($this->objective->isCompleted());
        $this->assertNull($this->objective->getQuest());
    }
    
    public function testSetters(): void
    {
        $this->objective->setDescription('Tuer 10 gobelins')
            ->setType(QuestObjective::TYPE_KILL_ENEMY)
            ->setTargetId('goblin')
            ->setRequiredAmount(10)
            ->setCurrentAmount(5);
            
        $this->assertEquals('Tuer 10 gobelins', $this->objective->getDescription());
        $this->assertEquals(QuestObjective::TYPE_KILL_ENEMY, $this->objective->getType());
        $this->assertEquals('goblin', $this->objective->getTargetId());
        $this->assertEquals(10, $this->objective->getRequiredAmount());
        $this->assertEquals(5, $this->objective->getCurrentAmount());
    }
    
    public function testIncrementProgress(): void
    {
        $this->objective->incrementProgress();
        $this->assertEquals(1, $this->objective->getCurrentAmount());
        
        $this->objective->incrementProgress(3);
        $this->assertEquals(4, $this->objective->getCurrentAmount());
    }
    
    public function testCompletionCheck(): void
    {
        $this->assertFalse($this->objective->isCompleted());
        
        $this->objective->incrementProgress(4);
        $this->assertFalse($this->objective->isCompleted());
        
        $this->objective->incrementProgress(1);
        $this->assertTrue($this->objective->isCompleted());
    }
    
    public function testSetQuest(): void
    {
        $quest = new Quest('Quête de test', 'Description', 100, 50, 1);
        $this->objective->setQuest($quest);
        
        $this->assertSame($quest, $this->objective->getQuest());
    }
    
    public function testObjectiveCompletionUpdatesQuest(): void
    {
        $quest = new Quest('Quête de test', 'Description', 100, 50, 1);
        $this->objective->setQuest($quest);
        $quest->addObjective($this->objective);
        
        $this->assertFalse($quest->isCompleted());
        
        // Compléter l'objectif
        $this->objective->incrementProgress(5);
        
        // La quête devrait être marquée comme complétée
        $this->assertTrue($quest->isCompleted());
    }
    
    public function testMultipleObjectivesQuest(): void
    {
        $quest = new Quest('Quête de test', 'Description', 100, 50, 1);
        
        $objective1 = $this->objective;
        $objective1->setQuest($quest);
        $quest->addObjective($objective1);
        
        $objective2 = new QuestObjective(
            'Collecter 3 herbes',
            QuestObjective::TYPE_COLLECT_ITEM,
            'herb',
            3
        );
        $objective2->setQuest($quest);
        $quest->addObjective($objective2);
        
        // Compléter seulement le premier objectif
        $objective1->incrementProgress(5);
        $this->assertFalse($quest->isCompleted());
        
        // Compléter le deuxième objectif
        $objective2->incrementProgress(3);
        $this->assertTrue($quest->isCompleted());
    }
}