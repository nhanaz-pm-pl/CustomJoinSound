<?php

declare(strict_types=1);

namespace NhanAZ\libRegRsp;

use pocketmine\plugin\PluginBase;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\resourcepacks\ZippedResourcePack;
use pocketmine\utils\Filesystem;
use ReflectionClass;
use Webmozart\PathUtil\Path;
use ZipArchive;
use function array_search;
use function mb_strtolower;
use function preg_replace;
use function str_contains;
use function unlink;

class libRegRsp {
	private static ?ResourcePack $pack = null;

	public function __construct(
		private PluginBase $plugin
	) {
	}

	public function regRsp() {
		// Compile resource pack
		$zip = new ZipArchive();
		$zip->open(Path::join($this->plugin->getDataFolder(), $this->plugin->getName() . '.mcpack'), ZipArchive::CREATE | ZipArchive::OVERWRITE);
		foreach ($this->plugin->getResources() as $resource) {
			if ($resource->isFile() and str_contains($resource->getPathname(), $this->plugin->getName() . ' Pack')) {
				$relativePath = Path::normalize(preg_replace("/.*[\/\\\\]{$this->plugin->getName()}\hPack[\/\\\\].*/U", '', $resource->getPathname()));
				$this->plugin->saveResource(Path::join($this->plugin->getName() . ' Pack', $relativePath), false);
				$zip->addFile(Path::join($this->plugin->getDataFolder(), $this->plugin->getName() . ' Pack', $relativePath), $relativePath);
			}
		}
		$zip->close();
		Filesystem::recursiveUnlink(Path::join($this->plugin->getDataFolder() . $this->plugin->getName() . ' Pack'));
		$this->plugin->getLogger()->debug('Resource pack compiled');

		// Register resource pack
		$this->registerResourcePack(self::$pack = new ZippedResourcePack(Path::join($this->plugin->getDataFolder(), $this->plugin->getName() . '.mcpack')));
		$this->plugin->getLogger()->debug('Resource pack registered');
	}

	public function unRegRsp() {
		$manager = $this->plugin->getServer()->getResourcePackManager();
		$pack = self::$pack;

		$reflection = new ReflectionClass($manager);

		$property = $reflection->getProperty("resourcePacks");
		$property->setAccessible(true);
		$currentResourcePacks = $property->getValue($manager);
		$key = array_search($pack, $currentResourcePacks, true);
		if ($key !== false) {
			unset($currentResourcePacks[$key]);
			$property->setValue($manager, $currentResourcePacks);
		}

		$property = $reflection->getProperty("uuidList");
		$property->setAccessible(true);
		$currentUUIDPacks = $property->getValue($manager);
		if (isset($currentResourcePacks[mb_strtolower($pack->getPackId())])) {
			unset($currentUUIDPacks[mb_strtolower($pack->getPackId())]);
			$property->setValue($manager, $currentUUIDPacks);
		}
		$this->plugin->getLogger()->debug('Resource pack unregistered');

		unlink(Path::join($this->plugin->getDataFolder(), $this->plugin->getName() . '.mcpack'));
		$this->plugin->getLogger()->debug('Resource pack file deleted');
	}

	private function registerResourcePack(ResourcePack $pack) {
		$manager = $this->plugin->getServer()->getResourcePackManager();

		$reflection = new ReflectionClass($manager);

		$property = $reflection->getProperty("resourcePacks");
		$property->setAccessible(true);
		$currentResourcePacks = $property->getValue($manager);
		$currentResourcePacks[] = $pack;
		$property->setValue($manager, $currentResourcePacks);

		$property = $reflection->getProperty("uuidList");
		$property->setAccessible(true);
		$currentUUIDPacks = $property->getValue($manager);
		$currentUUIDPacks[mb_strtolower($pack->getPackId())] = $pack;
		$property->setValue($manager, $currentUUIDPacks);

		$property = $reflection->getProperty("serverForceResources");
		$property->setAccessible(true);
		$property->setValue($manager, true);
	}
}
