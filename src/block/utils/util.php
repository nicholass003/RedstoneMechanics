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

namespace nicholass003\redstonemechanics\block\utils;

use nicholass003\redstonemechanics\RedstoneMechanics;
use pocketmine\block\Block;
use pocketmine\block\RedstoneWire;
use pocketmine\math\Facing;

final class BlockRedstoneUtils{

	public static function updateNearBlocks(Block $block) : void{
		$powered = false;
		$world = $block->getPosition()->getWorld();
		foreach(Facing::ALL as $face){
			$rBlock = $block->getSide($face);
			if($rBlock instanceof RedstoneWire){
				$signal = $rBlock->getOutputSignalStrength();
				if($signal > 0){
					$powered = true;
				}
				//TODO: check RedstoneWire signal connection
			}
			if(RedstoneMechanics::isPoweredByRedstone($rBlock)){
				$rBlock->setPowered($powered);
				$world->setBlock($rBlock->getPosition(), $rBlock);
			}
		}
	}
}
