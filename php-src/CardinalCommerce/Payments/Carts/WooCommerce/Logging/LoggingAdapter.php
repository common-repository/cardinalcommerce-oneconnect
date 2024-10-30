<?php
namespace CardinalCommerce\Payments\Carts\WooCommerce\Logging;

/**
 * NEXTREV: Remove this class for WooCommerce 4.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/// SEE: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md

/**
 * Implementation of Psr standard logging for WooCommerce
 *
 * NEXTREV: The next version of WooCommerce removes the need for this requirement, as it's logger implements the interface.
 */
class LoggingAdapter implements \Psr\Log\LoggerInterface {
    private $_handle = null;
    private $_wc_logger = null;

    public function __construct($wc_logger, $handle = 'CardinalProcessorModule') {
        $this->_handle = $handle;
        $this->_wc_logger = $wc_logger;
    }

    private static function interpolate($message, array $context = array()) {
        $replace = array();
        foreach($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert($message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }
    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical($message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array()) {
        $this->_wc_logger->add($this->_handle, self::interpolate($message, $context));
    }
}