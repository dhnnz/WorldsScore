<?php

namespace dhnnz\WorldsScore;

use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;

use pocketmine\event\EventPriority;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

class Loaader extends PluginBase
{

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvent(TagResolveEvent::class, function (TagsResolveEvent $event) {
            $tag = $event->getTag();
            $tags = explode('.', $tag->getName(), 2);
            $value = "0";

            if ($tags[0] !== 'world' || count($tags) < 2) {
                return;
            }

            foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
                if (strtolower($world->getFolderName()) == strtolower(str_replace("_", " ", $tags[1]))) {
                    $value = (string) count($world->getPlayers());
                }
            }

            $tag->setValue((string) $value);
        }, EventPriority::NORMAL, $this);

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                if (!$player->isOnline()) {
                    continue;
                }

                foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
                    (new PlayerTagUpdateEvent($player, new ScoreTag("world." . strtolower(str_replace(" ", "_", $world->getFolderName())), (string) count($world->getPlayers()))))->call();
                }
            }
        }), 20);
    }
}