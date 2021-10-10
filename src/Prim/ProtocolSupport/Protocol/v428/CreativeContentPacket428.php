<?php

namespace Prim\ProtocolSupport\Protocol\v428;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use Prim\ProtocolSupport\Protocol\PacketUtils;
use function count;

class CreativeContentPacket428 extends CreativeContentPacket {

	public const NETWORK_ID = ProtocolInfo::CREATIVE_CONTENT_PACKET;

	/** @var CreativeContentEntry[] */
	public array $entries;

	/**
	 * @param CreativeContentEntry[] $entries
	 */
	public static function create(array $entries) : self{
		$result = new self;
		$result->entries = $entries;
		return $result;
	}

	/** @return CreativeContentEntry[] */
	public function getEntries() : array{ return $this->entries; }

	protected function decodePayload() : void{
		$this->entries = [];
		for($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i){
			$this->entries[] = new CreativeContentEntry($this->readGenericTypeNetworkId(), PacketUtils::getSlot428($this));
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $entry){
			$this->writeGenericTypeNetworkId($entry->getEntryId());
			PacketUtils::putSlot428($entry->getItem(), $this);
		}
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleCreativeContent($this);
	}

}