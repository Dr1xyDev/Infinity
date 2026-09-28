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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\skin;

class SkinAnimation
{
	public const TYPE_HEAD = 1;
	public const TYPE_BODY_32 = 2;
	public const TYPE_BODY_64 = 3;

	public const EXPRESSION_LINEAR = 0; //???
	public const EXPRESSION_BLINKING = 1;

	public function __construct(
		private SkinImage $image,
		private int $type,
		private float $frames,
		private int $expressionType
	) {
	}

	/**
	 * Image of the animation.
	 */
	public function getImage() : SkinImage
	{
		return $this->image;
	}

	/**
	 * The type of animation you are applying.
	 */
	public function getType() : int
	{
		return $this->type;
	}

	/**
	 * The total amount of frames in an animation.
	 */
	public function getFrames() : float
	{
		return $this->frames;
	}

	public function getExpressionType() : int
	{
		return $this->expressionType;
	}
}
