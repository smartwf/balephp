<?php

/**
 * @package     Bale Php
 * @link        https://github.com/smartwf/balephp
 * @author      Smart <Hooshmandmrh@gmail.com>
 */


class BalePhp{

    /**
     *** initialize Class and get updates
     *
     * @param string $token token of your bot
     * @return null
     */
    public static function run($token){
        BaleUpdate::getNewUpdate();
        $baleBot = new BaleBot($token);
        BaleHandler::setBaleBot($baleBot);
    }

}