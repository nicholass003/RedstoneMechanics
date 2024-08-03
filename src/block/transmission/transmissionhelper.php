<?php

/*
 * Copyright (c) 2024 - present nicholass003
 *        _      _           _                ___   ___ ____
 *       (_)    | |         | |              / _ \ / _ \___ \
 *  _ __  _  ___| |__   ___ | | __ _ ___ ___| | | | | | |__) |
 * | '_ \| |/ __| '_ \ / _ \| |/ _` / __/ __| | | | | | |__ <
 * | | | | | (__| | | | (_) | | (_| \__ \__ \ |_| | |_| |__) |
 * |_| |_|_|\___|_| |_|\___/|_|\__,_|___/___/\___/ \___/____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  nicholass003
 * @link    https://github.com/nicholass003/
 *
 *
 */

declare(strict_types=1);

namespace nicholass003\redstonemechanics\block\transmission;

use nicholass003\redstonemechanics\block\IBlockRedstoneHelper;
use nicholass003\redstonemechanics\event\BlockRedstonePowerEvent;
use nicholass003\redstonemechanics\RedstoneMechanics;
use pocketmine\block\Block;
use pocketmine\block\RedstoneWire;
use pocketmine\math\Facing;
use function max;

class BlockRedstoneTransmissionHelper implements IBlockRedstoneHelper{

	public static function update(Block $block) : void{
		if($block instanceof RedstoneWire){
			$signal = max($block->getOutputSignalStrength(), 0);
			self::transmite($block, $signal);
		}
	}

	public static function transmite(Block $block, int $power, array &$visitedBlocks = []) : void {
		if($power < 0 || $power > 15){
			return;
		}

		$positionKey = $block->getPosition()->__toString();
		if(isset($visitedBlocks[$positionKey])){
			return;
		}

		$visitedBlocks[$positionKey] = true;

		$newPower = max($power - 1, 0);

		foreach(Facing::ALL as $face){
			$rBlock = $block->getSide($face);
			$world = $rBlock->getPosition()->getWorld();

			if($rBlock instanceof RedstoneWire){
				if ($rBlock->getOutputSignalStrength() !== $newPower){
					$rBlock->setOutputSignalStrength($newPower);
					$world->setBlock($rBlock->getPosition(), $rBlock);
					self::transmite($rBlock, $newPower, $visitedBlocks);
				}
			}elseif(RedstoneMechanics::isPoweredByRedstone($rBlock)){
				$actived = $newPower > 0;
				$ev = new BlockRedstonePowerEvent($rBlock, $actived);
				$ev->call();
				if($rBlock->isPowered() !== $ev->getPowered()){
					$rBlock->setPowered($ev->getPowered());
					$world->setBlock($rBlock->getPosition(), $rBlock);
				}
			}
		}
	}
}
