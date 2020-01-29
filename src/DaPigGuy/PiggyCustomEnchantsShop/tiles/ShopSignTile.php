<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\tiles;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use DaPigGuy\PiggyCustomEnchantsShop\PiggyCustomEnchantsShop;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

/**
 * Class ShopSignTile
 * @package DaPigGuy\PiggyCustomEnchantsShop\tiles
 */
class ShopSignTile extends Sign
{
    /** @var Enchantment */
    public $enchantment;
    /** @var int */
    public $enchantmentLevel = 1;
    /** @var int */
    public $price = 1;

    /**
     * @return Enchantment
     */
    public function getEnchantment(): Enchantment
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

    /**
     * @param PiggyCustomEnchantsShop $plugin
     * @param Player $player
     */
    public function purchaseItem(PiggyCustomEnchantsShop $plugin, Player $player): void
    {
        if (($enchant = $this->getEnchantment()) === null) return;
        if (Utils::canBeEnchanted($player->getInventory()->getItemInHand(), $enchant, $this->getEnchantmentLevel())) {
            $plugin->getEconomyProvider()->takeMoney($player, $this->getPrice());
            $item = $player->getInventory()->getItemInHand();
            $item->addEnchantment(new EnchantmentInstance($enchant, $this->getEnchantmentLevel()));
            $player->getInventory()->setItemInHand($item);
            $player->sendMessage(TextFormat::GREEN . "Item has successfully been enchanted.");
            return;
        }
        $player->sendMessage(TextFormat::RED . "Enchantment could not be applied to item.");
    }

    /**
     * @param CompoundTag $nbt
     */
    protected function readSaveData(CompoundTag $nbt): void
    {
        parent::readSaveData($nbt);
        $this->enchantment = CustomEnchantManager::getEnchantment($nbt->getInt("Enchantment")) ?? Enchantment::getEnchantment($nbt->getInt("Enchantment"));
        $this->enchantmentLevel = $nbt->getInt("EnchantmentLevel");
        $this->price = $nbt->getInt("Price");

    }

    /**
     * @param CompoundTag $nbt
     */
    protected function writeSaveData(CompoundTag $nbt): void
    {
        parent::writeSaveData($nbt);
        $nbt->setInt("Enchantment", $this->enchantment->getId());
        $nbt->setInt("EnchantmentLevel", $this->enchantmentLevel);
        $nbt->setInt("Price", $this->price);
    }

    /**
     * @param CompoundTag $nbt
     */
    public function addAdditionalSpawnData(CompoundTag $nbt): void
    {
        parent::addAdditionalSpawnData($nbt);
        $nbt->setString(self::TAG_ID, "Sign");
    }
}