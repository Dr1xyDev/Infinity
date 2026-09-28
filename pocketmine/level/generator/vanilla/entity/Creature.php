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

use pocketmine\level\generator\vanilla\math\Vector3;

use function abs;
use function asin;
use function atan;
use function rad2deg;

abstract class Creature extends Living
{
	public function willMove(int $distance = 36) : bool
	{
		foreach ($this->getViewers() as $viewer) {
			if ($this->distance($viewer->getLocation()) <= $distance) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return float|int
	 * 获取yaw角度
	 */
	public function getMyYaw($mx, $mz)  //根据motion计算转向角度
	{//转向计算
		if ($mz == 0) {  //斜率不存在
			if ($mx < 0) {
				$yaw = -90;
			} else {
				$yaw = 90;
			}
		} else {  //存在斜率
			if ($mx >= 0 && $mz > 0) {  //第一象限
				$atan = atan($mx / $mz);
				$yaw = rad2deg($atan);
			} elseif ($mx >= 0 && $mz < 0) {  //第二象限
				$atan = atan($mx / abs($mz));
				$yaw = 180 - rad2deg($atan);
			} elseif ($mx < 0 && $mz < 0) {  //第三象限
				$atan = atan($mx / $mz);
				$yaw = -(180 - rad2deg($atan));
			} elseif ($mx < 0 && $mz > 0) {  //第四象限
				$atan = atan(abs($mx) / $mz);
				$yaw = -(rad2deg($atan));
			} else {
				$yaw = 0;
			}
		}

		$yaw = -$yaw;
		return $yaw;
	}

	/**
	 * @return float|int
	 * 获取pitch角度
	 */
	public function getMyPitch(Vector3 $from, Vector3 $to)
	{
		$distance = $from->distance($to);
		$height = $to->y - $from->y;
		if ($height > 0) {
			return -rad2deg(asin($height / $distance));
		} elseif ($height < 0) {
			return rad2deg(asin(-$height / $distance));
		} else {
			return 0;
		}
	}
}
