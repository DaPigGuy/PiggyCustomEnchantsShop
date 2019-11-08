<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\shops;

use pocketmine\item\enchantment\Enchantment;

/**
 * Class UIShop
 * @package DaPigGuy\PiggyCustomEnchantsShop\shops
 */
class UIShop
{
    /** @var int */
    private $id;

    /** @var Enchantment|null */
    private $enchantment;
    /** @var int */
    private $enchantmentLevel;
    /** @var int */
    private $price;

    /**
     * UIShop constructor.
     * @param int $id
     * @param Enchantment|null $enchantment
     * @param int $enchantmentLevel
     * @param int $price
     */
    public function __construct(int $id, ?Enchantment $enchantment, int $enchantmentLevel, int $price)
    {
        $this->id = $id;
        $this->enchantment = $enchantment;
        $this->enchantmentLevel = $enchantmentLevel;
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Enchantment|null
     */
    public function getEnchantment(): ?Enchantment
    {
        return $this->enchantment;
    }

    /**
     * @param Enchantment $enchantment
     */
    public function setEnchantment(Enchantment $enchantment): void
    {
        $this->enchantment = $enchantment;
    }

    /**
     * @return int
     */
    public function getEnchantmentLevel(): int
    {
        return $this->enchantmentLevel;
    }

    /**
     * @param int $level
     */
    public function setEnchantmentLevel(int $level): void
    {
        $this->enchantmentLevel = $level;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }
}