<?php

namespace WeblaborMx\Front;

use Illuminate\Database\Eloquent\Model;
use WeblaborMx\Front\Pages\Page;

final class Front
{
    /** @var array<class-string<Resource>,class-string<Resource>> */
    private array $cachedResourceClasses = [];

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

        $basename = '\\' . \class_basename($resource);

        $found = \array_values(\array_filter(
            $this->cachedResourceClasses,
            fn($class) => \str_ends_with($class, $basename)
        ));

        if (empty($found)) {
            return config('front.resources_folder') . $basename;
        }

        if (\count($found) > 1) {
            throw new \InvalidArgumentException("Trying to resolve ambiguous resource '{$basename}'. Try using FQN classname instead");
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
