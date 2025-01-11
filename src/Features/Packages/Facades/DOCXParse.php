<?php

namespace Shakewellagency\ContentPortalDocsParser\Features\Packages\Facades;

use Shakewellagency\ContentPortalDocsParser\Features\Packages\Jobs\PackageInitializationJob;
use Shakewellagency\ContentPortalDocsParser\Features\Packages\Jobs\PageParserJob;
use Shakewellagency\ContentPortalPdfParser\Events\ParsingTriggerEvent;

class DOCXParse
{
    public static function execute($package, $version)
    {
        event(new ParsingTriggerEvent($package, $version));

        PackageInitializationJob::withChain([
            new PageParserJob($package, $version),
        ])->dispatch($package, $version);

    }
}
