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

namespace pocketmine\level\generator\vanilla\item\enchantment;

/**
 * Container for enchantment data applied to items.
 */
class EnchantmentInstance
{
	/** @var Enchantment */
	private $enchantment;
	/** @var int */
	private $level;

	/**
	 * EnchantmentInstance constructor.
	 *
	 * @param Enchantment $enchantment Enchantment type
	 * @param int         $level       Level of enchantment
	 */
	public function __construct(Enchantment $enchantment, int $level = 1)
	{
		$this->enchantment = $enchantment;
		$this->level = $level;
	}

	/**
	 * Returns the type of this enchantment.
	 */
	public function getType() : Enchantment
	{
		return $this->enchantment;
	}

	/**
	 * Returns the type identifier of this enchantment instance.
	 */
	public function getId() : int
	{
		return $this->enchantment->getId();
	}

	/**
	 * Returns the level of the enchantment.
	 */
	public function getLevel() : int
	{
		return $this->level;
	}

	/**
	 * Sets the level of the enchantment.
	 *
	 * @return $this
	 */
	public function setLevel(int $level)
	{
		$this->level = $level;

		return $this;
	}
}
