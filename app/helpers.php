<?php

if (! function_exists('isActiveRoute')) {
    function isActiveRoute(array $patterns): bool
    {
        if ($patterns === []) {
            return false;
        }

        $request = request();
        if (! $request) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
