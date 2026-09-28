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

namespace pocketmine\level\generator\vanilla\entity;

use Ahc\Json\Comment as CommentedJsonDecoder;
use InvalidArgumentException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin\SerializedSkin;

use function array_keys;
use function chr;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecopyresampled;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefill;
use function imagesavealpha;
use function imagesetpixel;
use function implode;
use function in_array;
use function intdiv;
use function json_encode;
use function ord;
use function strlen;

class Skin
{
	public const ACCEPTED_SKIN_SIZES = [
		64 * 32 * 4,
		64 * 64 * 4,
		128 * 128 * 4,
	];

	private string $skinId;
	private string $skinData;
	private string $capeData;
	private string $geometryName;
	private string $geometryData;
	private SerializedSkin $serializedSkin;
	/** @var string[] */
	private array $skinDataSizeCache = [];

	public function __construct(string $skinId, string $skinData, string $capeData = "", string $geometryName = "", string $geometryData = "")
	{
		$this->skinId = $skinId;
		$this->skinData = $skinData;
		$this->capeData = $capeData;
		$this->geometryName = $geometryName;
		$this->geometryData = $geometryData;
	}

	/**
	 * @deprecated
	 */
	public function isValid() : bool
	{
		try {
			$this->validate();
			return true;
		} catch (InvalidArgumentException $e) {
			return false;
		}
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function validate() : void
	{
		if ($this->skinId === "") {
			throw new InvalidArgumentException("Skin ID must not be empty");
		}
		$len = strlen($this->skinData);
		if (!in_array($len, self::ACCEPTED_SKIN_SIZES, true)) {
			throw new InvalidArgumentException("Invalid skin data size $len bytes (allowed sizes: " . implode(", ", self::ACCEPTED_SKIN_SIZES) . ")");
		}
		if ($this->capeData !== "" && strlen($this->capeData) !== 8192) {
			throw new InvalidArgumentException("Invalid cape data size " . strlen($this->capeData) . " bytes (must be exactly 8192 bytes)");
		}
		//TODO: validate geometry
	}

	public function getSkinId() : string
	{
		return $this->skinId;
	}

	public function getSkinData() : string
	{
		return $this->skinData;
	}

	public function getCapeData() : string
	{
		return $this->capeData;
	}

	public function getGeometryName() : string
	{
		return $this->geometryName;
	}

	public function getGeometryData() : string
	{
		return $this->geometryData;
	}

	public function getSerializedSkin() : SerializedSkin
	{
		return $this->serializedSkin ?? ($this->serializedSkin = SerializedSkin::fromSkin($this));
	}

	public function setSerializedSkin(SerializedSkin $skin) : void
	{
		$this->serializedSkin = $skin;
	}

	public function getSkinDataSize(int $neededSize) : string
	{
		if (!isset($this->skinDataSizeCache[$neededSize])) {
			$this->skinDataSizeCache[$neededSize] = $this->skinResize($this->skinData, $neededSize);
		}

		return $this->skinDataSizeCache[$neededSize];
	}

	/**
	 * Hack to cut down on network overhead due to skins, by un-pretty-printing geometry JSON.
	 *
	 * Mojang, some stupid reason, send every single model for every single skin in the selected skin-pack.
	 * Not only that, they are pretty-printed.
	 * TODO: find out what model crap can be safely dropped from the packet (unless it gets fixed first)
	 */
	public function debloatGeometryData() : void
	{
		if ($this->geometryData !== "") {
			$this->geometryData = (string) json_encode((new CommentedJsonDecoder())->decode($this->geometryData));
		}
	}

	public function getClientFriendlySkinData(int $protocol) : string
	{
		static $sizes = [
			128 * 128 * 4 => ProtocolInfo::PROTOCOL_407,
			64 * 64 * 4 => ProtocolInfo::PROTOCOL_113,
			64 * 32 * 4 => ProtocolInfo::PROTOCOL_113,
		];

		$skinSize = strlen($this->skinData);
		if (isset($sizes[$skinSize])) {
			if ($sizes[$skinSize] > $protocol) {
				foreach ($sizes as $size => $sizeProtocol) {
					if ($protocol >= $sizeProtocol) {
						return $this->getSkinDataSize($size);
					}
				}
			}
		}

		return $this->skinData;
	}

	protected function skinResize(string $skinData, int $neededSize) : string
	{
		$skinSize = strlen($skinData);
		$dimensions = [
			64 * 32 * 4 => [64, 32],
			64 * 64 * 4 => [64, 64],
			128 * 128 * 4 => [128, 128],
		];

		if (!isset($dimensions[$skinSize])) {
			throw new InvalidArgumentException("Invalid skin size $skinSize");
		}

		[$skinW, $skinH] = $dimensions[$skinSize];

		if (!in_array($neededSize, array_keys($dimensions), true)) {
			throw new InvalidArgumentException("Invalid neededSize $neededSize");
		}

		[$newW, $newH] = $dimensions[$neededSize];

		$originalImage = imagecreatetruecolor($skinW, $skinH);
		imagesavealpha($originalImage, true);
		$transColour = imagecolorallocatealpha($originalImage, 0, 0, 0, 127);
		imagefill($originalImage, 0, 0, $transColour);

		$skinPos = 0;
		for ($y = 0; $y < $skinH; $y++) {
			for ($x = 0; $x < $skinW; $x++) {
				$r = ord($skinData[$skinPos]);
				$g = ord($skinData[$skinPos + 1]);
				$b = ord($skinData[$skinPos + 2]);
				$a = ord($skinData[$skinPos + 3]);
				$color = imagecolorallocatealpha($originalImage, $r, $g, $b, 127 - intdiv($a, 2));
				imagesetpixel($originalImage, $x, $y, $color);
				$skinPos += 4;
			}
		}

		$resizedImage = imagecreatetruecolor($newW, $newH);
		imagesavealpha($resizedImage, true);
		imagefill($resizedImage, 0, 0, $transColour);
		imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $newW, $newH, $skinW, $skinH);

		$resizedSkinData = "";
		for ($y = 0; $y < $newH; $y++) {
			for ($x = 0; $x < $newW; $x++) {
				$color = @imagecolorat($resizedImage, $x, $y);
				$a = 127 - (($color >> 24) & 0x7F);
				$r = ($color >> 16) & 0xFF;
				$g = ($color >> 8) & 0xFF;
				$b = $color & 0xFF;
				$resizedSkinData .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}

		imagedestroy($originalImage);
		imagedestroy($resizedImage);

		return $resizedSkinData;
	}
}
