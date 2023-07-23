<?php

declare(strict_types=1);

namespace NhanAZ\CustomJoinSound;

use NhanAZ\libBedrock\ResourcePackManager;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class Main extends PluginBase implements Listener {

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		ResourcePackManager::registerResourcePack($this);
	}

	protected function onDisable(): void {
		ResourcePackManager::unRegisterResourcePack($this);
	}

	private function sendWelcomeSound(Player $player): void {
		$position = $player->getPosition();
		$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create(
			soundName: "CustomJoinSound",
			x: $position->getX(),
			y: $position->getY(),
			z: $position->getZ(),
			volume: 1.0,
			pitch: 1.0
		));
	}

	public function onJoin(PlayerJoinEvent $event): void {
		$player = $event->getPlayer();
		if ($this->getConfig()->get("onlyFirstTime", false)) {
			if (!$event->getPlayer()->hasPlayedBefore()) {
				$this->sendWelcomeSound($player);
			}
		} else {
			$this->sendWelcomeSound($player);
		}
	}
}
