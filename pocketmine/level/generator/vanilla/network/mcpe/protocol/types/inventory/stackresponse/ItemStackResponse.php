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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\stackresponse;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

use function count;

final class ItemStackResponse
{
	public const RESULT_OK = 0;
	public const RESULT_ERROR = 1;
	//TODO: there are a ton more possible result types but we don't need them yet and they are wayyyyyy too many for me
	//to waste my time on right now...

	/**
	 * @param ItemStackResponseContainerInfo[] $containerInfos
	 */
	public function __construct(
		private int $result,
		private int $requestId,
		private array $containerInfos = []
	) {
		if ($this->result !== self::RESULT_OK && count($this->containerInfos) !== 0) {
			throw new \InvalidArgumentException("Container infos must be empty if rejecting the request");
		}
	}

	public function getResult() : int
	{
		return $this->result;
	}

	public function getRequestId() : int
	{
		return $this->requestId;
	}

	/** @return ItemStackResponseContainerInfo[] */
	public function getContainerInfos() : array
	{
		return $this->containerInfos;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$result = $in->getByte();
		$requestId = $in->readItemStackRequestId();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40 the container infos are an optional list (wrapped in a dummy optional until 1.26.50)
			$containerInfos = $in->getProtocolOptional(fn() => $in->getList(fn() => ItemStackResponseContainerInfo::read($in), 256)) ?? [];
			return new self($result, $requestId, $containerInfos);
		}
		$containerInfos = [];
		if($result === self::RESULT_OK) {
			for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
				$containerInfos[] = ItemStackResponseContainerInfo::read($in);
			}
		}
		return new self($result, $requestId, $containerInfos);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putByte($this->result);
		$out->writeItemStackRequestId($this->requestId);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putProtocolOptional($this->result === self::RESULT_OK ? $this->containerInfos : null, fn(array $infos) => $out->putList($infos, fn(ItemStackResponseContainerInfo $info) => $info->write($out)));
			return;
		}
		if($this->result === self::RESULT_OK) {
			$out->putUnsignedVarInt(count($this->containerInfos));
			foreach ($this->containerInfos as $containerInfo) {
				$containerInfo->write($out);
			}
		}
	}
}
