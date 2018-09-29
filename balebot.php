<?php

/**
 * @package     Bale Php
 * @link        https://github.com/smartwf/balephp
 * @author      Smart <Hooshmandmrh@gmail.com>
 */
class BaleBot
{
    // Bale API Address
    protected $api = "https://apitest.bale.ai/v1/bots/http/";

    // Bale Bot Token
    protected $token;

    // Curl Variable
    protected $ch;

    // Datas
    protected $datas;

    // Default Chat Id
    protected $default_chat_id;

    // Default Message Id
    protected $default_message_id;

    /**
     *** initialize Class
     *
     * @param string $api_token The token looks something like e5r57et1b558c18506f4e231e9f2ba0228fe95au
     * @return bool
     */
    public function  __construct($api_token) {
        $this->token = $api_token;
        if (strlen($this->token) == 40) {
            $this->ch = curl_init();
            return true;
        }
        else
            return false;
    }

    /**
     *** Destruct Class
     */
    public function __destruct() {
        curl_close($this->ch);
    }

    /**
     *** Make Http Request
     *
     * @param object $datas  Datas for Send to Bale
     * @return object
     */
    private function make_http_request($datas, $message = true) {
        $url = $this->api.$this->token;
        $type = '$type';
        $datas = ($message) ? $this->_createDatas($datas) : $datas;

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($datas));
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $res = curl_exec($this->ch);

