<?php

namespace WeblaborMx\Front\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<class-string<\WeblaborMx\Front\Resource>,class-string<\WeblaborMx\Front\Resource>> getRegisteredResources()
 * @method static Resource makeResource(string $resource, ?string $source = null)
 * @method static class-string<\WeblaborMx\Front\Resource> registerResource(string $resource)
 * @method static class-string<\WeblaborMx\Front\Resource> resolveResource(string $resource)
 * @method static class-string<\WeblaborMx\Front\Pages\Page> resolvePage(string $page)
 *
 * @see \WeblaborMx\Front\Front
 */
class Front extends Facade
{
    public static function getFacadeAccessor()
    {
        return \WeblaborMx\Front\Front::class;
    }
}
