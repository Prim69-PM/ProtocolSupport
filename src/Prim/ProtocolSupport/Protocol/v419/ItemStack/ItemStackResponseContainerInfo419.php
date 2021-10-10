<?php

declare(strict_types=1);

namespace Prim\ProtocolSupport\Protocol\v419;

use pocketmine\network\mcpe\NetworkBinaryStream;
use function count;

final class ItemStackResponseContainerInfo419 {

	private int $containerId;
	/** @var ItemStackResponseSlotInfo419[] */
	private array $slots;

	/**
	 * @param ItemStackResponseSlotInfo419[] $slots
	 */
	public function __construct(int $containerId, array $slots){
		$this->containerId = $containerId;
		$this->slots = $slots;
	}

	public function getContainerId() : int{ return $this->containerId; }

	/** @return ItemStackResponseSlotInfo419[] */
	public function getSlots() : array{ return $this->slots; }

	public static function read(NetworkBinaryStream $in) : self{
		$containerId = $in->getByte();
		$slots = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$slots[] = ItemStackResponseSlotInfo419::read($in);
		}
		return new self($containerId, $slots);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->containerId);
		$out->putUnsignedVarInt(count($this->slots));
		foreach($this->slots as $slot){
			$slot->write($out);
		}
	}
}
