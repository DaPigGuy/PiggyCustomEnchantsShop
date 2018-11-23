<?php

namespace DaPigGuy\PiggyCustomEnchantsShop\Shops;

/**
 * Class Shop
 * @package DaPigGuy\PiggyCustomEnchantsShops\Shops
 */
class Shop
{
    /** @var string */
    private $enchantment;
    /** @var int */
    private $enchantLevel;
    /** @var int */
    private $price;

    /**
     * Shop constructor.
     * @param string $enchantment
     * @param int    $enchantLevel
     * @param int    $price
     */
    public function __construct(string $enchantment, int $enchantLevel, int $price)
    {
        $this->enchantment = $enchantment;
        $this->enchantLevel = $enchantLevel;
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getEnchantment()
    {
        return $this->enchantment;
    }

    /**
     * @return int
     */
    public function getEnchantLevel()
    {
        return $this->enchantLevel;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }
}