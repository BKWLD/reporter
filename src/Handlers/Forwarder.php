<?php namespace Bkwld\Reporter\Handlers;

// Dependencies
use Bkwld\Reporter\Reporter;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

/**
 * Forward logs between loggers.  Based on
 * https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/PsrHandler.php
 */
class Forwarder extends AbstractHandler {

    /**
     * @var Reporter
     */
    protected $reporter;

    /**
     * @param Logger  $logger
     * @param int     $level  The minimum logging level at which this handler
     *                        will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble
     *                        up the stack or not
     */
    public function __construct(Reporter $reporter, $level = Logger::DEBUG, $bubble = true) {
        parent::__construct($level, $bubble);
        $this->reporter = $reporter;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(array $record) {
        if (!$this->isHandling($record)) return false;
        $this->reporter->buffer($record['level_name'], $record['message'], $record['context']);
        $this->reporter->write();
        return false === $this->bubble;
    }
}
