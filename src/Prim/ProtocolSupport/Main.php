<?php

namespace Prim\ProtocolSupport;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\RakLibInterface;
use pocketmine\plugin\PluginBase;
use Prim\ProtocolSupport\Protocol\PacketHandler;
use Prim\ProtocolSupport\Protocol\v419\PacketHandler419;

class Main extends PluginBase {

	/** @var array<string, Session> */
	public array $sessions = [];
	/** @var array<int, PacketHandler> */
	public array $protocols = [];
	/** @var array<string, DataPacket> */
	public array $lastSentPackets = [];

	public static self $instance;

	public function onEnable() : void {
		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		foreach($this->getServer()->getNetwork()->getInterfaces() as $interface){
			if($interface instanceof RakLibInterface){
				Utils::$interface = $interface;
				break;
			}
		}

		$this->protocols = [419 => new PacketHandler419];
	}

	public function getInstance() : self {
		return self::$instance;
	}

}