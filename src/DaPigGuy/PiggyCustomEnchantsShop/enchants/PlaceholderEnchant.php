<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\enchants;

use pocketmine\item\enchantment\Enchantment;

/**
 * Class PlaceholderEnchant
 * @package DaPigGuy\PiggyCustomEnchantsShop\enchants
 */
class PlaceholderEnchant extends Enchantment
{
    /**
     * @param int $id
     * @param string $name
     */
    public function __construct(int $id, string $name = "")
    {
        parent::__construct($id, $name, Enchantment::RARITY_COMMON, Enchantment::SLOT_ALL, Enchantment::SLOT_ALL, 1);
    }
}