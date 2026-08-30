<?php

use App\Http\Middleware\AnalyticsMiddleware;
use App\Mcp\Servers\CvServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/cv', CvServer::class)
    ->middleware([AnalyticsMiddleware::class, 'throttle:mcp']);

Mcp::web('/mcp/cv/auth', CvServer::class)
    ->middleware(['auth:sanctum', AnalyticsMiddleware::class, 'throttle:mcp']);
