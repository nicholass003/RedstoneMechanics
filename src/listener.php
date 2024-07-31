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
use nicholass003\redstonemechanics\event\BlockRedstonePowerEvent;
use pocketmine\block\Lever;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class EventListener implements Listener{

	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$block = $event->getBlock();
			if($block instanceof Lever){
				foreach(BlockRedstonePowerHelper::update($block) as $rBlock){
					$ev = new BlockRedstonePowerEvent($rBlock, $rBlock->isPowered(), !$rBlock->isPowered());
					$ev->call();
				}
			}
		}
	}
}
