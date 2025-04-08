<?php
namespace App\Service;

use App\Entity\Character;

class CombatSystem
{
    /**
     * Simule un tour de combat entre deux personnages
     * @return array Information sur le tour [attacker, defender, hit, damage, defenderRemaining]
     */
    public function processTurn(Character $attacker, Character $defender): array
    {
        $result = $attacker->attack($defender);
        
        return [
            'attacker' => $attacker->getName(),
            'defender' => $defender->getName(),
            'hit' => $result['hit'],
            'damage' => $result['damage'],
            'defenderRemaining' => $defender->getHp()
        ];
    }
    
    /**
     * Simule un combat complet jusqu'à ce qu'un des personnages meure
     * @return array Historique du combat et résultat final
     */
    public function simulateCombat(Character $character1, Character $character2): array
    {
        $turnHistory = [];
        $currentTurn = 1;
        $attacker = $character1;
        $defender = $character2;
        
        // Assure-toi que les personnages sont en bonne santé avant le combat
        $character1->fullHeal();
        $character2->fullHeal();
        
        while (!$character1->isDead() && !$character2->isDead()) {
            // Simuler un tour
            $turnResult = $this->processTurn($attacker, $defender);
            $turnResult['turn'] = $currentTurn;
            $turnHistory[] = $turnResult;
            
            // Si le défenseur est mort, on sort de la boucle
            if ($defender->isDead()) {
                break;
            }
            
            // Inverser les rôles pour le prochain tour
            $temp = $attacker;
            $attacker = $defender;
            $defender = $temp;
            
            $currentTurn++;
        }
        
        // Déterminer le vainqueur
        $winner = null;
        $loser = null;
        
        if ($character1->isDead()) {
            $winner = $character2;
            $loser = $character1;
        } else {
            $winner = $character1;
            $loser = $character2;
        }
        
        // Appliquer les conséquences du combat
        $xpGained = $winner->getExperienceForDefeating($loser);
        $winner->gainExperience($xpGained);
        $loser->handleDeath(); // Perte de 33% XP
        
        return [
            'history' => $turnHistory,
            'winner' => $winner->getName(),
            'loser' => $loser->getName(),
            'xpGained' => $xpGained,
            'winnerLevel' => $winner->getLevel(),
            'winnerXp' => $winner->getExperience()
        ];
    }
}