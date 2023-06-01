<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\tiles;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use DaPigGuy\PiggyCustomEnchantsShop\enchants\PlaceholderEnchant;
use DaPigGuy\PiggyCustomEnchantsShop\PiggyCustomEnchantsShop;
use pocketmine\block\tile\Sign;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ShopSignTile extends Sign
{
    public Enchantment $enchantment;
    public int $enchantmentLevel = 1;
    public float $price = 1;

    public function getEnchantment(): Enchantment
    {
        return $this->enchantment;
    }

    public function setEnchantment(Enchantment $enchantment): void
    {
        $this->enchantment = $enchantment;
    }

    public function getEnchantmentLevel(): int
    {
        return $this->enchantmentLevel;
    }

    public function setEnchantmentLevel(int $level): void
    {
        $this->enchantmentLevel = $level;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function purchaseItem(PiggyCustomEnchantsShop $plugin, Player $player): void
    {
        if (($enchant = $this->getEnchantment()) instanceof PlaceholderEnchant) return;
        if (($limit = $plugin->getConfig()->get("enchant-limit", -1)) !== -1 && count($player->getInventory()->getItemInHand()->getEnchantments()) >= $limit) {
            $player->sendMessage(TextFormat::RED . "Enchantment limit of " . $limit . " reached.");
            return;
        }
        if (!Utils::canBeEnchanted($player->getInventory()->getItemInHand(), $enchant, $this->getEnchantmentLevel())) {
            $player->sendMessage(TextFormat::RED . "Enchantment could not be applied to item.");
            return;
        }
        $plugin->getEconomyProvider()->takeMoney($player, $this->getPrice());
        $item = $player->getInventory()->getItemInHand();
        $item->addEnchantment(new EnchantmentInstance($enchant, $this->getEnchantmentLevel()));
        $player->getInventory()->setItemInHand($item);
        $player->sendMessage(TextFormat::GREEN . "Item has successfully been enchanted.");
    }

    public function readSaveData(CompoundTag $nbt): void
    {
        parent::readSaveData($nbt);
        $this->enchantment = CustomEnchantManager::getEnchantment($nbt->getInt("Enchantment")) ?? Enchantment::get($nbt->getInt("Enchantment")) ?? new PlaceholderEnchant($nbt->getInt("Enchantment"));
        $this->enchantmentLevel = $nbt->getInt("EnchantmentLevel");
        $this->price = (float)$nbt->getTagValue("Price", Tag::class);

    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        parent::writeSaveData($nbt);
        $nbt->setInt("Enchantment", $this->enchantment->getId());
        $nbt->setInt("EnchantmentLevel", $this->enchantmentLevel);
        $nbt->setFloat("Price", $this->price);
    }

    public function addAdditionalSpawnData(CompoundTag $nbt): void
    {
        parent::addAdditionalSpawnData($nbt);
        $nbt->setString(self::TAG_ID, "Sign");
    }
}