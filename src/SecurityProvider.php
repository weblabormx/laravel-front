<?php

namespace WeblaborMx\Front;

use Opis\Closure\ISecurityProvider;

class SecurityProvider implements ISecurityProvider
{
    /** @var  string */
    protected $secret;

    /**
     * SecurityProvider constructor.
     * @param string $secret
     */
    public function __construct($secret = null)
    {
        $this->secret = $secret;
    }

    /**
     * @inheritdoc
     */
    public function sign($closure)
    {
        return array(
            'closure' => $closure,
            'hash' => base64_encode(hash_hmac('sha256', $closure, $this->secret, true)),
        );
    }

    /**
     * @inheritdoc
     */
    public function verify(array $data)
    {
        return true;
    }
}
