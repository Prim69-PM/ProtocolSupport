<?php

namespace Prim\ProtocolSupport\Protocol;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;

abstract class PacketHandler {

	/**
	 * @param Player $player
	 * @param DataPacket $packet
	 * @return bool
	 * Returns true if a packet has been handled
	 */
	abstract function handleInbound(Player $player, DataPacket $packet) : bool;

	/**
	 * @param Player $player
	 * @param DataPacket $packet
	 * @return bool
	 * Returns true if a packet has been handled
	 */
	abstract function handleOutbound(Player $player, DataPacket $packet) : bool;

}