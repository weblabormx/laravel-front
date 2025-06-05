<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as Intervention;
use WeblaborMx\Front\Front;

function getThumb($full_name, $prefix, $force = false)
{
    $editGetThumbHelper = 'editGetThumb';
    $validateGetThumbHelper = 'validateGetThumb';

    if (function_exists($validateGetThumbHelper) && !$force) {
        $execute = $validateGetThumbHelper($full_name);
        if (!$execute) {
            if (function_exists($editGetThumbHelper)) {
                return $editGetThumbHelper($full_name);
            }
            return $full_name;
        }
    }
    $full_name = explode('/', $full_name);
    $key = count($full_name) - 1;

    $name = explode('.', $full_name[$key]);
    $name[0] = $name[0] . $prefix;
    $name = implode('.', $name);

    $full_name[$key] = $name;
    $full_name = implode('/', $full_name);

    if (function_exists($editGetThumbHelper)) {
        return $editGetThumbHelper($full_name);
    }

    return $full_name;
}

function getFront($model, $source = null)
{
    $model = config('front.resources_folder') . '\\' . $model;
    return new $model($source);
}

function getFrontByModel($object)
{
    $class = $object->getMorphClass();
    $class = str_replace('App\\Models\\', '', $class);
    return getFront($class)->setObject($object);
}

function getButtonByName($name, $front = null, $object = null)
{
    return (new Front)->buttons()->getByName($name, $front, $object);
}

function isResponse($response)
{
    if (!is_object($response)) {
        return false;
    }
    $class = get_class($response);
    $classes = [$class];
    while (true) {
        $class = get_parent_class($class);
        if (!$class) {
            break;
        }
        $classes[] = $class;
    }
    return collect($classes)->contains(function ($item) {
        return Str::contains($item, [
            'Symfony\Component\HttpFoundation\Response',
            'Illuminate\View\View'
        ]);
    });
}

function saveImagesWithThumbs($image, $directory, $file_name, $disk = null)
{
    if (is_null($disk)) {
        $disk = config('front.disk', 'public');
    }

    $thumbnails = config('front.thumbnails', []);
    Storage::putFileAs($directory, $image, $file_name);

    foreach ($thumbnails as $thumbnail) {
        $width = $thumbnail['width'];
        $height = $thumbnail['height'];
        $prefix = $thumbnail['prefix'];
        $is_fit = $thumbnail['fit'] ?? false;

        // Make smaller the image
        $new_file = Intervention::make($image);

        if ($is_fit) {
            $new_file = $new_file->fit($width, $height);
        } elseif ($new_file->height() > $height || $new_file->width() > $width) {
            $new_file = $new_file->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        // Save the image
        $new_name = getThumb($file_name, $prefix, true);
        $new_file_name = $directory . '/' . $new_name;
        Storage::put($new_file_name, (string) $new_file->encode(), 'public');
    }
    return $directory . '/' . $file_name;
}

function deleteImagesWithThumbs($file_name, $disk = null)
{
    if (is_null($disk)) {
        $disk = config('front.disk', 'public');
    }

    $thumbnails = config('front.thumbnails', []);
    foreach ($thumbnails as $thumbnail) {
        $prefix = $thumbnail['prefix'];
        $new_name = getThumb($file_name, $prefix, true);
        if(!Storage::exists($new_name)) {
            continue;
        }
        Storage::delete($new_name);
    }
    if (!Storage::exists($file_name)) {
        return;
    }
    Storage::delete($file_name);
}

function getImageUrl($path, $default = null, $disk = null)
{
    $disk = $disk ?? config('filesystems.default');
    if (!$path || !Storage::disk($disk)->exists($path)) {
        return $default;
    }

    $publicDisks = collect(config('filesystems.disks'))->where('visibility', 'public')->keys()->all();
    if (in_array($disk, $publicDisks)) {
        return Storage::disk($disk)->url($path);
    }

    $cacheKey = "temporary_url:{$disk}:{$path}";
    return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($disk, $path) {
        return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(5));
    });
}