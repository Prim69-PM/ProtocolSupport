<?php

namespace Prim\ProtocolSupport;

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class Session {

	public Player $player;
	public int $protocol = ProtocolInfo::CURRENT_PROTOCOL;

	public function __construct(Player $player, int $protocol){
		$this->player = $player;
		$this->protocol = $protocol;
	}

}