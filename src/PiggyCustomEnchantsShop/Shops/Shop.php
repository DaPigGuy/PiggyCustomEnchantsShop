<?php

namespace PiggyCustomEnchantsShop\Shops;

/**
 * Class Shop
 * @package PiggyCustomEnchantsShops\Shops
 */
class Shop
{
    private $enchantment;
    private $enchantLevel;
    private $price;

    /**
     * Shop constructor.
     * @param string $enchantment
     * @param int $enchantLevel
     * @param int $price
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