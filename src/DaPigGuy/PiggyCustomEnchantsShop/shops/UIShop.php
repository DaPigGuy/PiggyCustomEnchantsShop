<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\shops;

use pocketmine\item\enchantment\Enchantment;

class UIShop
{
    public function __construct(private int $id, private Enchantment $enchantment, private int $enchantmentLevel, private float $price)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEnchantment(): Enchantment
    {
        return $this->enchantment;
    }

    public function setEnchantment(Enchantment $enchantment): void
    {
        $this->enchantment = $enchantment;
    }

    public function getEnchantmentLevel(): int
    {
        return $this->enchantmentLevel;
    }

    public function setEnchantmentLevel(int $level): void
    {
        $this->enchantmentLevel = $level;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}