<?php

namespace Prim\ProtocolSupport\Protocol\v419;

use pocketmine\network\mcpe\NetworkBinaryStream;

class ItemStackResponseSlotInfo419 {

	private int $slot;
	private int $hotbarSlot;
	private int $count;
	private int $itemStackId;

	public function __construct(int $slot, int $hotbarSlot, int $count, int $itemStackId){
		$this->slot = $slot;
		$this->hotbarSlot = $hotbarSlot;
		$this->count = $count;
		$this->itemStackId = $itemStackId;
	}

	public function getSlot() : int{ return $this->slot; }

	public function getHotbarSlot() : int{ return $this->hotbarSlot; }

	public function getCount() : int{ return $this->count; }

	public function getItemStackId() : int{ return $this->itemStackId; }

	public static function read(NetworkBinaryStream $in) : self{
		$slot = $in->getByte();
		$hotbarSlot = $in->getByte();
		$count = $in->getByte();
		$itemStackId = $in->readGenericTypeNetworkId();
		return new self($slot, $hotbarSlot, $count, $itemStackId);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->slot);
		$out->putByte($this->hotbarSlot);
		$out->putByte($this->count);
		$out->writeGenericTypeNetworkId($this->itemStackId);
	}

}