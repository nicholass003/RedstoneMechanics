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
use nicholass003\redstonemechanics\block\power\BlockRedstonePowerHelper;
use nicholass003\redstonemechanics\block\utils\BlockRedstoneUtils;
use pocketmine\block\Block;
use pocketmine\block\RedstoneWire;
use pocketmine\math\Facing;
use pocketmine\world\World;
use function max;

class BlockRedstoneTransmissionHelper implements IBlockRedstoneHelper{

	public static function update(Block $block) : void{
		if($block instanceof RedstoneWire){
			$signal = max($block->getOutputSignalStrength(), 0);
			self::transmite($block, $signal);
		}
	}

	public static function transmite(Block $block, int $power, array &$visitedBlocks = []) : void{
		$pos = $block->getPosition();
		$world = $pos->getWorld();

		$hash = World::blockHash($pos->x, $pos->y, $pos->z);
		if(isset($visitedBlocks[$hash])){
			return;
		}

		$visitedBlocks[$hash] = true;

		if($block instanceof RedstoneWire){
			if($block->getOutputSignalStrength() !== $power){
				$block->setOutputSignalStrength($power);
				$world->setBlock($pos, $block);
			}
		}

		$_power = max($power - 1, 0);
		$neighbors = [];

		foreach(Facing::ALL as $face){
			$_block = $block->getSide($face);
			if($_block instanceof RedstoneWire){
				$neighbors[] = $_block;
			}elseif(BlockRedstoneUtils::isPoweredByRedstone($_block)){
				BlockRedstonePowerHelper::activate($_block, $_power > 0);
			}
		}

		foreach([1, -1] as $yOffset){
			foreach([Facing::NORTH, Facing::SOUTH, Facing::EAST, Facing::WEST] as $horizontal){
				$neighborPos = $pos->getSide($horizontal)->add(0, $yOffset, 0);
				$diagonalBlock = $world->getBlock($neighborPos);
				if($diagonalBlock instanceof RedstoneWire){
					$neighbors[] = $diagonalBlock;
				}
			}
		}

		foreach($neighbors as $neighbor){
			if(!isset($visitedBlocks[World::blockHash($neighbor->getPosition()->x, $neighbor->getPosition()->y, $neighbor->getPosition()->z)])){
				static::transmite($neighbor, $_power, $visitedBlocks);
			}
		}
	}
}