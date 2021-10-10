<?php

namespace Prim\ProtocolSupport\Protocol\v440;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\CommandData;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\utils\BinaryDataException;
use function count;
use function dechex;

class AvailableCommandsPacket440 extends AvailableCommandsPacket {

	public static function from(AvailableCommandsPacket $pk) : self {
		$p = new self;
		$p->commandData = $pk->commandData;
		$p->hardcodedEnums = $pk->hardcodedEnums;
		$p->softEnums = $pk->softEnums;
		$p->enumConstraints = $pk->enumConstraints;
		return $p;
	}

	/**
	 * @param CommandEnum[] $enums
	 * @param string[]      $postfixes
	 *
	 * @throws \UnexpectedValueException
	 * @throws BinaryDataException
	 */
	protected function getCommandData(array $enums, array $postfixes) : CommandData{
		$retval = new CommandData();
		$retval->commandName = $this->getString();
		$retval->commandDescription = $this->getString();
		$retval->flags = (\ord($this->get(1)));
		$retval->permission = (\ord($this->get(1)));
		$retval->aliases = $enums[((\unpack("V", $this->get(4))[1] << 32 >> 32))] ?? null;

		for($overloadIndex = 0, $overloadCount = $this->getUnsignedVarInt(); $overloadIndex < $overloadCount; ++$overloadIndex){
			$retval->overloads[$overloadIndex] = [];
			for($paramIndex = 0, $paramCount = $this->getUnsignedVarInt(); $paramIndex < $paramCount; ++$paramIndex){
				$parameter = new CommandParameter();
				$parameter->paramName = $this->getString();
				$parameter->paramType = ((\unpack("V", $this->get(4))[1] << 32 >> 32));
				$parameter->isOptional = (($this->get(1) !== "\x00"));
				$parameter->flags = (\ord($this->get(1)));

				if(($parameter->paramType & self::ARG_FLAG_ENUM) !== 0){
					$index = ($parameter->paramType & 0xffff);
					$parameter->enum = $enums[$index] ?? null;
					if($parameter->enum === null){
						throw new \UnexpectedValueException("deserializing $retval->commandName parameter $parameter->paramName: expected enum at $index, but got none");
					}
				}elseif(($parameter->paramType & self::ARG_FLAG_POSTFIX) !== 0){
					$index = ($parameter->paramType & 0xffff);
					$parameter->postfix = $postfixes[$index] ?? null;
					if($parameter->postfix === null){
						throw new \UnexpectedValueException("deserializing $retval->commandName parameter $parameter->paramName: expected postfix at $index, but got none");
					}
				}elseif(($parameter->paramType & self::ARG_FLAG_VALID) === 0){
					throw new \UnexpectedValueException("deserializing $retval->commandName parameter $parameter->paramName: Invalid parameter type 0x" . dechex($parameter->paramType));
				}

				$retval->overloads[$overloadIndex][$paramIndex] = $parameter;
			}
		}

		return $retval;
	}

	/**
	 * @param int[]       $enumIndexes string enum name -> int index
	 * @param int[]       $postfixIndexes
	 */
	protected function putCommandData(CommandData $data, array $enumIndexes, array $postfixIndexes) : void{
		$this->putString($data->commandName);
		$this->putString($data->commandDescription);
		($this->buffer .= \chr($data->flags));
		($this->buffer .= \chr($data->permission));

		if($data->aliases !== null){
			($this->buffer .= (\pack("V", $enumIndexes[$data->aliases->enumName] ?? -1)));
		}else{
			($this->buffer .= (\pack("V", -1)));
		}

		$this->putUnsignedVarInt(count($data->overloads));
		foreach($data->overloads as $overload){
			/** @var CommandParameter[] $overload */
			$this->putUnsignedVarInt(count($overload));
			foreach($overload as $parameter){
				$this->putString($parameter->paramName);

				if($parameter->enum !== null){
					$type = self::ARG_FLAG_ENUM | self::ARG_FLAG_VALID | ($enumIndexes[$parameter->enum->enumName] ?? -1);
				}elseif($parameter->postfix !== null){
					$key = $postfixIndexes[$parameter->postfix] ?? -1;
					if($key === -1){
						throw new \InvalidStateException("Postfix '$parameter->postfix' not in postfixes array");
					}
					$type = self::ARG_FLAG_POSTFIX | $key;
				}else{
					$type = $parameter->paramType;
				}

				($this->buffer .= (\pack("V", $type)));
				($this->buffer .= ($parameter->isOptional ? "\x01" : "\x00"));
				($this->buffer .= \chr($parameter->flags));
			}
		}
	}

}