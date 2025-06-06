<?php

namespace WeblaborMx\Front;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use WeblaborMx\Front\ButtonManager;
use WeblaborMx\Front\Pages\Page;
use WeblaborMx\Front\ThumbManager;

final class Front
{
    /** @var array<class-string<Resource>,class-string<Resource>> */
    private array $cachedResourceClasses = [];

    /** @var array<class-string<Resource|Page>,Route> */
    private array $cachedRoutes = [];

    /* --------------------
     * Helpers
     ---------------------- */

    public function baseNamespace(): string
    {
        return trim(config('front.resources_folder'), '\\') . '\\';
    }

    public function thumbs(): ThumbManager
    {
        return app(ThumbManager::class);
    }

    public function buttons(): ButtonManager
    {
        return app(ButtonManager::class);
    }

    /* --------------------
     * Route registrar
     ---------------------- */

    /** @var array<class-string<Resource>,class-string<Resource>> */
    public function getRegisteredResources(): array
    {
        return $this->cachedResourceClasses;
    }

    public function makeResource(string $resource, ?string $source = null): Resource
    {
        $class = $this->resolveResource($resource);

        return new $class($source);
    }

    public function registerRoute(string|Resource|Page $frontItem, Route $route, string $action): Route
    {
        $key = is_object($frontItem) ? $frontItem::class : $frontItem;

        if (!isset($this->cachedRoutes[$key])) {
            $this->cachedRoutes[$key] = [];
        }

        $this->cachedRoutes[$key][$action] = $route;

        return $route;
    }

    public function routeOf(string|Resource|Page $frontItem, ?string $action = null): ?Route
    {
        if (is_object($frontItem)) {
            $key = $frontItem::class;
            $action = $action ?? $frontItem->source;
        } else {
            $key = $frontItem;
            $action = $action ?? 'index';
        }

        if (!isset($this->cachedRoutes[$key])) {
            return null;
        }

        return $this->cachedRoutes[$key][$action] ?? null;
    }

    /** @return array<class-string<\WeblaborMx\Front\Resource|\WeblaborMx\Front\Pages\Page>, \Illuminate\Routing\Route> */
    public function getRegisteredRoutes(): array
    {
        return $this->cachedRoutes;
    }

    /** @return class-string<Resource> */
    public function registerResource(string $resource): string
    {
        $resource = $this->resolveResource($resource);

        $this->cachedResourceClasses[$resource] = $resource;

        return $resource;
    }

    /** @return class-string<Resource> */
    public function resolveResource(string $resource): string
    {
        if (isset($this->cachedResourceClasses[$resource])) {
            return $resource;
        }

        if (\class_exists($resource)) {
            if (\is_subclass_of($resource, Resource::class)) {
                return $resource;
            } elseif (!\is_subclass_of($resource, Model::class)) {
                throw new \InvalidArgumentException("Class '{$resource}' cannot be resolved to a Front Resource");
            }
        }

        $namespace = $this->baseNamespace();
        $basename = str($resource)
            ->after($namespace)
            ->toString();

        $found = \array_values(\array_filter(
            $this->cachedResourceClasses,
            fn($class) => \str_ends_with($class, $basename)
        ));

        if (empty($found)) {
            return $namespace . $basename;
        }

        return $found[0];
    }

    /** @return class-string<Page> */
    public function resolvePage(string $page): string
    {
        if (\class_exists($page)) {
            if (\is_subclass_of($page, Page::class)) {
                return $page;
            }

            throw new \InvalidArgumentException("Class '{$page}' cannot be resolved to a Front Page");
        }

        return 'App\\Front\\Pages\\' . $page;
    }
}
