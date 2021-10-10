<?php

declare(strict_types=1);

namespace Prim\ProtocolSupport\Protocol\v419;

use pocketmine\network\mcpe\NetworkBinaryStream;
use function count;

final class ItemStackResponse419 {

	public const RESULT_OK = 0;
	public const RESULT_ERROR = 1;
	//TODO: there are a ton more possible result types but we don't need them yet and they are wayyyyyy too many for me
	//to waste my time on right now...

	private int $result;
	private int $requestId;
	/** @var ItemStackResponseContainerInfo419[] */
	private array $containerInfos;

	/**
	 * @param ItemStackResponseContainerInfo419[] $containerInfos
	 */
	public function __construct(int $result, int $requestId, array $containerInfos){
		$this->result = $result;
		$this->requestId = $requestId;
		$this->containerInfos = $containerInfos;
	}

	public function getResult() : int{ return $this->result; }

	public function getRequestId() : int{ return $this->requestId; }

	/** @return ItemStackResponseContainerInfo419[] */
	public function getContainerInfos() : array{ return $this->containerInfos; }

	public static function read(NetworkBinaryStream $in) : self{
		$result = $in->getByte();
		$requestId = $in->readGenericTypeNetworkId();
		$containerInfos = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$containerInfos[] = ItemStackResponseContainerInfo419::read($in);
		}
		return new self($result, $requestId, $containerInfos);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->result);
		$out->writeGenericTypeNetworkId($this->requestId);
		$out->putUnsignedVarInt(count($this->containerInfos));
		foreach($this->containerInfos as $containerInfo){
			$containerInfo->write($out);
		}
	}
}