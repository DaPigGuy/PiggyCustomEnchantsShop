<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\shops;

use pocketmine\item\enchantment\Enchantment;

class UIShop
{
    /** @var int */
    private $id;

    /** @var Enchantment */
    private $enchantment;
    /** @var int */
    private $enchantmentLevel;
    /** @var float */
    private $price;

    public function __construct(int $id, Enchantment $enchantment, int $enchantmentLevel, float $price)
    {
        $this->id = $id;
        $this->enchantment = $enchantment;
        $this->enchantmentLevel = $enchantmentLevel;
        $this->price = $price;
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