<?php

/**
 * @package     Bale Php
 * @link        https://github.com/smartwf/balephp
 * @author      Smart <Hooshmandmrh@gmail.com>
 */


class BalePhp{

    // Variable of BaleBot Class
    protected static $bot;


    /**
     *** initialize Class and get updates
     *
     * @param string $token token of your bot
     * @return null
     */
    public static function run($token){
        BaleUpdate::getNewUpdate();
        self::$bot = new BaleBot($token);
        BaleHandler::setBaleBot(self::$bot);
    }


    /**
     *** use this method to get bot
     *
     * @return BaleBot
     */
    public static function bot(){
        return self::$bot;
    }

}
