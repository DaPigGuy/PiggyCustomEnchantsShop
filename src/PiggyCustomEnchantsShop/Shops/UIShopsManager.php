<?php

namespace PiggyCustomEnchantsShop\Shops;

use PiggyCustomEnchantsShop\Main;
use pocketmine\utils\Config;

/**
 * Class UIShopsManager
 * @package PiggyCustomEnchantsShop\Shops
 */
class UIShopsManager
{
    private $plugin;
    private $file;

    private $shops = [];

    /**
     * UIShopsManager constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        @mkdir($this->plugin->getDataFolder() . "ui");
        $this->file = new Config($this->plugin->getDataFolder() . "ui/shops.yml");
    }

    public function initShops()
    {
        foreach ($this->file->getAll() as $key => $value) {
            $this->shops[$key] = new UIShop($value[0], $value[1], $value[2], (int) str_replace("id:", "", $key));
        }
    }

    /**
     * @param UIShop $shop
     * @return mixed|void
     */
    public function addShop(UIShop $shop)
    {
        $key = "id:" . $shop->getId();
        $this->file->setNested($key, [$shop->getEnchantment(), $shop->getEnchantLevel(), $shop->getPrice()]);
        $this->file->save();
        $this->shops[$key] = $shop;
    }

    /**
     * @param UIShop $shop
     * @return mixed|void
     */
    public function removeShop(UIShop $shop)
    {
        $key = "id:" . $shop->getId();
        $this->file->removeNested($key);
        $this->file->save();
        if (isset($this->shops[$key])) {
            unset($this->shops[$key]);
        }
    }

    /**
     * @param $id
     * @return null
     */
    public function getShopById($id)
    {
        return isset($this->shops["id:" . $id]) ? $this->shops["id:" . $id] : null;
    }

    /**
     * @return mixed
     */
    public function getShops()
    {
        return $this->shops;
    }


    /**
     * @return int
     */
    public function getNextId(){
        return count($this->shops);
    }
}