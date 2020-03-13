<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\enchants;

use pocketmine\item\enchantment\Enchantment;

class PlaceholderEnchant extends Enchantment
{
    public function __construct(int $id, string $name = "")
    {
        parent::__construct($id, $name, Enchantment::RARITY_COMMON, Enchantment::SLOT_ALL, Enchantment::SLOT_ALL, 1);
    }
}