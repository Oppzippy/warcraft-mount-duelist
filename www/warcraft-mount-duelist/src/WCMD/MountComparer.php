<?php

namespace WCMD;

class MountComparer {
    private $players = [];
    
    public function addPlayer(array $player) {
        $insertion = [];
        
        foreach ($player['mounts']['collected'] as $mount) {
            $insertion[$mount['name']] = [
                'id' => $mount['spellId'],
                'id_type' => 'spell',
            ];
        }
        
        return array_push($this->players, $insertion) - 1;
    }
    
    public function compare(int $targetId) {
        $target = $this->players[$targetId];
        $players_length = count($this->players);
        for ($i = 0; $i < $players_length; $i++) {
            if ($i == $targetId) {
                continue;
            }
            
            $target = array_diff_key($target, $this->players[$i]);
        }
        
        return $target;
    }
}