        if (curl_error($this->ch)) {
            $r = new \stdClass();
            $r->ok = false;
            $r->description = curl_error($this->ch);
            $r->errno = curl_errno($this->ch);
            return $r;
        } else {
            if ($this->isJson($res)) {
                $res = json_decode($res);
                if ($message && $res->body->$type == 'Success') {
                    $date = $res->body->obj->date;
                    $res = new \stdClass();
                    $res->ok = true;
                    $res->message_id = $datas->body->randomId;
                    $res->date = $date;
                }
            }
            return $res;
        }
    }

    /**
     *** Self Function to Send Message to Bale Server
     *
     * @param object $peer
     * @param object $message
     * @param object $quotedMessage
     * @return object
     */
    private function _sendMessage($peer, $message, $quotedMessage = null) {
        $type = '$type';
        $data = new \stdClass();

        $data->$type = 'Request';
        $data->body = new \stdClass();
        $data->body->$type = 'SendMessage';
        $data->body->randomId = (string) rand();
        $data->body->peer = $peer;
        $data->body->message = $message;
        $data->body->quotedMessage = $quotedMessage;
        $data->service = 'messaging';
        $data->id = (string) 0;

        return $data;
    }

    /**
     *** Self Function to Create peer from chat id
     *
     * @param string $chat_id
     * @return stdClass
     */
    private function _createPeer($chat_id) {
        $type = '$type';
        $chat_id = explode('|', $chat_id);
        $peer = new \stdClass();
        $peer->$type = ucfirst($chat_id[0]);
        $peer->id = $chat_id[1];
        $peer->accessHash = $chat_id[2];
        return $peer;
    }

    /**
     *** Self Function to create Quoted Message
     *
     * @param object $peer
     * @param object $message_id
     * @return null|object
     */
    private function _createQuotedMessage($peer, $message_id) {
        $quotedMessage = new \stdClass();
        $quotedMessage->messageId = $message_id;
        $quotedMessage->peer = $peer;

        if ($message_id != null)
            return $quotedMessage;
        else
            return null;
    }

    /**
     *** Self Function to create message
     *
     * @param string $type message type name
     * @param string|array $datas
     * @return object
     */
    private function _createMessage($type, $datas) {
        $t = '$type';
        $message = new \stdClass();
        $message->$t = ucfirst($type);

        switch ($type) {
            case 'text':
                $message->text = $datas;
                break;
            case 'templateMessage':
                $message->templateMessageId = "0";
                $message->generalMessage = $this->datas->message;
                $message->btnList = $datas;
                break;
            case 'json':
                $message->rawJson = new \stdClass();
                $message->rawJson->dataType = $datas[0];
                $message->rawJson->data = $datas[1];
                $message->rawJson = json_encode($message->rawJson);
                break;
            case 'location':
                $message = $this->_createMessage('json', ['location', ["location" => ["latitude" => $datas[0], "longitude" => $datas[1]]]]);
                break;
            case 'contact':
                $message = $this->_createMessage('json', ['contact', ["contact" => ["name" => $datas[0], "phones" => $datas[1], "emails" => $datas[2]]]]);
                break;
            case 'purchaseMessage':
                $message->amount = (string) $datas[2];
                $message->accountNumber = $datas[1];
                $message->msg = $datas[0];
                $message->moneyRequestType = new \stdClass();
                $message->moneyRequestType->$t = 'MoneyRequestNormal';
                $message->regexAmount = ($datas[2]=="0") ? '[]' : '['.$datas[2].']';
                break;
            case 'sticker':
                $sticker = explode('|',$datas[0]);
                $message->fastPreview = null;
                $message->stickerId = $sticker[0];
                $message->stickerCollectionId = $sticker[1];
                $message->stickerCollectionAccessHash = $sticker[2];
                $message->image256 = new \stdClass();
                $message->image256->width = 256;
                $message->image256->height = 256;
                $message->image256->fileSize = $sticker[5];
                $message->image256->fileLocation = new \stdClass();
                $message->image256->fileLocation->fileId = $sticker[3];
                $message->image256->fileLocation->accessHash = $sticker[4];
                $message->image256->fileLocation->fileStorageVersion = 1;
                $message->image512 = new \stdClass();
                $message->image512->width = 512;
                $message->image512->height = 512;
                $message->image512->fileSize = $sticker[6];
                $message->image512->fileLocation = new \stdClass();
                $message->image512->fileLocation->fileId = $sticker[3];
                $message->image512->fileLocation->accessHash = $sticker[4];
                $message->image512->fileLocation->fileStorageVersion = 1;
                break;
            case 'document':
                $file = explode('|',$datas[0]);
                $message->ext = null;
                $message->thumb = null;
                $message->caption = $this->_createMessage('text', $datas[3]);
                $message->fileSize = $file[2];
                $message->fileId = $file[0];
                $message->accessHash = $file[1];
                $message->fileStorageVersion = 1;
                $message->name = $datas[1];
                $message->algorithm = 'algorithm';
                $message->checkSum = 'checkSum';
                $message->mimeType = $datas[2];
                break;
            case 'photo':
                $message = $this->_createMessage('document', [$datas[0], $datas[1], 'image/jpeg', $datas[3]]);
                $message->ext = $datas[2];
                $message->thumb = ['width' => $message->ext['width'], 'height' => $message->ext['height'], 'thumb' => $datas[4]];
                break;
            case 'video':
                $message = $this->_createMessage('document', [$datas[0], $datas[1], 'video/mp4', $datas[3]]);
                $message->ext = $datas[2];
                $message->thumb = ['width' => $message->ext['width'], 'height' => $message->ext['height'], 'thumb' => $datas[4]];
                break;
            case 'voice':
                $message = $this->_createMessage('document',[$datas[0], $datas[1], 'audio/mp3', $datas[3]]);
                $message->ext = $datas[2];
                break;
        }

        return $message;
    }

    /**
     *** Self Function to create final datas
     *
     * @param object $data
     * @return object
     */
    private function _createDatas($data) {
        $peer = $this->_createPeer($data->chat_id);
        $message = $data->message;
        $quotedMessage = $this->_createQuotedMessage(($data->reply_to_user == null) ? $peer : $this->_createPeer($data->reply_to_user), $data->reply_to_message_id);

        if ($data->t)
            return $this->_sendMessage($peer, $message, $quotedMessage);
    }

    /**
     *** Self Function to check json validation
     *
     * @param string $json
     * @return bool
     */
    private function isJson($json) {
        json_decode($json);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     *** Self Function to create button
     *
     * @param string $text
     * @param string $value
     * @param int $action
     * @return object
     */
    private function _createBtn($text, $value, $action = 0) {
        $btn = new \stdClass();
        $btn->text = $text;
        $btn->value = $value;
        $btn->action = ($action!=null) ? $action : 0;
        return $btn;
    }

    /**
     *** Self Function to get url to upload file in Bale Server
     *
     * @param string $file_name
     * @return object
     */
    private function _getUploadUrl($file_name) {
        $type = '$type';
        $data = new \stdClass();

        $data->$type = 'Request';
        $data->body = new \stdClass();
        $data->body->$type = 'GetFileUploadUrl';
        $data->body->crc = (string) hexdec(hash_file('crc32', $file_name));
        $data->body->size = filesize($file_name);
        $data->body->isServer = false;
        $data->body->fileType = 'file';
        $data->service = 'files';
        $data->id = (string) 0;

        return $this->make_http_request($data, false);
    }

    /**
     *** Self Function to put data on server
     *
     * @param string $url
     * @param string $file_name
     * @param int $size
     * @return int
     */
    private function _putData($url, $file_name, $size) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['filesize: "'.$size.'"']);
        curl_setopt($ch, CURLOPT_INFILE, fopen($file_name, 'r'));
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code;

    }


    //\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\




    /********************************************
     *                                          *
     *               Bale functions             *
     *                                          *
     ********************************************/



    /**
     *** Use this method to send text messages
     *
     * @param string $text Text of the message to be sent
     * @return $this
     */
    public function sendMessage($text) {
        $message = $this->_createMessage('text', $text);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to send random text messages
     *
     * @param array $texts Texts of the message to send randomly
     * @return $this
     */
    public function sendRandomMessage(array $texts) {
        $text = '';
        if (count($texts) > 1)
            $text = $texts[rand(0, count($texts)-1)];
        $message = $this->_createMessage('text', $text);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     * @param $accountNumber
     * @param $image
     * @param int $amount
     * @param null|string $text
     * @return $this
     */
    public function sendPurchase($accountNumber, $image, $amount=0, $text='') {
        $msg = $this->_createMessage('photo', [$image,'donate.png', ['$type' => 'Photo', 'width' => 1024, 'height' => 1024] ,$text, '']);
        $message = $this->_createMessage('purchaseMessage', [$msg, $accountNumber, $amount]);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to send photos
     *
     * @param string $photo Photo to send. fileId and accessHash and fileSize (in the format 'id|hash|size')
     * @param string $filename name of file
     * @param int $width width of photo
     * @param int $height height of photo
     * @param string $caption Photo caption
     * @param string $thumb thumb of photo for preview
     * @return $this
     */
    public function sendPhoto($photo, $filename, $width, $height, $caption='', $thumb='') {
        $message = $this->_createMessage('photo', [$photo,$filename, ['$type' => 'Photo', 'width' => $width, 'height' => $height], $caption, $thumb]);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to send general files
     *
     * @param string $file file to send. fileId and accessHash and fileSize (in the format 'id|hash|size')
     * @param string $filename name of file
     * @param string $mimeType type of file
     * @param string $caption Photo caption
     * @return $this
     */
    public function sendDocument($file, $filename, $mimeType, $caption='') {
        $message = $this->_createMessage('document', [$file, $filename, $mimeType, $caption]);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to send video files
     *
     * @param string $video Video to send. fileId and accessHash and fileSize (in the format 'id|hash|size')
     * @param string $filename name of file
     * @param int $width width of video
     * @param int $height height of video
     * @param int $duration Duration of the audio in seconds
     * @param string $caption Photo caption
     * @param string $thumb thumb of photo for preview
     * @return $this
     */
    public function sendVideo($video, $filename, $width, $height, $duration, $caption='', $thumb='') {
        $message = $this->_createMessage('video', [$video, $filename, ['$type' => 'Video', 'width' => $width, 'height' => $height, 'duration' => $duration], $caption, $thumb]);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to send voice message
     *
     * @param string $voice Voice to send. fileId and accessHash and fileSize (in the format 'id|hash|size')
     * @param int $duration Duration of the audio in seconds
     * @param string $caption Photo caption
     * @return $this
     */
    public function sendVoice($voice, $duration, $caption='') {
        $filename = 'voice_msg_'.rand().'.opus';
        $message = $this->_createMessage('voice', [$voice, $filename, ['$type' => 'Voice', 'duration' => $duration], $caption]);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to send point on the map
     *
     * @param float $latitude Latitude of the location
     * @param float $longitude Longitude of the location
     * @return $this
     */
    public function sendLocation($latitude, $longitude) {
        $message = $this->_createMessage('location', [$latitude, $longitude]);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to send phone contacts
     *
     * @param string $name Contact's name
     * @param array|string $phones Contact's phone or phones
     * @param array|string $emails Contact's email or emails
     * @return $this
     */
    public function sendContact($name, $phones, $emails=null) {
        $message = $this->_createMessage('contact', [$name, (is_array($phones))?$phones:[$phones], ($emails!==null)?(is_array($emails))?$emails:[$emails]:[]]);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to send stickers
     *
     * @param string $sticker sticker to send. stickerId and stickerCollectionId and stickerCollectionAccessHash and fileId and accessHash and fileSize (in the format 'stickerId|stickerCollectionId|stickerHash|fileId|fileHash|file256Size|file512Size')
     * @return $this
     */
    public function sendSticker($sticker) {
        $message = $this->_createMessage('sticker', $sticker);
        $this->datas = (object) get_defined_vars();
        return $this;
    }

    /**
     *** Use this method to upload your file on Bale servers
     *
     * @param string $file_name
     * @return bool|stdClass
     */
    public function uploadFile($file_name) {
        $type = '$type';
        $size = filesize($file_name);
        $upload = $this->_getUploadUrl($file_name);

        if ($upload->body->$type == 'Success'){

            $url = $upload->body->obj->url;
            $file_id = $upload->body->obj->fileId;
            $file_access_hash = $upload->body->obj->userId;

            if ($this->_putData($url, $file_name, $size) == 200){

                $ret = new \stdClass();
                $ret->id = $file_id;
                $ret->access_hash = $file_access_hash;
                $ret->name = $file_name;
                $ret->size = $size;
                $ret->type = 'file';

                return $ret;

            }

        }

        return false;
    }


    //\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\




    /********************************************
     *                                          *
     *               Other functions            *
     *                                          *
     ********************************************/


    /**
     *** Use this method to create chat id
     *
     * @param string $peer_type type of peer (User or Group) (0 or 1)
     * @param string $peer_id id of peer
     * @param string $peer_accessHash peer access hash
     * @return string
     */
    public function createChatId($peer_type, $peer_id, $peer_accessHash) {
        return (ucfirst($peer_type)).'|'.$peer_id.'|'.$peer_accessHash;
    }

    /**
     *** Use this method to create chat id from peer
     *
     * @param object $peer peer
     * @return string|bool
     */
    public function createChatIdFromPeer($peer) {
        $peer = (object) $peer;
        if (!isset($peer->id))
            return false;
        $type = '$type';
        return $peer->$type.'|'.$peer->id.'|'.$peer->accessHash;
    }

    /**
     *** Use this method to create user id
     *
     * @param string $sender_type type of peer (User or Group) (0 or 1)
     * @param string $sender_id id of peer
     * @param string $sender_accessHash peer access hash
     * @return string
     */
    public function createUserId($sender_type, $sender_id, $sender_accessHash) {
        return $this->createChatId($sender_type, $sender_id, $sender_accessHash);
    }

    /**
     *** Use this method to create user id from sender
     *
     * @param object $sender sender
     * @return string|bool
     */
    public function createUserIdFromSender($sender) {
        return $this->createChatIdFromPeer($sender);
    }

    /**
     *** Use this method to create file | photo | video | voice | sticker file
     *
     * @param string $file_id id of file
     * @param string $file_accessHash file access has
     * @param string $file_size size of file
     * @return string
     */
    public function createFile($file_id, $file_accessHash, $file_size) {
        return $file_id.'|'.$file_accessHash.'|'.$file_size;
    }

    /**
     *** Use this method to create sticker
     *
     * @param string $sticker_id id of sticker
     * @param string $sticker_collection_id id of sticker collection
     * @param string $sticker_accessHash sticker access hash
     * @param string $sticker_file_id sticker file id
     * @param string $sticker_file_access_hash sticker file access hash
     * @param string $sticker_file256_size size of sticker in 256
     * @param string $sticker_file512_size size of sticker in 512
     * @return string
     */
    public function createSticker($sticker_id, $sticker_collection_id, $sticker_accessHash, $sticker_file_id, $sticker_file_access_hash, $sticker_file256_size, $sticker_file512_size) {
        return $sticker_id.'|'.$sticker_collection_id.'|'.$sticker_accessHash.'|'.$sticker_file_id.'|'.$sticker_file_access_hash.'|'.$sticker_file256_size.'|'.$sticker_file512_size;
    }

    /**
     *** Use This Method to change Bale Api Address because bale api now is demo version
     *
     * @param string $api api address
     */
    public function setApi(string $api) {
        $this->api = $api;
    }

    /**
     *** Use This Method to set Default chat id
     *
     * @param mixed $default_chat_id default chat id
     */
    public function setDefaultChatId($default_chat_id) {
        $this->default_chat_id = $default_chat_id;
    }

    /**
     *** Use This Method to set Default message id
     *
     * @param mixed $default_message_id default message id
     */
    public function setDefaultMessageId($default_message_id) {
        $this->default_message_id = $default_message_id;
    }


    //\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\




    /********************************************
     *                                          *
     *              functions Method            *
     *                                          *
     ********************************************/



    /**
     *** Use this method to reply messages
     *
     * @param string $message_id If the message is a reply, ID of the original message
     * @param string $user peer for reply
     * @return $this
     */
    public function replyTo($message_id=null, $user=null) {
        if ($message_id === null)
            $message_id = $this->default_message_id;
        $this->datas->reply_to_message_id = $message_id;
        $this->datas->reply_to_user = $user;
        return $this;
    }

    /**
     *** Use this method to add buttons to message
     *
     * @param array $btnList array of btn exp:(  [['btn name','command1'],['btn name','command2']]  )
     * @return $this
     */
    public function withBtn($btnList) {
        $btnList2=[];
        foreach ($btnList as $btn){
            $btnList2[] = $this->_createBtn($btn[0], $btn[1], (isset($btn[2])) ? $btn[2] : null);
        }
        $this->datas->message = $this->_createMessage('templateMessage', $btnList2);
        return $this;
    }

    /**
     *** Use this method to send messages
     *
     * @param string $chat_id peer type and peer id and peer Hash (in the format 'type|id|hash')
     * @return object
     */
    public function send($chat_id=null){
        if ($chat_id === null)
            $chat_id = $this->default_chat_id;
        $this->datas->t = 1;
        $this->datas->chat_id = $chat_id;
        return $this->make_http_request($this->datas);
    }

}
