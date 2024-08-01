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

namespace nicholass003\redstonemechanics;

use nicholass003\redstonemechanics\block\power\BlockRedstonePowerHelper;
use nicholass003\redstonemechanics\block\transmission\BlockRedstoneTransmissionHelper;
use nicholass003\redstonemechanics\block\utils\BlockRedstoneUtils;
use pocketmine\block\Lever;
use pocketmine\block\RedstoneWire;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Facing;

class EventListener implements Listener{

	public function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();
		BlockRedstoneUtils::updateNearBlocks($block);
	}

	public function onBlockPlace(BlockPlaceEvent $event) : void{
		foreach($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]){
			foreach(Facing::ALL as $face){
				$rBlock = $block->getSide($face);
				if($block instanceof RedstoneWire && $rBlock instanceof RedstoneWire){
					$block->setOutputSignalStrength($rBlock->getOutputSignalStrength());
					$block->getPosition()->getWorld()->setBlock($block->getPosition(), $block);
					BlockRedstoneTransmissionHelper::update($block);
					//TODO: check RedstoneWire signal connection
				}
			}
			BlockRedstoneUtils::updateNearBlocks($block);
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$block = $event->getBlock();
			if($block instanceof Lever){
				BlockRedstonePowerHelper::update($block);
			}
		}
	}
}
