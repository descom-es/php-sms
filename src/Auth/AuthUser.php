<?php

namespace Descom\Sms\Auth;

class AuthUser
{
    /**
         * Define the username for auth.
         *
         * @var string $auth
         */
    private $username;

    /**
         * Define the password for auth.
         *
         * @var string $auth
         */
    private $password;

    /**
         * Create a new authuser instance.
         *
         * @param  string $username
         * @param  string $password
         * @return void
         */
    public function __construct(string $username, string $password)
    {
            $this->username = $username;
            $this->password = $password;
    }

    /**
         * Get headers for Auth
         *
         * @return array
         */
    public function headers()
    {
        return [
            'DSMS-User' => $this->username,
            'DSMS-Pass' => $this->password
        ];
    }

}
