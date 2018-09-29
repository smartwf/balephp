<?php

/**
 * @package     Bale Php
 * @link        https://github.com/smartwf/balephp
 * @author      Smart <Hooshmandmrh@gmail.com>
 */

// get update data
$update = json_decode(file_get_contents("php://input"));

class BaleUpdate{

    //update type
    protected static $update_type;

    // chat id
    protected static $chat_id;

    //user id
    protected static $user_id;

    // chat type
    protected static $chat_type;

    //message id
    protected static $message_id;

    // message type
    protected static $message_type;

    //message date
    protected static $message_date;

    //message
    protected static $message;

    //sticker
    protected static $sticker;

    //contact
    protected static $contact;

    //location
    protected static $location;

    //file
    protected static $file;

    //file name
    protected static $file_name;

    // file type
    protected static $file_type;

    //file width
    protected static $file_width;

    //file height
    protected static $file_height;

    //file duration
    protected static $file_duration;

    //file thumb
    protected static $file_thumb;

    //file caption of message
    protected static $caption;

    //pay information
    protected static $pay;



    /**
     *** initialize Class and get updates
     *
     * @return null
     */
    public static function getNewUpdate(){
        // $type
        $type = '$type';

        // get update variable
        global $update;

        // update Type
        self::$update_type = $update->body->$type;

        self::$chat_id = $update->body->peer->$type.'|'.$update->body->peer->id.'|'.$update->body->peer->accessHash;
        self::$chat_type = $update->body->peer->$type;

        self::$user_id = $update->body->sender->$type.'|'.$update->body->sender->id.'|'.$update->body->sender->accessHash;

        self::$message = $update->body->message;
        self::$message_id = $update->body->randomId;
        self::$message_type = $update->body->message->$type;
        self::$message_date = $update->body->date;

        self::$sticker = self::$message->stickerId.'|'.self::$message->stickerCollectionId.'|'.self::$message->stickerCollectionAccessHash.'|'.self::$message->image512->fileLocation->fileId.'|'.self::$message->image512->fileLocation->accessHash.'|'.self::$message->image256->fileSize.'|'.self::$message->image512->fileSize;



        $rawJson = json_decode(self::$message->rawJson);

        self::$contact = $rawJson->data->contact;

        self::$location = $rawJson->data->location;

        self::$file = self::$message->fileId.'|'.self::$message->accessHashm.'|'.self::$message->fileSize;
        self::$file_name = self::$message->name;
        self::$file_type = self::$message->mimeType;
        self::$file_width = self::$message->ext->width;
        self::$file_height = self::$message->ext->height;
        self::$file_duration = self::$message->ext->duration;
        self::$file_thumb = self::$message->thumb->thumb;
        self::$caption = self::$message->caption->text;

        self::$pay = new \stdClass();
        self::$pay->description = self::$message->message->transferInfo->items[4]->value->text;
        self::$pay->amount = self::$message->message->transferInfo->items[8]->value->value;
        self::$pay->date = self::$message->message->transferInfo->items[9]->value->value;
        self::$pay->status = strtolower(self::$message->message->transferInfo->items[10]->value->text);
        self::$pay->trace_id = strtolower(self::$message->message->transferInfo->items[12]->value->value);
        self::$pay->payer = new \stdClass();

        $msg = self::$message->message->message->items[0]->value;
        $s = strpos($msg,'واریزکننده') + 35;
        $e = strpos($msg,'شماره پیگیری') -$s-18;
        $payer = explode('-',substr($msg,$s,$e));

        self::$pay->payer->name = trim($payer[0]);
        self::$pay->payer->username = trim($payer[1]);
    }

    /**
     *** is group
     *
     * @return bool
     */
    public static function isGroup(){
        if (self::$chat_type == 'Group')
            return true;
        return false;
    }

    /**
     *** is user
     *
     * @return bool
     */
    public static function isUser(){
        if (self::$chat_type == 'User')
            return true;
        return false;
    }

    /**
     *** use this method to get update type
     *
     * @return bool
     */
    public static function updateType(){
        return self::$update_type;
    }

    /**
     *** use this method to get chat id
     *
     * @return string
     */
    public static function chatId(){
        return self::$chat_id;
    }

    /**
     *** use this method to get user id
     *
     * @return string
     */
    public static function userId(){
        return self::$user_id;
    }

    /**
     *** use this method to get message id
     *
     * @return string
     */
    public static function messageId(){
        return self::$message_id;
    }

    /**
     *** use this method to get message
     *
     * @return string
     */
    public static function message(){
        return self::$message;
    }

    /**
     *** use this method to get sticker
     *
     * @return string
     */
    public static function sticker(){
        return self::$sticker;
    }

    /**
     *** use this method to get chat type
     *
     * @return string
     */
    public static function chatType(){
        return self::$chat_type;
    }

    /**
     *** use this method to get chat id
     *
     * @return string
     */
    public static function messageDate(){
        return self::$message_date;
    }


    /**
     *** use this method to get message type
     *
     * @return string
     */
    public static function messageType(){
        return self::$message_type;
    }

    /**
     *** use this method to get contact
     *
     * @return object
     */
    public static function contact(){
        return self::$contact;
    }

    /**
     *** use this method to get location
     * @return object
     */
    public static function location(){
        return self::$location;
    }

    /**
     *** use this method to get file
     *
     * @return string
     */
    public static function file(){
        return self::$file;
    }

    /**
     *** use this method to get file name
     *
     * @return string
     */
    public static function fileName(){
        return self::$file_name;
    }

    /**
     *** use this method to get file type
     *
     * @return string
     */
    public static function fileType(){
        return self::$file_type;
    }

    /**
     *** use this method to get file width
     *
     * @return int
     */
    public static function fileWidth(){
        return self::$file_width;
    }

    /**
     *** use this method to get file height
     *
     * @return int
     */
    public static function fileHeight(){
        return self::$file_height;
    }

    /**
     *** use this method to get file duration
     *
     * @return int
     */
    public static function fileDuration(){
        return self::$file_duration;
    }

    /**
     *** use this method to get caption
     *
     * @return string
     */
    public static function caption(){
        return self::$caption;
    }

    /**
     *** use this method to get file thumb
     *
     * @return string
     */
    public static function fileThumb(){
        return self::$file_thumb;

    }

    /**
     *** use this method to get pay
     *
     * @return object
     */
    public static function pay(){
        return self::$pay;

    }
}