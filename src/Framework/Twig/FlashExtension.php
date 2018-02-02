<?php
namespace Framework\Twig;

use Framework\Session\FlashHandler;

/**
 * Implements extension about flash messages
 *
 * Class FlashExtension
 * @package Framework\Twig
 */
class FlashExtension extends \Twig_Extension
{
    /**
     * @var FlashHandler
     */
    private $flash;

    /**
     * FlashExtension constructor.
     * @param FlashHandler $flash
     */
    public function __construct(FlashHandler $flash)
    {
        $this->flash = $flash;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('flash', [$this, 'getFlash'])
        ];
    }

    /**
     * Flash message input
     * @param string $type
     * @return string|null
     */
    public function getFlash(string $type): ?string
    {
        return $this->flash->get($type);
    }
}
