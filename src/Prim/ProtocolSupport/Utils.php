<?php

namespace Prim\ProtocolSupport;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\RakLibInterface;
use pocketmine\Player;
use pocketmine\timings\Timings;
use function get_class;

class Utils {

	public static RakLibInterface $interface;
	public static bool $customPacket = false;

	public static function sendSilentPacket(Player $player, DataPacket $packet, bool $needACK = false, bool $immediate = false) : bool {
		self::$customPacket = true;
		if(!$player->isConnected()) return false;

		if(!$player->loggedIn && !$packet->canBeSentBeforeLogin()){
			throw new InvalidArgumentException('Attempted to send ' . get_class($packet) . ' to ' . $player->getName() . ' too early');
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try {
			$identifier = self::$interface->putPacket($player, $packet, $needACK, $immediate);
			if($needACK and $identifier !== null) return $identifier;
			return true;
		} finally {
			$timings->stopTiming();
		}
	}

}