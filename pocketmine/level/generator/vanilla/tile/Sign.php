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

namespace pocketmine\level\generator\vanilla\tile;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\event\block\SignChangeEvent;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Binary;
use pocketmine\level\generator\vanilla\utils\Color;
use pocketmine\level\generator\vanilla\utils\TextFormat;
use UnexpectedValueException;

use function array_map;
use function array_pad;
use function array_slice;
use function explode;
use function get_class;
use function gettype;
use function implode;
use function is_object;
use function mb_check_encoding;
use function mb_scrub;
use function sprintf;
use function strlen;

class Sign extends Spawnable
{
	public const TAG_TEXT_BLOB = "Text";
	public const TAG_TEXT_LINE = "Text%d"; //sprintf()able
	public const TAG_TEXT_COLOR = "SignTextColor";
	public const TAG_GLOWING_TEXT = "IgnoreLighting";
	public const TAG_PERSIST_FORMATTING = "PersistFormatting"; //TAG_Byte
	/**
	 * This tag is set to indicate that MCPE-117835 has been addressed in whatever version this sign was created.
	 * @see https://bugs.mojang.com/browse/MCPE-117835
	 */
	public const TAG_LEGACY_BUG_RESOLVE = "TextIgnoreLegacyBugResolved";

	public const TAG_FRONT_TEXT = "FrontText";
	public const TAG_BACK_TEXT = "BackText"; //TAG_Compound
	public const TAG_WAXED = "IsWaxed"; //TAG_Byte
	public const TAG_LOCKED_FOR_EDITING_BY = "LockedForEditingBy"; //TAG_Long

	private static function fixTextBlob(string $blob) : array
	{
		return array_slice(array_pad(explode("\n", $blob, 5), 4, ""), 0, 4);
	}

	/** @var string[] */
	protected $text = ["", "", "", ""];

	/** @var ?int */
	protected $editorEntityRuntimeId = null;

	protected Color $textColor;
	private bool $glowing;

	public function __construct(ChunkManager $level, CompoundTag $nbt, bool $glowing = false){
		parent::__construct($level, $nbt);

		$this->glowing = $glowing;
	}

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->textColor = new Color(0, 0, 0);
		if ($nbt->hasTag(self::TAG_TEXT_COLOR)) {
			if (($baseColor = $nbt->getTag(self::TAG_TEXT_COLOR)) instanceof IntTag) {
				$this->textColor = Color::fromARGB(Binary::unsignInt($baseColor->getValue()));
			}
		}

		if (($glowingTag = $nbt->getTag(self::TAG_GLOWING_TEXT)) instanceof ByteTag &&
			($lightingBugResolvedTag = $nbt->getTag(self::TAG_LEGACY_BUG_RESOLVE)) instanceof ByteTag) {
			$glowingText = $glowingTag->getValue() !== 0 && $lightingBugResolvedTag->getValue() !== 0;
			$this->glowing = $glowingText;
		}

		if ($nbt->hasTag(self::TAG_FRONT_TEXT, CompoundTag::class)) { //MCPE 1.19.80 save format
			$this->text = self::fixTextBlob($nbt->getCompoundTag(self::TAG_FRONT_TEXT)->getString(self::TAG_TEXT_BLOB, ""));
		} elseif ($nbt->hasTag(self::TAG_TEXT_BLOB, StringTag::class)) { //MCPE 1.2 save format
			$this->text = self::fixTextBlob($nbt->getString(self::TAG_TEXT_BLOB));
		} else {
			for ($i = 1; $i <= 4; ++$i) {
				$textKey = sprintf(self::TAG_TEXT_LINE, $i);
				if ($nbt->hasTag($textKey, StringTag::class)) {
					$this->text[$i - 1] = $nbt->getString($textKey);
				}
			}
		}

