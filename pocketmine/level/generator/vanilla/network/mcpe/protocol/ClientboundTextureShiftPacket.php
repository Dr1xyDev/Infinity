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
use function count;

class ClientboundTextureShiftPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_TEXTURE_SHIFT_PACKET;

	/** @see TextureShiftAction */
	private int $actionId;
	private string $collectionName;
	private string $fromStep;
	private string $toStep;
	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	private array $allSteps = [];
	private int $currentLengthTicks;
	private int $totalLengthTicks;
	private bool $enabled;

	/**
	 * @generate-create-func
	 * @param string[] $allSteps
	 * @phpstan-param list<string> $allSteps
	 */
	public static function create(
		int $actionId,
		string $collectionName,
		string $fromStep,
		string $toStep,
		array $allSteps,
		int $currentLengthTicks,
		int $totalLengthTicks,
		bool $enabled,
	) : self{
		$result = new self();
		$result->actionId = $actionId;
		$result->collectionName = $collectionName;
		$result->fromStep = $fromStep;
		$result->toStep = $toStep;
		$result->allSteps = $allSteps;
		$result->currentLengthTicks = $currentLengthTicks;
		$result->totalLengthTicks = $totalLengthTicks;
		$result->enabled = $enabled;
		return $result;
	}

	/**
	 * @see TextureShiftAction
	 */
	public function getActionId() : int{ return $this->actionId; }

	public function getCollectionName() : string{ return $this->collectionName; }

	public function getFromStep() : string{ return $this->fromStep; }

	public function getToStep() : string{ return $this->toStep; }

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getAllSteps() : array{ return $this->allSteps; }

	public function getCurrentLengthTicks() : int{ return $this->currentLengthTicks; }

	public function getTotalLengthTicks() : int{ return $this->totalLengthTicks; }

	public function isEnabled() : bool{ return $this->enabled; }

	protected function decodePayload() : void{
		$this->actionId = $this->getByte();
		$this->collectionName = $this->getString();
		$this->fromStep = $this->getString();
		$this->toStep = $this->getString();

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->allSteps[] = $this->getString();
		}

		$this->currentLengthTicks = $this->getUnsignedVarLong();
		$this->totalLengthTicks = $this->getUnsignedVarLong();
		$this->enabled = $this->getBool();
	}

	protected function encodePayload() : void{
		$this->putByte($this->actionId);
		$this->putString($this->collectionName);
		$this->putString($this->fromStep);
		$this->putString($this->toStep);

		$this->putUnsignedVarInt(count($this->allSteps));
		foreach($this->allSteps as $step){
			$this->putString($step);
		}

		$this->putUnsignedVarLong($this->currentLengthTicks);
		$this->putUnsignedVarLong($this->totalLengthTicks);
		$this->putBool($this->enabled);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundTextureShift($this);
	}
}
