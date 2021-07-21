<?php

namespace Prim\ProtocolSupport;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\RakLibInterface;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\timings\Timings;
use Prim\ProtocolSupport\Protocol\PacketHandler;
use Prim\ProtocolSupport\Protocol\v419\PacketHandler419;
use function get_class;

class Main extends PluginBase {

	/** @var array<string, Session> */
	public array $sessions = [];
	/** @var array<int, PacketHandler> */
	public array $protocols = [];

	public static self $instance;
	public RakLibInterface $interface;

	public function onEnable() : void {
		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		foreach($this->getServer()->getNetwork()->getInterfaces() as $interface){
			if($interface instanceof RakLibInterface){
				$this->interface = $interface;
				break;
			}
		}

		$this->protocols = [419 => new PacketHandler419];
	}

	public function sendSilentPacket(Player $player, DataPacket $packet, bool $needACK = false, bool $immediate = false) : bool {
		if(!$player->isConnected()) return false;

		if(!$player->loggedIn && !$packet->canBeSentBeforeLogin()){
			throw new InvalidArgumentException('Attempted to send ' . get_class($packet) . ' to ' . $this->getName() . ' too early');
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try {
			$identifier = $this->interface->putPacket($player, $packet, $needACK, $immediate);

			if($needACK and $identifier !== null){
				// $this->needACK[$identifier] = false; pretty sure this is useless?
				return $identifier;
			}

			return true;
		} finally {
			$timings->stopTiming();
		}
	}

	public function getInstance() : self {
		return self::$instance;
	}

}