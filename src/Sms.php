<?php

namespace Descom\Sms;

use Descom\Sms\Auth\AuthInterface as Auth;
use Descom\Sms\Exceptions\MessageTextAlreadyExists;
use Descom\Sms\Exceptions\RequestFail;
use Descom\Sms\Http\Http;

class Sms
{
    /**
     * The headers for request API.
     *
     * @var array
     */
    private $headers = [
        'Content-Type'  => 'application/json',
    ];

    /**
     * Define the app and version of client.
     *
     * @var string
     */
    private $app = 'php-sms v1.0.10';

    /**
     * Define if then sent is dryrun.
     *
     * @var bool
     */
    private $dryrun = false;

    /**
     * Define if text must be sanitize to GSM 7bits.
     *
     * @var bool
     */
    private $sanitize = false;

    /**
     * Define if the sender no must be force in server.
     *
     * @var bool
     */
    private $sender_not_force = false;

    /**
     * Define if then messages for sent.
     *
     * @var array
     */
    private $messages = [];

    /**
     * Create a new sms instance.
     *
     * @param \Descom\Sms\Auth\BaseAuth $auth
     *
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->headers = array_merge($this->headers, $auth->headers());

        $this->headers = array_merge($this->headers, [
            'DSMS-App' => $this->app,
        ]);
    }

    /**
     * Set the app and version of client.
     *
     * @param string $app
     *
     * @var string
     */
    public function setApp($app)
    {
        $this->app = $app;

        $this->headers = array_merge($this->headers, [
            'DSMS-App' => $this->app,
        ]);

        return $this;
    }

    /**
     * Set if the sent is dryrun.
     *
     * @param bool $dryrun
     *
     * @return $this
     */
    public function setDryrun($dryrun)
    {
        $this->dryrun = $dryrun;

        return $this;
    }

    /**
     * Set if text must be sanitize to GSM 7bits.
     *
     * @param bool $sanitize
     *
     * @return $this
     */
    public function setSanitize($sanitize)
    {
        $this->sanitize = $sanitize;

        return $this;
    }

    /**
     * Set if sender no must be force in server.
     *
     * @param bool $sanitize
     *
     * @return $this
     */
    public function setSenderNotForce($sender_not_force)
    {
        $this->sender_not_force = $sender_not_force;

        return $this;
    }

    /**
     * Add headers.
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addHeader($name, $value)
    {
        $this->headers = array_merge($this->headers, [
            $name => $value,
        ]);

        return $this;
    }

    /**
     * Add a message in sent.
     *
     * @param \Descom\Sms\Message $message
     * @param bool                $control
     *
     * @return $this
     */
    public function addMessage(Message $message, $control = true)
    {
        $message_text = $message->getText();

        if ($control) {
            foreach ($this->messages as $cur_message) {
                if ($cur_message->getText() == $message_text) {
                    throw new MessageTextAlreadyExists($message_text);
                }
            }
        }

        $this->messages[] = $message;

        return $this;
    }

    /**
     * Get the balance in platform.
     *
     * @return float
     */
    public function getBalance()
    {
        $http = new Http();

        $response = $http->sendHttp('GET', 'balance', $this->headers);

        if ($response->status == 200) {
            $data = json_decode($response->message);

            return $data->balance;
        }

        throw new RequestFail($response->message, $response->status);
    }

    /**
     * Get the list with authorized senderID.
     *
     * @return array
     */
    public function getSenderID($details = false)
    {
        $http = new Http();

        if ($details) {
            $response = $http->sendHttp('POST', 'senderID', $this->headers, [
                'details' => 1,
            ]);
        } else {
            $response = $http->sendHttp('GET', 'senderID', $this->headers);
        }

        if ($response->status == 200) {
            $data = json_decode($response->message);

            return $data;
        }

        throw new RequestFail($response->message, $response->status);
    }

    /**
     * Send SMS's to the platform.
     *
     * @return object
     */
    public function send()
    {
        $http = new Http();

        $data = [
            'messages' => [],
        ];

        foreach ($this->messages as $message) {
            $data['messages'][] = $message->getArray();
            $message->clean();
        }

        $this->messages = [];

        if (isset($this->dryrun) && $this->dryrun) {
            $data['dryrun'] = true;
        }

        if (isset($this->sanitize) && $this->sanitize) {
            $data['sanitize'] = true;
        }

        if (isset($this->sender_not_force) && $this->sender_not_force) {
            $data['sender_not_force'] = true;
        }

        $response = $http->sendHttp('POST', 'sms/send', $this->headers, $data);

        if ($response->status == 200) {
            return json_decode($response->message);
        }

        throw new RequestFail($response->message, $response->status);
    }
}
