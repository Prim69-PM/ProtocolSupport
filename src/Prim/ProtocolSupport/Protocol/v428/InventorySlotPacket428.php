<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use Prim\ProtocolSupport\Protocol\PacketUtils;

class InventorySlotPacket428 extends InventorySlotPacket {

	protected function decodePayload(){
		$this->windowId = $this->getUnsignedVarInt();
		$this->inventorySlot = $this->getUnsignedVarInt();
		$this->item = new ItemStackWrapper($this->readGenericTypeNetworkId(), PacketUtils::getSlot428($this));
	}

	protected function encodePayload(){
		$this->putUnsignedVarInt($this->windowId);
		$this->putUnsignedVarInt($this->inventorySlot);
		$this->writeGenericTypeNetworkId($this->item->getStackId());
		PacketUtils::putSlot428($this->item->getItemStack(), $this);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleInventorySlot($this);
	}

}