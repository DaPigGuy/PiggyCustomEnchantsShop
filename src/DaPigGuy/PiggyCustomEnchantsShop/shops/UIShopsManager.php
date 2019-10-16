<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\Shops;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchantsShop\PiggyCustomEnchantsShop;
use pocketmine\utils\Config;

/**
 * Class UIShopsManager
 * @package DaPigGuy\PiggyCustomEnchantsShop\Shops
 */
class UIShopsManager
{
    /** @var PiggyCustomEnchantsShop */
    private $plugin;
    /** @var Config */
    private $file;

    /** @var UIShop[] */
    private $shops = [];

    /**
     * UIShopsManager constructor.
     * @param PiggyCustomEnchantsShop $plugin
     */
    public function __construct(PiggyCustomEnchantsShop $plugin)
    {
        $this->plugin = $plugin;

        @mkdir($this->plugin->getDataFolder() . "ui");
        $this->file = new Config($this->plugin->getDataFolder() . "ui/shops.yml");
    }

    public function initShops(): void
    {
        foreach ($this->file->getAll() as $key => $value) {
            $this->shops[$key] = new UIShop((int)str_replace("id:", "", $key), CustomEnchantManager::getEnchantmentByName($value[0]), $value[1], $value[2]);
        }
    }

    /**
     * @param UIShop $shop
     */
    public function addShop(UIShop $shop): void
    {
        $key = "id:" . $shop->getId();
        $this->file->setNested($key, [$shop->getEnchantment()->getName(), $shop->getEnchantmentLevel(), $shop->getPrice()]);
        $this->file->save();
        $this->shops[$key] = $shop;
    }

    /**
     * @param UIShop $shop
     */
    public function removeShop(UIShop $shop): void
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
     * @return UIShop|null
     */
    public function getShopById($id): ?UIShop
    {
        return isset($this->shops["id:" . $id]) ? $this->shops["id:" . $id] : null;
    }

    /**
     * @return UIShop[]
     */
    public function getShops(): array
    {
        return $this->shops;
    }

    /**
     * @return int
     */
    public function getNextId(): int
    {
        return count($this->shops);
    }
}