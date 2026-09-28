<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\DataStoreChange;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\DataStoreOperation;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\DataStoreOperationType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\DataStoreRemoval;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\DataStoreUpdate;
use function count;

class ClientboundDataStorePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_DATA_STORE_PACKET;

	/**
	 * @var DataStoreOperation[]
	 * @phpstan-var list<DataStoreOperation>
	 */
	public array $values = [];

	/**
	 * @generate-create-func
	 * @param DataStoreOperation[] $values
	 * @phpstan-param list<DataStoreOperation> $values
	 */
	public static function create(array $values) : self{
		$result = new self();
		$result->values = $values;
		return $result;
	}

	protected function decodePayload() : void{
		$this->values = [];
		for($i = 0, $len = $this->getUnsignedVarInt(); $i < $len; ++$i){
			$this->values[] = match(DataStoreOperationType::fromPacket($this->getUnsignedVarInt())){
				DataStoreOperationType::UPDATE => DataStoreUpdate::read($this),
				DataStoreOperationType::CHANGE => DataStoreChange::read($this),
				DataStoreOperationType::REMOVAL => DataStoreRemoval::read($this),
			};
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->values));
		foreach($this->values as $value){
			$this->putUnsignedVarInt($value->getTypeId()->value);
			$value->write($this);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundDataStore($this);
	}
}
