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

namespace pocketmine\level\generator\vanilla\level\generator;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\SubChunk;
use pocketmine\level\generator\vanilla\utils\Random;
use function array_map;
use function count;
use function explode;
use function preg_match;
use function preg_match_all;

class FlatGenerator extends Generator {
	private Chunk $chunk;

	private array $structure = [];

	/** @var mixed[] */
	private array $options;
	private string $preset;

	public function init(ChunkManager $level, Random $random) : void{
		parent::init($level, $random);
		$this->generateBaseChunk();
	}

	public function getName() : string{
		return "flat";
	}

	public function getSettings() : array{
		return $this->options;
	}

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(array $options = []){
		parent::__construct($options);

		$this->options = $options;
		if (isset($this->options["preset"]) && $this->options["preset"] != "") {
			$this->preset = $this->options["preset"];
		} else {
			$this->preset = "2;7,2x3,2;1;";
		}

		$this->parsePreset();
	}

	/**
	 * @return int[][]
	 * @throws InvalidGeneratorOptionsException
	 */
	public static function parseLayers(string $layers) : array{
		$result = [];
		$split = array_map('\trim', explode(',', $layers));
		$y = 0;
		foreach ($split as $line) {
			preg_match('#^(?:(\d+)[x|*])?(.+)$#', $line, $matches);
			if (count($matches) !== 3) {
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\"");
			}

			$cnt = $matches[1] !== "" ? (int) $matches[1] : 1;
			try {
				$b = ItemFactory::fromString($matches[2])->getBlock();
			} catch (InvalidArgumentException $e) {
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\": " . $e->getMessage(), 0, $e);
			}
			for ($cY = $y, $y += $cnt; $cY < $y; ++$cY) {
				$result[$cY] = [$b->getId(), $b->getDamage()];
			}
		}

		return $result;
	}

	protected function parsePreset() : void{
		$preset = explode(";", $this->preset);
		$blocks = (string) ($preset[1] ?? "");
		$options = (string) ($preset[3] ?? "");
		$this->structure = self::parseLayers($blocks);

		//TODO: more error checking
		preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:]{0,})\)?),?#', $options, $matches);
		foreach ($matches[2] as $i => $option) {
			$params = true;
			if ($matches[3][$i] !== "") {
				$params = [];
				$p = explode(" ", $matches[3][$i]);
				foreach ($p as $k) {
					$k = explode("=", $k);
					if (isset($k[1])) {
						$params[$k[0]] = $k[1];
					}
				}
			}
			$this->options[$option] = $params;
		}
	}

	protected function generateBaseChunk() : void{
		$this->chunk = new Chunk(0, 0);
		$this->chunk->setGenerated();

		$structure = $this->structure;
		$count = count($structure);
		for ($sy = 0; $sy < $count; $sy += SubChunk::EDGE_LENGTH) {
			$subchunk = $this->chunk->getSubChunk($sy >> SubChunk::COORD_BIT_SIZE);
			for ($y = 0; $y < SubChunk::EDGE_LENGTH && isset($structure[$y | $sy]); ++$y) {
				[$id, $meta] = $structure[$y | $sy];

				for ($Z = 0; $Z < SubChunk::EDGE_LENGTH; ++$Z) {
					for ($X = 0; $X < SubChunk::EDGE_LENGTH; ++$X) {
						$subchunk->setFullBlock($X, $y, $Z, $id << Block::INTERNAL_METADATA_BITS | $meta);
					}
				}
			}
		}
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$chunk = clone $this->chunk;
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->level->setChunk($chunkX, $chunkZ, $chunk);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{}

	public function getGroundHeight() : int{
		return count($this->structure);
	}
}
