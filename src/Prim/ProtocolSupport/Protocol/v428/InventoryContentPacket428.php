<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use Prim\ProtocolSupport\Protocol\PacketUtils;
use function count;

class InventoryContentPacket428 extends InventoryContentPacket {

	public static function from(InventoryContentPacket $packet) : self {
		$pk = new self;
		$pk->windowId = $packet->windowId;
		$pk->items = PacketUtils::newTo428Wrappers(count($packet->items), $packet);
		return $pk;
	}

	protected function decodePayload(){
		$this->windowId = $this->getUnsignedVarInt();
		$count = $this->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$this->items[] = new ItemStackWrapper($this->readGenericTypeNetworkId(), PacketUtils::getSlot428($this));
		}
	}

	protected function encodePayload(){
		$this->putUnsignedVarInt($this->windowId);
		$this->putUnsignedVarInt(count($this->items));
		foreach($this->items as $item){
			$this->writeGenericTypeNetworkId($item->getStackId());
			PacketUtils::putSlot428($item->getItemStack(), $this);
		}
	}

}