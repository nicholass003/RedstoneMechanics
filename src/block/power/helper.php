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

namespace nicholass003\redstonemechanics\block\power;

use nicholass003\redstonemechanics\block\IBlockRedstoneHelper;
use nicholass003\redstonemechanics\block\transmission\BlockRedstoneTransmissionHelper;
use nicholass003\redstonemechanics\block\utils\BlockRedstoneUtils;
use nicholass003\redstonemechanics\event\BlockRedstonePowerEvent;
use pocketmine\block\Block;
use pocketmine\block\Lever;
use pocketmine\block\RedstoneWire;
use pocketmine\math\Facing;

class BlockRedstonePowerHelper implements IBlockRedstoneHelper{

	public static function update(Block $block) : void{
		self::power($block);
	}

	public static function power(Block $block) : void{
		$activate = false;
		$ignoreFace = null;
		$power = 0;
		if($block instanceof Lever){
			$activate = !$block->isActivated();
			$ignoreFace = $block->getFacing()->getFacing();
			if($activate === true){
				$power = 15;
			}
		}
		foreach(Facing::ALL as $face){
			if($face === $ignoreFace){
				continue;
			}
			$rBlock = $block->getSide($face);
			$world = $rBlock->getPosition()->getWorld();
			if($rBlock instanceof RedstoneWire){
				$rBlock->setOutputSignalStrength($power);
				if($activate === false){
					$rBlock->setOutputSignalStrength(0);
				}
				$world->setBlock($rBlock->getPosition(), $rBlock);
				BlockRedstoneTransmissionHelper::update($rBlock);
			}else{
				self::activate($rBlock, $activate);
			}
		}
	}

	public static function activate(Block $block, bool $activate) : void{
		$pos = $block->getPosition();
		$world = $pos->getWorld();
		if(BlockRedstoneUtils::isPoweredByRedstone($block)){
			/** @var Block&\pocketmine\block\utils\PoweredByRedstoneTrait $block */
			$ev = new BlockRedstonePowerEvent($block, $activate);
			$ev->call();
			if($activate !== $ev->getPowered()){
				$block->setPowered($ev->getPowered());
				$world->setBlock($pos, $block);
			}
		}
	}
}
