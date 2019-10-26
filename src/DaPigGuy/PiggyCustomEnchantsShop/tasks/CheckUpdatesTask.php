<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchantsShop\tasks;

use DaPigGuy\PiggyCustomEnchantsShop\PiggyCustomEnchantsShop;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

/**
 * Class CheckUpdatesTask
 * @package DaPigGuy\PiggyCustomEnchantsShop\tasks
 */
class CheckUpdatesTask extends AsyncTask
{
    /** @var string */
    private $version;
    /** @var string */
    private $api;

    /**
     * CheckUpdatesTask constructor.
     * @param string $version
     * @param string $api
     */
    public function __construct(string $version, string $api)
    {
        $this->version = $version;
        $this->api = $api;
    }

    public function onRun(): void
    {
        $releases = Internet::getURL("https://poggit.pmmp.io/releases.json?name=PiggyCustomEnchantsShop");
        if ($releases !== null) {
            $data = json_decode($releases, true);
            if ($this->isLatestVersion($data[0]["version"])) {
                if ($this->isAPICompatible($data[0]["api"][0])) {
                    $this->setResult($releases);
                }
            }
        }
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server): void
    {
        if ($this->getResult() !== null) {
            $plugin = $server->getPluginManager()->getPlugin("PiggyCustomEnchantsShop");
            if ($plugin instanceof PiggyCustomEnchantsShop && $plugin->isEnabled()) {
                $data = json_decode($this->getResult(), true);
                $plugin->getLogger()->info("PiggyCustomEnchantsShop v" . $data[0]["version"] . " is available for download at " . $data[0]["artifact_url"] . "/PiggyCustomEnchantsShop.phar");
            }
        }
    }

    /**
     * @param string $version
     * @return bool
     */
    public function isLatestVersion(string $version): bool
    {
        $versionInformation = explode(".", $version);
        $currentVersionInformation = explode(".", $this->version);
        if ($versionInformation[0] > $currentVersionInformation[0]) return true;
        if ($versionInformation[0] === $currentVersionInformation[0]) {
            if ($versionInformation[1] > $currentVersionInformation[1]) return true;
            if ($versionInformation[1] === $currentVersionInformation[1] && $versionInformation[2] > $currentVersionInformation[2]) return true;
        }
        return false;
    }

    /**
     * @param array $range
     * @return bool
     */
    public function isAPICompatible(array $range): bool
    {
        $lowestAPI = $range["from"];
        $highestAPI = $range["to"];
        $currentAPIInformation = explode(".", $this->api);
        $lowestAPIInformation = explode(".", $lowestAPI);
        $highestAPIInformation = explode(".", $highestAPI);
        if (
            $currentAPIInformation[0] >= $lowestAPIInformation[0] && $currentAPIInformation[0] <= $highestAPIInformation[0] &&
            $currentAPIInformation[1] >= $lowestAPIInformation[1] && $currentAPIInformation[1] <= $highestAPIInformation[1] &&
            $currentAPIInformation[2] >= $lowestAPIInformation[2] && $currentAPIInformation[2] <= $highestAPIInformation[2]
        ) {
            return true;
        }
        return false;
    }
}