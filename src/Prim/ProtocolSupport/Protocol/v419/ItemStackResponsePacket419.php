<?php

namespace Prim\ProtocolSupport\Protocol\v419;

use pocketmine\network\mcpe\protocol\ItemStackResponsePacket;
use function count;

class ItemStackResponsePacket419 extends ItemStackResponsePacket {

	/** @var ItemStackResponse419[] */
	public array $responses;

	/**
	 * @param ItemStackResponse419[] $responses
	 */
	public static function create(array $responses) : self{
		$result = new self;
		$result->responses = $responses;
		return $result;
	}

	/** @return ItemStackResponse419[] */
	public function getResponses() : array{ return $this->responses; }

	protected function decodePayload() : void {
		$this->responses = [];
		for($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i){
			$this->responses[] = ItemStackResponse419::read($this);
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->responses));
		foreach($this->responses as $response){
			$response->write($this);
		}
	}

}