		$this->text = array_map(function (string $line) : string {
			return mb_scrub($line, 'UTF-8');
		}, $this->text);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$signNbt = new CompoundTag(self::TAG_FRONT_TEXT);
		$signNbt->setString(self::TAG_TEXT_BLOB, implode("\n", $this->text));
		$nbt->setTag($signNbt);
		$nbt->setInt(self::TAG_TEXT_COLOR, Binary::signInt($this->textColor->toARGB()));
		$nbt->setByte(self::TAG_GLOWING_TEXT, $this->glowing ? 1 : 0);
		$nbt->setByte(self::TAG_LEGACY_BUG_RESOLVE, 1);
	}

	/**
	 * Changes contents of the specific lines to the string provided.
	 * Leaves contents of the specific lines as is if null is provided.
	 */
	public function setText(?string $line1 = "", ?string $line2 = "", ?string $line3 = "", ?string $line4 = "") : void
	{
		if ($line1 !== null) {
			$this->setLine(0, $line1);
		}
		if ($line2 !== null) {
			$this->setLine(1, $line2);
		}
		if ($line3 !== null) {
			$this->setLine(2, $line3);
		}
		if ($line4 !== null) {
			$this->setLine(3, $line4);
		}
	}

	/**
	 * @param int $index 0-3
	 */
	public function setLine(int $index, string $line, bool $update = true) : void
	{
		if ($index < 0 || $index > 3) {
			throw new InvalidArgumentException("Index must be in the range 0-3!");
		}
		if (!mb_check_encoding($line, 'UTF-8')) {
			throw new InvalidArgumentException("Text must be valid UTF-8");
		}

		$this->text[$index] = $line;
		if ($update) {
			$this->onChanged();
		}
	}

	/**
	 * @param int $index 0-3
	 */
	public function getLine(int $index) : string
	{
		if ($index < 0 || $index > 3) {
			throw new InvalidArgumentException("Index must be in the range 0-3!");
		}
		return $this->text[$index];
	}

	/**
	 * @return string[]
	 */
	public function getText() : array
	{
		return $this->text;
	}

	public function setGlowing(bool $glowing) : void
	{
		$this->glowing = $glowing;
		$this->onChanged();
	}

	public function isGlowing() : bool
	{
		return $this->glowing;
	}

	public function setTextColor(Color $textColor) : void
	{
		$this->textColor = $textColor;
		$this->onChanged();
	}

	public function getTextColor() : Color
	{
		return $this->textColor;
	}

	/**
	 * Returns the entity runtime ID of the player who placed this sign. Only the player whose entity ID matches this
	 * one may edit the sign text.
	 * This is needed because as of 1.16.220, there is still no reliable way to detect when the MCPE client closed the
	 * sign edit GUI, so we have no way to know when the text is finalized. This limits editing of the text to only the
	 * player who placed it, and only while that player is online.
	 * We can say for sure that the sign is finalized if either of the following occurs:
	 * - The player quits (after rejoin, the player's entity runtimeID will be different).
	 * - The chunk is unloaded (on next load, the entity runtimeID will be null, because it's not saved).
	 */
	public function getEditorEntityRuntimeId() : ?int
	{
		return $this->editorEntityRuntimeId;
	}

	public function setEditorEntityRuntimeId(?int $editorEntityRuntimeId) : void
	{
		$this->editorEntityRuntimeId = $editorEntityRuntimeId;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$getText = function (array $text) : array {
			$line1 = $text[0] ?? "";
			$line2 = $text[1] ?? "";
			$line3 = $text[2] ?? "";
			$line4 = $text[3] ?? "";

			if ($line4 !== "") {
				return [$line1, $line2, $line3, $line4];
			} elseif ($line3 !== "") {
				return [$line1, $line2, $line3];
			} elseif ($line2 !== "") {
				return [$line1, $line2];
			} elseif ($line1 !== "") {
				return [$line1];
			}

			return [];
		};

		$nbt->setString(self::TAG_TEXT_BLOB, implode("\n", $getText($this->text)));

		for ($i = 1; $i <= 4; ++$i) { //Backwards-compatibility
			$textKey = sprintf(self::TAG_TEXT_LINE, $i);
			$nbt->setString($textKey, $this->getLine($i - 1));
		}

		$textPolyfill = function (CompoundTag $textTag) : CompoundTag {
			//the following are not yet used by the server, but needed to roll back any changes to glowing state or colour
			//if the client uses dye on the sign
			$textTag->setInt(self::TAG_TEXT_COLOR, Binary::signInt(0xff_00_00_00));
			$textTag->setByte(self::TAG_GLOWING_TEXT, 0);
			$textTag->setByte(self::TAG_PERSIST_FORMATTING, 1); //TODO: not sure what this is used for

			return $textTag;
		};
		$front_text_tag = $textPolyfill(new CompoundTag(self::TAG_FRONT_TEXT));
		$front_text_tag->setString(self::TAG_TEXT_BLOB, implode("\n", $getText($this->text)));
		$nbt->setTag(
			$front_text_tag
		);
		//TODO: this is not yet used by the server, but needed to rollback any client-side changes to the back text
		$back_text_tag = $textPolyfill(new CompoundTag(self::TAG_BACK_TEXT));
		$back_text_tag->setString(self::TAG_TEXT_BLOB, "");
		$nbt->setTag(
			$back_text_tag
		);
		$nbt->setByte(self::TAG_WAXED, 0);
		$nbt->setLong(self::TAG_LOCKED_FOR_EDITING_BY, $this->editorEntityRuntimeId ?? -1);
	}

	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		if ($nbt->getString("id") !== Tile::SIGN) {
			return false;
		}

		if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_582) {
			if (!(($nbt = $nbt->getCompoundTag(self::TAG_FRONT_TEXT)) instanceof CompoundTag)) {
				throw new InvalidArgumentException("Expected " . CompoundTag::class . " in block entity NBT, got " . (is_object($nbt) ? get_class($nbt) : gettype($nbt)));
			}
		}

		$lines = [];
		if ($nbt->hasTag(self::TAG_TEXT_BLOB, StringTag::class)) {
			$lines = self::fixTextBlob($nbt->getString(self::TAG_TEXT_BLOB));
		} elseif ($player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
			$lines = [
				$nbt->getString("Text1"),
				$nbt->getString("Text2"),
				$nbt->getString("Text3"),
				$nbt->getString("Text4")
			];
		}

		if($lines === $this->getText()){
			return false;
		}

		$size = 0;
		foreach ($lines as $line) {
			$size += strlen($line);
		}
		if ($size > 1000) {
			//trigger kick + IP ban - TODO: on 4.0 this will require a better fix
			throw new UnexpectedValueException($player->getName() . " tried to write $size bytes of text onto a sign (bigger than max 1000)");
		}

		$removeFormat = $player->getRemoveFormat();

		$ev = new SignChangeEvent($this->getBlock(), $player, array_map(function (string $line) use ($removeFormat) { return TextFormat::clean($line, $removeFormat); }, $lines));
		if (
			$player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_503 &&
			$this->editorEntityRuntimeId !== $player->getId()
		) {
			$ev->setCancelled();
		}
		$ev->call();

		if (!$ev->isCancelled()) {
			$this->setText(...$ev->getLines());
			$this->editorEntityRuntimeId = null;
			$this->getLevel()->setBlock($this, $this->getLevel()->getBlock($this));
			return true;
		} else {
			return false;
		}
	}
}
