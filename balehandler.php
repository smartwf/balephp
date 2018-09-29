<?php

/**
 * @package     Bale Php
 * @link        https://github.com/smartwf/balephp
 * @author      Smart <Hooshmandmrh@gmail.com>
 */

class BaleHandler{

    //handel back chat id;
    protected static $handle_back_chat_id;

    //bale bot variable
    protected static $bot;

    // answered or not
    protected static $answered=false;


    /**
     *** if text has x
     *
     * @param string $text text
     * @param string $has word to find
     * @return bool
     */
    private static function textHas($text,$has){
        if (strpos($text, $has) !== false)
            return true;
        return false;
    }

    /**
     *** cache data
     *
     * @param string $key cache key name
     * @param mixed $val value to cache
     * @return null
     */
    private static function cache_set($key, $val) {
        $val = var_export($val, true);

        $val = str_replace('stdClass::__set_state', '(object)', $val);

        $tmp = "/tmp/$key." . uniqid('', true) . '.tmp';

        file_put_contents($tmp, '<?php $val = ' . $val . ';', LOCK_EX);
        rename($tmp, "/tmp/$key");
    }

    /**
     *** get cached data
     *
     * @param string $key cache key name
     * @return mixed
     */
    private static function cache_get($key) {
        @include "/tmp/$key";
        return isset($val) ? $val : null;
    }

    /**
     *** Use this function to initialize function
     *
     * @param BaleBot $balebot
     */
    public static function setBaleBot(BaleBot $balebot){
        self::$bot = $balebot;
        $balebot->setDefaultChatId(BaleUpdate::chatId());
        $balebot->setDefaultMessageId(BaleUpdate::messageId());
    }

    /**
     *** Use this function to get User Step
     *
     * @param string $user_id
     * @return int
     */
    public static function getStep($user_id = null){
        $user_id = ($user_id === null)?BaleUpdate::userId():$user_id;
        $uId = hexdec(crc32($user_id));
        $step = self::cache_get('step'.$uId);
        return ($step != null)?$step:0;
    }

    /**
     *** Use this function to set User Step
     *
     * @param string $user_id
     * @param int $step set user step
     * @return null
     */
    public static function setStep($user_id = null,$step = 0){
        $user_id = ($user_id === null)?BaleUpdate::userId():$user_id;
        $uId = hexdec(crc32($user_id));
        self::cache_set('step'.$uId,$step);
    }

    /**
     *** Use this function to go next Step
     *
     * @param string $user_id
     * @return null
     */
    public static function nextStep($user_id = null){
        $user_id = ($user_id === null)?BaleUpdate::userId():$user_id;
        $uId = hexdec(crc32($user_id));
        $step = self::getStep($user_id);
        self::cache_set('step'.$uId,$step+1);
    }

    /**
     *** Use this function to go before Step
     *
     * @param string $user_id
     * @return null
     */
    public static function beforeStep($user_id = null){
        $user_id = ($user_id === null)?BaleUpdate::userId():$user_id;
        $uId = hexdec(crc32($user_id));
        $step = self::getStep($user_id);
        self::cache_set('step'.$uId,($step == 0)?0:$step-1);
    }

    /**
     *** Use this function to reset Step
     *
     * @param string $user_id
     * @return null
     */
    public static function resetStep($user_id = null){
        $user_id = ($user_id === null)?BaleUpdate::userId():$user_id;
        self::setStep($user_id,0);
    }

    /**
     *** Use this function to cache Data
     *
     * @param string $name name of cached data
     * @param mixed $val value to cache
     * @param string $user_id
     * @return null
     */
    public static function cacheData($name,$val,$user_id = null){
        $user_id = ($user_id === null)?BaleUpdate::userId():$user_id;
        $uId = hexdec(crc32($user_id));
        self::cache_set($name.$uId,$val);
    }

