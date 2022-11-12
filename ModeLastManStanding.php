<?php

namespace EvoSC\Modules\LastManStandingCup;

use EvoSC\Classes\File;
use EvoSC\Classes\Log;
use EvoSC\Classes\Module;
use EvoSC\Classes\RestClient;
use EvoSC\Interfaces\ModuleInterface;
use EvoSC\Modules\MatchSettingsManager\MatchSettingsManager;
use EvoSC\Modules\MatchSettingsManager\ModeScriptSettings;
use GuzzleHttp\Exception\GuzzleException;

class ModeLastManStanding extends Module implements ModuleInterface
{
    /**
     * URL to the raw script
     */
    const SCRIPT_SOURCE = 'https://raw.githubusercontent.com/BossBravo/Trackmania2020_LastManStandingCup/main/LastManStandingCup.Script.txt';

    /**
     * The script name
     */
    const SCRIPT_NAME = 'Modes/TrackMania/LastManStandingCup.Script.txt';

    /**
     * @param string $mode
     * @param bool $isBoot
     * @return mixed|void
     * @throws \EvoSC\Exceptions\FileAlreadyExistsException
     * @throws \EvoSC\Exceptions\FilePathNotAbsoluteException
     * @throws \EvoSC\Modules\MatchSettingsManager\Exceptions\GameModeAlreadyDefinedException
     */
    public static function start(string $mode, bool $isBoot = false)
    {
        if ($isBoot) {
            if (!MatchSettingsManager::gameModeExists(self::SCRIPT_NAME)) {
                Log::warning('Game mode ' . basename(self::SCRIPT_SOURCE) . ' is missing, attempting download.');
                self::downloadGameMode();
            }

            ModeScriptSettings::extend(self::SCRIPT_NAME, 'TM_Rounds_Online.Script.txt');
        }
    }

    /**
     * @return void
     * @throws \EvoSC\Exceptions\FileAlreadyExistsException
     * @throws \EvoSC\Exceptions\FilePathNotAbsoluteException
     */
    public static function downloadGameMode()
    {
        try {
            $response = RestClient::get(self::SCRIPT_SOURCE);

            if ($response->getStatusCode() == 200) {
                File::put(modeScriptsDir(self::SCRIPT_NAME), $response->getBody());
                Log::info('Downloaded game mode ' . basename(self::SCRIPT_SOURCE));
            } else {
                self::installFromLocalFile();
            }
        } catch (GuzzleException $e) {
            self::installFromLocalFile();
        }
    }

    /**
     * @return void
     * @throws \EvoSC\Exceptions\FileAlreadyExistsException
     * @throws \EvoSC\Exceptions\FilePathNotAbsoluteException
     */
    private static function installFromLocalFile()
    {
        Log::error('Failed to download latest version of game mode "' . basename(self::SCRIPT_SOURCE) . '". Installing from local file.');
        File::copy(__DIR__ . '/GameModes/LastManStandingCup.Script.txt', modeScriptsDir(self::SCRIPT_NAME));
    }
}