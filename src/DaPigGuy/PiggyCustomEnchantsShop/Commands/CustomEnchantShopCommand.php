<?php

namespace DaPigGuy\PiggyCustomEnchantsShop\Commands;

use DaPigGuy\PiggyCustomEnchantsShop\Main;
use DaPigGuy\PiggyCustomEnchantsShop\Shops\UIShop;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class CustomEnchantShopCommand
 * @package DaPigGuy\PiggyCustomEnchantsShop\Commands
 */
class CustomEnchantShopCommand extends PluginCommand
{
    /** @var array */
    private $confirmations;

    /**
     * CustomEnchantShopCommand constructor.
     * @param      $name
     * @param Main $plugin
     */
    public function __construct($name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription("");
        $this->setUsage("/customenchantshop [add]");
        $this->setAliases(["ceshop"]);
        $this->setPermission("piggycustomenchantsshop.command.ceshop");
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if (isset($args[0])) {
                switch ($args[0]) {
                    case "add":
                        if (!$sender->hasPermission("piggycustomenchantsshop.command.ceshop.add")) {
                            $sender->sendMessage(TextFormat::RESET . "You do not have permission to do this.");
                            return;
                        }
                        if (count($args) >= 4) {
                            $args[1] = ucfirst($args[1]);
                            if (is_null($enchantment = CustomEnchants::getEnchantmentByName($args[1])) && is_null($enchantment = CustomEnchants::getEnchantment($args[1]))) {
                                $sender->sendMessage(TextFormat::RED . "Invalid enchantment.");
                                return;
                            }
                            if (!is_numeric($args[2])) {
                                $sender->sendMessage(TextFormat::RED . "Level must be numerical.");
                                return;
                            }
                            if (!is_numeric($args[3])) {
                                $sender->sendMessage(TextFormat::RED . "Price must be numerical.");
                                return;
                            }
                            $plugin->getShopManager()->addShop(new UIShop($args[1], $args[2], $args[3], $plugin->getShopManager()->getNextId()));
                            $sender->sendMessage(TextFormat::GREEN . "Shop added!");
                        } else {
                            if ($sender instanceof Player) {
                                $this->addShop($sender);
                                return;
                            }
                            $sender->sendMessage("Usage: /ceshop add <enchantment> <level> <price>");
                        }
                        return;
                    default:
                        $sender->sendMessage("Usage: /customenchantshop [add]");
                        return;
                }
            }
            if ($sender instanceof Player) {
                if (!$sender->hasPermission("piggycustomenchantsshop.command.ceshop.use")) {
                    $sender->sendMessage(TextFormat::RESET . "You do not have permission to do this.");
                    return;
                }
                $this->shopForm($sender);
                return;
            }
            $sender->sendMessage("Usage: /customenchantshop <add>");
        }
    }

    /**
     * @param Player $player
     */
    public function shopForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $form = new SimpleForm(function (Player $player, ?int $data) {
                $plugin = $this->getPlugin();
                if ($plugin instanceof Main) {
                    if (!is_null($data) && count($plugin->getShopManager()->getShops()) > $data) {
                        $this->confirmTransaction($player, $data);
                    }
                }
            });
            $form->setTitle(TextFormat::GREEN . "Custom Enchants Shop");
            foreach ($plugin->getShopManager()->getShops() as $shop) {
                $form->addButton($shop->getEnchantment() . " " . $plugin->getCustomEnchants()->getRomanNumber($shop->getEnchantLevel()));
            }
            $form->addButton("Exit");
            $player->sendForm($form);
        }
    }

    /**
     * @param Player $player
     * @param        $index
     */
    public function confirmTransaction(Player $player, $index)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $shop = $plugin->getShopManager()->getShopById($index);
            $form = new SimpleForm(function (Player $player, ?int $data) {
                if (!is_null($data)) {
                    $plugin = $this->getPlugin();
                    if ($plugin instanceof Main) {
                        switch ($data) {
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
            $player->sendForm($form);
            $this->confirmations[$player->getLowerCaseName()] = $index;
        }
    }

    /**
     * @param Player $player
     */
    public function addShop(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $form = new CustomForm(function (Player $player, ?array $data) {
                $plugin = $this->getPlugin();
                if ($plugin instanceof Main) {
                    if (!is_null($data)) {
                        if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                            $data[0] = ucfirst($data[0]);
                            if (is_null($enchantment = CustomEnchants::getEnchantmentByName($data[0])) && is_null($enchantment = CustomEnchants::getEnchantment($data[1]))) {
                                $player->sendMessage(TextFormat::RED . "Invalid enchantment.");
                                return;
                            }
                            if (!is_numeric($data[1])) {
                                $player->sendMessage(TextFormat::RED . "Level must be numerical.");
                                return;
                            }
                            if (!is_numeric($data[2])) {
                                $player->sendMessage(TextFormat::RED . "Price must be numerical.");
                                return;
                            }
                            if ($data[1] > $max = $plugin->getCustomEnchants()->getEnchantMaxLevel($enchantment)) {
                                $data[1] = $max;
                            }
                            $plugin->getShopManager()->addShop(new UIShop($data[0], $data[1], $data[2], $plugin->getShopManager()->getNextId()));
                            $player->sendMessage(TextFormat::GREEN . "Shop added!");
                        }
                    }
                }
            });
            $form->setTitle("New Enchant Shop");
            $form->addInput("Enchantment", "Porkified", "Porkified");
            $form->addSlider("Level", 1, 5, 1, 1);
            $form->addInput("Price", 1, 1);
            $player->sendForm($form);
        }
    }
}