    /**
     *** Use this function to get cached Data
     *
     * @param string $name name of cached data
     * @param string $user_id
     * @return null
     */
    public static function getCachedData($name,$user_id = null){
        $user_id = ($user_id === null)?BaleUpdate::userId():$user_id;
        $uId = hexdec(crc32($user_id));
        return self::cache_get($name.$uId);
    }

    /**
     *** Use this function to clear cached Data
     *
     * @param string $name name of cached data
     * @return null
     */
    public static function clearCache($name,$user_id = null){
        $user_id = ($user_id === null)?BaleUpdate::userId():$user_id;
        $uId = hexdec(crc32($user_id));
        /** @scrutinizer ignore-unhandled */ @unlink("/tmp/$name".$uId);
    }



    //\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\




    /********************************************
     *                                          *
     *              Handle functions            *
     *                                          *
     ********************************************/


    /**
     *** Use this method to group handle
     *
     * @param bool $condition
     * @param mixed $function
     * @return bool|mixed
     */
    public static function handleGroup($condition,$function){
        if ($condition){
            if( is_callable($function))
                return $function(self::$bot);
            else
                return false;
        }
    }

    /**
     *** Use this method to handle step
     *
     * @param int $step
     * @param mixed $function
     * @return bool|mixed
     */
    public static function handleStep($step,$function){
        if (self::getStep() == $step){
            if( is_callable($function))
                return $function(self::$bot);
            else
                return false;
        }
    }

