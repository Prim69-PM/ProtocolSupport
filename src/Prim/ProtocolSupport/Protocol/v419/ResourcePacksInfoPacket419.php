<?php

namespace Prim\ProtocolSupport\Protocol\v419;

use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;

class ResourcePacksInfoPacket419 extends ResourcePacksInfoPacket {

	protected function decodePayload(){
		$this->mustAccept = $this->getBool();
		$this->hasScripts = $this->getBool();
		$behaviorPackCount = $this->getLShort();
		while($behaviorPackCount-- > 0){
			$this->getString();
			$this->getString();
			$this->getLLong();
			$this->getString();
			$this->getString();
			$this->getString();
			$this->getBool();
		}

		$resourcePackCount = $this->getLShort();
		while($resourcePackCount-- > 0){
			$this->getString();
			$this->getString();
			$this->getLLong();
			$this->getString();
			$this->getString();
			$this->getString();
			$this->getBool();
		}
	}

	protected function encodePayload(){
		$this->putBool($this->mustAccept);
		$this->putBool($this->hasScripts);
		$this->putLShort(count($this->behaviorPackEntries));
		foreach($this->behaviorPackEntries as $entry){
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			$this->putLLong($entry->getPackSize());
			$this->putString(''); //TODO: encryption key
			$this->putString(''); //TODO: subpack name
			$this->putString(''); //TODO: content identity
			$this->putBool(false); //TODO: has scripts (?)
		}
		$this->putLShort(count($this->resourcePackEntries));
		foreach($this->resourcePackEntries as $entry){
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			$this->putLLong($entry->getPackSize());
			$this->putString(''); //TODO: encryption key
			$this->putString(''); //TODO: subpack name
			$this->putString(''); //TODO: content identity
			$this->putBool(false); //TODO: seems useless for resource packs
		}
	}


}