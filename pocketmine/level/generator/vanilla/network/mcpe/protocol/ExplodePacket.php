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

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

use function count;

class ExplodePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::EXPLODE_PACKET;

	/** @var Vector3 */
	public $position;
	/** @var float */
	public $radius;
	/** @var Vector3[] */
	public $records = [];

	protected function decodePayload() : void
	{
		$this->position = $this->getVector3();
		$this->radius = (float) ($this->getVarInt() / 32);
		$count = $this->getUnsignedVarInt();
		for ($i = 0; $i < $count; ++$i) {
			$x = $y = $z = 0;
			$this->getSignedBlockPosition($x, $y, $z);
			$this->records[$i] = new Vector3($x, $y, $z);
		}
	}

	protected function encodePayload() : void
	{
		$this->putVector3($this->position);
		$this->putVarInt((int) ($this->radius * 32));
		$this->putUnsignedVarInt(count($this->records));
		if (count($this->records) > 0) {
			foreach ($this->records as $record) {
				$this->putSignedBlockPosition((int) $record->x, (int) $record->y, (int) $record->z);
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleExplode($this);
	}
}
