<?php

namespace PiggyCustomEnchantsShop\Provider;

use PiggyCustomEnchantsShop\Shop;

/**
 * Interface Provider
 * @package PiggyCustomEnchantsShop\Provider
 */
interface Provider
{
    public function initShops();

    /**
     * @param Shop $shop
     * @return mixed
     */
    public function addShop(Shop $shop);

    /**
     * @param Shop $shop
     * @return mixed
     */
    public function removeShop(Shop $shop);

    /**
     * @param int $x
     * @param int $y
     * @param int $z
     * @return mixed
     */
    public function getShop(int $x, int $y, int $z);
}