    /**
     *** Use this method to Handle Text
     *
     * @param string|array $text text to handle
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleText($text,$function){
        if (BaleUpdate::updateType() == 'Message' && BaleUpdate::messageType() == 'Text') {
            $text = (is_array($text))?$text:[$text];
            foreach ($text as $t) {
                if (strtolower(BaleUpdate::message()->text) == strtolower($t)) {
                    self::$answered = true;
                    return $function(self::$bot);
                }
            }
        }
    }

    /**
     *** Use this method to Handle exact text
     *
     * @param string|array $text text to handel
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleTextE($text,$function){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Text'){
            $text = (is_array($text))?$text:[$text];
            foreach ($text as $t) {
                if (BaleUpdate::message()->text==$t){
                    self::$answered = true;
                    return $function(self::$bot);
                }
            }
        }
    }

    /**
     *** Use this method to Handle text if has x
     *
     * @param string|array $text has
     * @param mixed $function function to run
     * @param bool $sendIfNotAnswered send if not answered
     * @return mixed
     */
    public static function handleTextHas($text,$function,$sendIfNotAnswered=true){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Text'){
            $text = (is_array($text))?$text:[$text];
            foreach ($text as $t) {
                if (self::textHas(strtolower(BaleUpdate::message()->text), strtolower($t))) {
                    if ($sendIfNotAnswered === true) {
                        if (self::$answered === false) {
                            self::$answered = true;
                            return $function(self::$bot);
                        }
                    } else {
                        self::$answered = true;
                        return $function(self::$bot);
                    }
                }
            }
        }
    }

    /**
     *** Use this method to Handle Else Message
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleTextElse($function){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Text'){
            if (self::$answered===false){
                self::$answered=true;
                return $function(self::$bot);
            }
        }
    }

    /**
     *** Use this method to Handle Command
     *
     * @param string|array $command command to handle
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleCommand($command,$function){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='TemplateMessageResponse'){
            $command = (is_array($command))?$command:[$command];
            foreach ($command as $com) {
                if (strtolower(BaleUpdate::message()->textMessage) == strtolower($com)) {
                    self::$answered = true;
                    return $function(self::$bot);
                }
            }
        }
    }

    /**
     *** Use this method to Handle Else Command
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleCommandElse($function){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='TemplateMessageResponse'){
            self::$answered = true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle Sticker
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleSticker($function){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Sticker'){
            self::$answered=true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle Contact
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleContact($function){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Json' && json_decode(BaleUpdate::message()->rawJson)->dataType=='contact'){
            self::$answered=true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle Location
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleLocation($function){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Json' && json_decode(BaleUpdate::message()->rawJson)->dataType=='location'){
            self::$answered=true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle Document
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleDocument($function){
        $type = '$type';
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Document' && BaleUpdate::message()->ext->$type != 'Video' &&  BaleUpdate::message()->ext->$type != 'Photo' && BaleUpdate::message()->ext->$type != 'Voice'){
            self::$answered=true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle Photo
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handlePhoto($function){
        $type = '$type';
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Document' && BaleUpdate::message()->ext->$type == 'Photo'){
            self::$answered=true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle Video
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleVideo($function){
        $type = '$type';
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Document' && BaleUpdate::message()->ext->$type == 'Video'){
            self::$answered=true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle Voice
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleVoice($function){
        $type = '$type';
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='Document' && BaleUpdate::message()->ext->$type == 'Voice'){
            self::$answered=true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle pay
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handlePay($function){
        $type = '$type';
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType()=='BankMessage' && BaleUpdate::message()->message->$type == 'ReceiptMessage' && self::textHas(BaleUpdate::message()->message->message->items[0]->value,'#دریافت')){
            self::$answered=true;
            return $function(self::$bot);
        }
    }

    /**
     *** Use this method to Handle something else
     *
     * @param mixed $function function to run
     * @return mixed
     */
    public static function handleElse($function){
        if (BaleUpdate::updateType()=='Message' && BaleUpdate::messageType() != null){
            if (self::$answered===false){
                self::$answered=true;
                return $function(self::$bot);
            }
        }
    }

    /**
     *** Use this method to Handle Back any message
     *
     * @param null|string $chat_id
     * @param string $types types of Handle Back - default 'text,sticker,contact,location,document,photo,video,voice';
     */
    public static function handleBack($chat_id=null,$types='text,sticker,contact,location,document,photo,video,voice'){

        self::$handle_back_chat_id = ($chat_id === null)?BaleUpdate::chatId():$chat_id;

        $types=strtolower($types);

        if (self::textHas($types,'text'))
            self::handleTextElse(function (){
                self::$bot->sendMessage(BaleUpdate::message()->text)->send(self::$handle_back_chat_id);
            });

        if (self::textHas($types,'sticker'))
            self::handleSticker(function (){
                self::$bot->sendSticker(BaleUpdate::sticker())->send(self::$handle_back_chat_id);
            });

        if (self::textHas($types,'contact'))
            self::handleContact(function (){
                $contact = BaleUpdate::contact();
                self::$bot->sendContact($contact->name,$contact->phones,$contact->emails)->send(self::$handle_back_chat_id);
            });

        if (self::textHas($types,'location'))
            self::handleLocation(function (){
                $location = BaleUpdate::location();
                self::$bot->sendLocation($location->latitude,$location->longitude)->send(self::$handle_back_chat_id);
            });

        if (self::textHas($types,'document'))
            self::handleDocument(function (){
                self::$bot->sendDocument(BaleUpdate::file(),BaleUpdate::fileName(),BaleUpdate::fileType(),BaleUpdate::caption())->send(self::$handle_back_chat_id);
            });

        if (self::textHas($types,'photo'))
            self::handlePhoto(function (){
                self::$bot->sendPhoto(BaleUpdate::file(),BaleUpdate::fileName(),BaleUpdate::fileWidth(),BaleUpdate::fileHeight(),BaleUpdate::caption(),BaleUpdate::fileThumb())->send(self::$handle_back_chat_id);
            });

        if (self::textHas($types,'voide'))
            self::handleVideo(function (){
                self::$bot->sendVideo(BaleUpdate::file(),BaleUpdate::fileName(),BaleUpdate::fileWidth(),BaleUpdate::fileHeight(),BaleUpdate::fileDuration(),BaleUpdate::caption(),BaleUpdate::fileThumb())->send(self::$handle_back_chat_id);
            });

        if (self::textHas($types,'voice'))
            self::handleVoice(function (){
                self::$bot->sendVoice(BaleUpdate::file(),BaleUpdate::fileDuration())->send(self::$handle_back_chat_id);
            });


    }
}
