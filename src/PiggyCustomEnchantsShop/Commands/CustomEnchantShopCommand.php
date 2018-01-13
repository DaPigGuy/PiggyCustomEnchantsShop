<?php

namespace PiggyCustomEnchantsShop\Commands;


use jojoe77777\FormAPI\FormAPI;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchantsShop\Main;
use PiggyCustomEnchantsShop\Shops\UIShop;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class CustomEnchantShopCommand
 * @package PiggyCustomEnchantsShop\Commands
 */
class CustomEnchantShopCommand extends PluginCommand
{
    private $confirmations;

    /**
     * CustomEnchantShopCommand constructor.
     * @param $name
     * @param Main $plugin
     */
    public function __construct($name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription("");
        $this->setUsage("/customenchantshop [add]");
        $this->setAliases(["ceshop"]);
        $this->setPermission("piggycustomenchantsshop.command");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if (isset($args[0])) {
                switch ($args[0]) {
                    case "add":
                        if (count($args) >= 4) {
                            $args[1] = ucfirst($args[1]);
                            if (is_null($enchantment = CustomEnchants::getEnchantmentByName($args[1])) && is_null($enchantment = CustomEnchants::getEnchantment($args[1]))) {
                                $sender->sendMessage(TextFormat::RED . "Invalid enchantment.");
                                return false;
                            }
                            if (!is_numeric($args[2])) {
                                $sender->sendMessage(TextFormat::RED . "Level must be numerical.");
                                return false;
                            }
                            if (!is_numeric($args[3])) {
                                $sender->sendMessage(TextFormat::RED . "Price must be numerical.");
                                return false;
                            }
                            $plugin->getShopManager()->addShop(new UIShop($args[1], $args[2], $args[3], $plugin->getShopManager()->getNextId()));
                            $sender->sendMessage(TextFormat::GREEN . "Shop added!");
                        } else {
                            if ($sender instanceof Player) {
                                $this->addShop($sender);
                                return true;
                            }
                            $sender->sendMessage("Usage: /ceshop add <enchantment> <level> <price>");
                        }
                        return false;
                    default:
                        $sender->sendMessage("Usage: /customenchantshop [add]");
                        return false;
                }
            }
            if ($sender instanceof Player) {
                $formsapi = $plugin->getFormsAPI();
                if ($formsapi instanceof FormAPI && $formsapi->isEnabled()) {
                    $this->shopForm($sender);
                    return true;
                }
                return false;
            }
            $sender->sendMessage("Usage: /customenchantshop <add>");
            return false;
        }
        return false;
    }

    public function shopForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getFormsAPI();
            if ($formsapi instanceof FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    $plugin = $this->getPlugin();
                    if ($plugin instanceof Main) {
                        if (isset($data[0]) && count($plugin->getShopManager()->getShops()) > $data[0]) {
                            $this->confirmTransaction($player, $data[0]);
                        }
                    }
                });
                $form->setTitle(TextFormat::GREEN . "Custom Enchants Shop");
                foreach ($plugin->getShopManager()->getShops() as $shop) {
                    $form->addButton($shop->getEnchantment() . " " . $plugin->getCustomEnchants()->getRomanNumber($shop->getEnchantLevel()));
                }
                $form->addButton("Exit");
                $form->sendToPlayer($player);
            }
        }
    }

    /**
     * @param Player $player
     * @param $index
     */
    public function confirmTransaction(Player $player, $index)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getFormsAPI();
            if ($formsapi instanceof FormAPI && $formsapi->isEnabled()) {
                $shop = $plugin->getShopManager()->getShopById($index);
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        $plugin = $this->getPlugin();
                        if ($plugin instanceof Main) {
                            switch ($data[0]) {
                                case 0:
                                    $shop = $plugin->getShopManager()->getShopById($this->confirmations[$player->getLowerCaseName()]);
                                    if ($plugin->getEconomyManager()->getMoney($player) >= $shop->getPrice()) {
                                        $plugin->buyItem($player, $shop);
                                    } else {
                                        $player->sendMessage(TextFormat::RED . "Not enough money. Need " . $plugin->getEconomyManager()->getMonetaryUnit() . ($shop->getPrice() - $plugin->getEconomyManager()->getMoney($player)) . " more.");
                                    }
                                    break;
                                case 1:
                                    $this->shopForm($player);
                                    break;
                            }
                            unset($this->confirmations[$player->getLowerCaseName()]);
                        }
                    }
                });
                $form->setTitle("Confirmation");
                $form->setContent("Are you sure you would like to buy the enchantment " . $shop->getEnchantment() . " " . $plugin->getCustomEnchants()->getRomanNumber($shop->getEnchantLevel()) . " for " . $plugin->getEconomyManager()->getMonetaryUnit() . $shop->getPrice() . "?");
                $form->addButton("Yes");
                $form->addButton("No");
                $form->sendToPlayer($player);
                $this->confirmations[$player->getLowerCaseName()] = $index;
            }
        }
    }

    /**
     * @param Player $player
     */
    public function addShop(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $formsapi = $plugin->getFormsAPI();
            if ($formsapi instanceof FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createCustomForm(function (Player $player, $data) {
                    $plugin = $this->getPlugin();
                    if ($plugin instanceof Main) {
                        if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                            $data[0] = ucfirst($data[0]);
                            if (is_null($enchantment = CustomEnchants::getEnchantmentByName($data[0])) && is_null($enchantment = CustomEnchants::getEnchantment($data[1]))) {
                                $player->sendMessage(TextFormat::RED . "Invalid enchantment.");
                                return false;
                            }
                            if (!is_numeric($data[1])) {
                                $player->sendMessage(TextFormat::RED . "Level must be numerical.");
                                return false;
                            }
                            if (!is_numeric($data[2])) {
                                $player->sendMessage(TextFormat::RED . "Price must be numerical.");
                                return false;
                            }
                            if ($data[1] > $max = $plugin->getCustomEnchants()->getEnchantMaxLevel($enchantment)) {
                                $data[1] = $max;
                            }
                            $plugin->getShopManager()->addShop(new UIShop($data[0], $data[1], $data[2], $plugin->getShopManager()->getNextId()));
                            $player->sendMessage(TextFormat::GREEN . "Shop added!");
                            return true;
                        }
                        return false;
                    }
                    return false;
                });
                $form->setTitle("New Enchant Shop");
                $form->addInput("Enchantment", "Porkified", "Porkified");
                $form->addSlider("Level", 1, 5, 1, 1);
                $form->addInput("Price", 1, 1);
                $form->sendToPlayer($player);
            }
        }
    }
}