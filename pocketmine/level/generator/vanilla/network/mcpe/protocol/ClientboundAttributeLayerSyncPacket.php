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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AttributeEnvironment;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AttributeLayer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AttributeLayerPayloadType;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\AttributeLayerSettings;

use function count;

class ClientboundAttributeLayerSyncPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_ATTRIBUTE_LAYER_SYNC_PACKET;

	public int $payloadType = AttributeLayerPayloadType::UPDATE_LAYERS;
	/**
	 * @var AttributeLayer[]
	 * @phpstan-var list<AttributeLayer>
	 */
	public array $layers = [];
	public string $layerName = "";
	public int $dimensionId = 0;
	public ?AttributeLayerSettings $settings = null;
	/**
	 * @var AttributeEnvironment[]
	 * @phpstan-var list<AttributeEnvironment>
	 */
	public array $environmentAttributes = [];
	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	public array $removeAttributeNames = [];

	protected function decodePayload() : void{
		$this->payloadType = $this->getUnsignedVarInt();
		$this->layers = [];
		$this->layerName = "";
		$this->dimensionId = 0;
		$this->settings = null;
		$this->environmentAttributes = [];
		$this->removeAttributeNames = [];

		switch($this->payloadType){
			case AttributeLayerPayloadType::UPDATE_LAYERS:
				for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
					$this->layers[] = AttributeLayer::read($this);
				}
				break;
			case AttributeLayerPayloadType::UPDATE_SETTINGS:
				$this->layerName = $this->getString();
				$this->dimensionId = $this->getVarInt();
				$this->settings = AttributeLayerSettings::read($this);
				break;
			case AttributeLayerPayloadType::UPDATE_ENVIRONMENT:
				$this->layerName = $this->getString();
				$this->dimensionId = $this->getVarInt();
				for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
					$this->environmentAttributes[] = AttributeEnvironment::read($this);
				}
				break;
			case AttributeLayerPayloadType::REMOVE_ENVIRONMENT:
				$this->layerName = $this->getString();
				$this->dimensionId = $this->getVarInt();
				for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
					$this->removeAttributeNames[] = $this->getString();
				}
				break;
			default:
				throw new PacketDecodeException("Unknown attribute layer payload type $this->payloadType");
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt($this->payloadType);
		switch($this->payloadType){
			case AttributeLayerPayloadType::UPDATE_LAYERS:
				$this->putUnsignedVarInt(count($this->layers));
				foreach($this->layers as $layer){
					$layer->write($this);
				}
				break;
			case AttributeLayerPayloadType::UPDATE_SETTINGS:
				$this->putString($this->layerName);
				$this->putVarInt($this->dimensionId);
				($this->settings ?? throw new InvalidArgumentException("Settings must be set for attribute layer settings payload"))->write($this);
				break;
			case AttributeLayerPayloadType::UPDATE_ENVIRONMENT:
				$this->putString($this->layerName);
				$this->putVarInt($this->dimensionId);
				$this->putUnsignedVarInt(count($this->environmentAttributes));
				foreach($this->environmentAttributes as $environmentAttribute){
					$environmentAttribute->write($this);
				}
				break;
			case AttributeLayerPayloadType::REMOVE_ENVIRONMENT:
				$this->putString($this->layerName);
				$this->putVarInt($this->dimensionId);
				$this->putUnsignedVarInt(count($this->removeAttributeNames));
				foreach($this->removeAttributeNames as $attributeName){
					$this->putString($attributeName);
				}
				break;
			default:
				throw new InvalidArgumentException("Unknown attribute layer payload type $this->payloadType");
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundAttributeLayerSync($this);
	}
}
