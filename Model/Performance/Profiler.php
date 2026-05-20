<?php

declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Model\Performance;

/**
 * Thin profiler-tagging helper for the AccountLinksManager hot path
 * (the navigation-block plugin runs on every storefront page that
 * renders the My Account sidebar). Tags spans `ETechFlow_ALM_*` for
 * filtering in Tideways. No-op when Tideways isn't installed.
 */
final class Profiler
{
    private static ?bool $tidewaysAvailable = null;

    /**
     * @param string $name
     * @return object|null
     */
    public static function start(string $name): ?object
    {
        if (self::$tidewaysAvailable === null) {
            self::$tidewaysAvailable = class_exists('\\Tideways\\Profiler', false);
        }
        if (!self::$tidewaysAvailable) {
            return null;
        }
        try {
            return \Tideways\Profiler::createSpan($name);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param object|null $span
     */
    public static function stop(?object $span): void
    {
        if ($span === null) {
            return;
        }
        try {
            if (method_exists($span, 'stopTimer')) {
                $span->stopTimer();
            }
        } catch (\Throwable $e) {
            // Never let instrumentation surface to the customer.
        }
    }
}
