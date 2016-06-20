<?php
/**
 * Created by PhpStorm.
 * User: ilelis
 * Date: 20/06/16
 * Time: 14:53
 */

namespace HelloFresh\Engine\CommandBus\Exception;

/**
 * No handler could be found for the given command.
 */
class MissingHandlerException extends \OutOfBoundsException
{
    /**
     * @var string
     */
    private $commandName;

    /**
     * @param string $commandName
     *
     * @return static
     */
    public static function forCommand($commandName)
    {
        $exception = new static('Missing handler for command ' . $commandName);
        $exception->commandName = $commandName;

        return $exception;
    }

    /**
     * @return string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }
}
