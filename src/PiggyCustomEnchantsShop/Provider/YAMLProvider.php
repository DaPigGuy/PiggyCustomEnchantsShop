<?php

namespace PiggyCustomEnchantsShop\Provider;


use PiggyCustomEnchantsShop\Main;
use PiggyCustomEnchantsShop\Shop;
use pocketmine\utils\Config;
use function explode;

/**
 * Class YAMLProvider
 * @package PiggyCustomEnchantsShop\Provider
 */
class YAMLProvider implements Provider
{
    private $plugin;
    private $file;

    private $shops;

    /**
     * YAMLProvider constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        $this->file = new Config($this->plugin->getDataFolder() . "shops.yml");
    }

    public function initShops()
    {
        foreach ($this->file->getAll() as $key => $value) {
            $coords = explode(",", $key);
            $this->shops[$key] = new Shop($coords[0], $coords[1], $coords[2], $value[0], $value[1], $value[2]);
        }
    }

    /**
     * @param Shop $shop
     * @return mixed|void
     */
    public function addShop(Shop $shop)
    {
        $key = $shop->getX() . "," . $shop->getY() . "," . $shop->getZ();
        $this->file->setNested($key, [$shop->getEnchantment(), $shop->getLevel(), $shop->getPrice()]);
        $this->file->save();
        $this->shops[$key] = $shop;
    }

    /**
     * @param Shop $shop
     * @return mixed|void
     */
    public function removeShop(Shop $shop)
    {
        $key = $shop->getX() . "," . $shop->getY() . "," . $shop->getZ();
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
     * @return null
     */
    public function getShop(int $x, int $y, int $z)
    {
        if ($this->file->exists($x . "," . $y . "," . $z)) {
            return $this->shops[$x . "," . $y . "," . $z];
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getShops()
    {
        return $this->shops;
    }
}