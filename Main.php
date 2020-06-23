<?php

/**
 * @name ScoreBoard
 * @api 3.10.0
 * @author me
 * @version 1.0.0
 * @main ScoreBoard\Main
 */

namespace ScoreBoard;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\Server;
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {
    public function onEnable() {
        $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            protected $plugin;

            public function __construct(Main $plugin) {
                $this->plugin = $plugin;
            }
            public function addScoreboard(Player $player, string $objectiveName, string $displayName) {
                $pack = new SetDisplayObjectivePacket();
                $pack->displaySlot = "sidebar";
                $pack->objectiveName = $objectiveName;
                $pack->displayName = $displayName;
                $pack->criteriaName = "dummy";
                $pack->sortOrder = 0;
                $player->dataPacket($pack);
            }

            public function setLine(Player $player, string $objectiveName, int $line, string $message) {
                $entry = new ScorePacketEntry();
                $entry->scoreboardId = $line;
                $entry->objectiveName = $objectiveName;
                $entry->score = $line;
                $entry->type = $entry::TYPE_FAKE_PLAYER;
                $entry->customName = $message;
                $pack = new SetScorePacket();
                $pack->type = $pack::TYPE_CHANGE;
                $pack->entries[] = $entry;
                $player->dataPacket($pack);
            }

            public function removeScoreboard(Player $player, string $objectiveName) {
                $pack = new RemoveObjectivePacket();
                $pack->objectiveName = $objectiveName;
                $player->dataPacket($pack);
            }

            public function onRun(int $currentTick) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $this->removeScoreboard($player, "Main");
                    $this->addScoreboard($player, "Main", "§l§e【§f {$player->getName()} §e】");
                    $this->setLine($player, "Main", 1, "§6돈 : §f" . EconomyAPI::getInstance()->myMoney($player));
                    $this->setLine($player, "Main", 2, "§6현재동접 : §f" . count(Server::getInstance()->getOnlinePlayers()));
                    $this->setLine($player, "Main", 3, "§6아이피 : §f" . $player->getAddress());
                }
            }
        }, 25);
    }
}