<?php

namespace PiggyCustomEnchantsShop;

use PiggyCustomEnchantsShop\Economy\BasicEconomy;
use PiggyCustomEnchantsShop\Economy\EconomyAPI;

use PiggyCustomEnchantsShop\Provider\YAMLProvider;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

/**
 * Class Main
 * @package PiggyCustomEnchantsShop
 */
class Main extends PluginBase
{
    public $ce;
    public $economy;
    public $provider;

    private $economymanager;

    public function onEnable()
    {
        if ($this->checkDependents()) {
            $this->saveDefaultConfig();
            switch ($this->economy->getName()) {
                case "EconomyAPI":
                    $this->economymanager = new EconomyAPI($this, $this->economy);
                    break;
            }
            switch ($this->getConfig()->getNested("provider")){
                case "yml":
                case "yaml":
                default:
                    $this->provider = new YAMLProvider($this);
                    break;
            }
            $this->provider->initShops();
            $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
            $this->getLogger()->info(TextFormat::GREEN . "Enabled.");
        }
    }

    /**
     * @return bool
     */
    public function checkDependents()
    {
        $this->ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        if (is_null($this->ce)) {
            $this->getLogger()->critical("PiggyCustomEnchants is required.");
            $this->getPluginLoader()->disablePlugin($this);
            return false;
        }
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if (is_null($this->economy)) {
            $this->getLogger()->critical("EconomyAPI is required.");
            $this->getPluginLoader()->disablePlugin($this);
            return false;
        }
        return true;
    }

    /**
     * @return Plugin
     */
    public function getEconomy()
    {
        return $this->economy;
    }

    /**
     * @return BasicEconomy
     */
    public function getEconomyManager()
    {
        return $this->economymanager;
    }

    /**
     * @return YAMLProvider
     */
    public function getProvider(){
        return $this->provider;
    }

}