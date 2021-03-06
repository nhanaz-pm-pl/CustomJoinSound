<?php

declare(strict_types=1);

namespace NhanAZ\CustomJoinSound;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use NhanAZ\libRegRsp\libRegRsp;

class Main extends PluginBase implements Listener {

	private libRegRsp $libRegRsp;

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		$this->libRegRsp = new libRegRsp($this);
		$this->libRegRsp->regRsp();
	}

	protected function onDisable(): void {
		$this->libRegRsp->unregRsp();
	}

	private function sendWelcomeSound(Player $player): void {
		$position = $player->getPosition();
		$packet = new PlaySoundPacket();
		$packet->soundName = "CustomJoinSound";
		$packet->x = $position->getX();
		$packet->y = $position->getY();
		$packet->z = $position->getZ();
		$packet->volume = 1;
		$packet->pitch = 1;
		$player->getNetworkSession()->sendDataPacket($packet);
	}

	public function onJoin(PlayerJoinEvent $event) {
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
