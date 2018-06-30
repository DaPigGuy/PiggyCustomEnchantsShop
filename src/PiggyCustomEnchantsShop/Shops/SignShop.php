<?php

namespace PiggyCustomEnchantsShop\Shops;

/**
 * Class SignShop
 * @package PiggyCustomEnchantsShop\Shops
 */
class SignShop extends Shop
{
    /** @var int */
    private $x;
    /** @var int */
    private $y;
    /** @var int */
    private $z;
    /** @var string */
    private $level;

    /**
     * SignShop constructor.
     * @param string $enchantment
     * @param int $enchantLevel
     * @param int $price
     * @param int $x
     * @param int $y
     * @param int $z
     * @param string $level
     */
    public function __construct(string $enchantment, int $enchantLevel, int $price, int $x, int $y, int $z, string $level)
    {
        parent::__construct($enchantment, $enchantLevel, $price);
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->level = $level;
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @return int
     */
    public function getZ()
    {
        return $this->z;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }
}