<?php

namespace PiggyCustomEnchantsShop\Shops;

use PiggyCustomEnchantsShop\Main;
use pocketmine\utils\Config;

/**
 * Class SignShopsManager
 * @package PiggyCustomEnchantsShop\Shops
 */
class SignShopsManager
{
    private $plugin;
    private $file;

    private $shops = [];

    /**
     * SignShopsManager constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        @mkdir($this->plugin->getDataFolder() . "signs");
        $this->file = new Config($this->plugin->getDataFolder() . "signs/shops.yml");
    }

    public function initShops()
    {
        foreach ($this->file->getAll() as $key => $values) {
            $coords = explode(",", $key);
            $this->shops[$key] = new SignShop($values[0], $values[1], $values[2], $coords[0], $coords[1], $coords[2], $coords[3]);
        }
    }

    /**
     * @param SignShop $shop
     * @return mixed|void
     */
    public function addShop(SignShop $shop)
    {
        $key = $shop->getX() . "," . $shop->getY() . "," . $shop->getZ() . "," . $shop->getLevel();
        $this->file->setNested($key, [$shop->getEnchantment(), $shop->getEnchantLevel(), $shop->getPrice()]);
        $this->file->save();
        $this->shops[$key] = $shop;
    }

    /**
     * @param SignShop $shop
     * @return mixed|void
     */
    public function removeShop(SignShop $shop)
    {
        $key = $shop->getX() . "," . $shop->getY() . "," . $shop->getZ() . "," . $shop->getLevel();
        $this->file->removeNested($key);
        $this->file->save();
        if (isset($this->shops[$key])) {
            unset($this->shops[$key]);
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $z
     * @param string $level
     * @return null
     */
    public function getShopAt(int $x, int $y, int $z, string $level)
    {
        return $this->file->exists($x . "," . $y . "," . $z . "," . $level) ? $this->shops[$x . "," . $y . "," . $z . "," . $level] : null;
    }

    /**
     * @return mixed
     */
    public function getShops()
    {
        return $this->shops;
    }
}