<?php

use Illuminate\Support\{HtmlString, Str};
use Illuminate\Support\Facades\{Cache, Storage};
use Intervention\Image\ImageManager;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\DomCrawler\Crawler;
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

function saveImagesWithThumbs($image, $directory, $file_name, $max_width = null, $max_height = null)
{
    $manager = ImageManager::imagick();
    $thumbnails = config('front.thumbnails', []);

    // Change the extension of the image if is heic,heif
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $convertToJpg = false;
    if (in_array($extension, ['heic', 'heif', 'avif'])) {
        $file_name = pathinfo($file_name, PATHINFO_FILENAME) . '.jpg';
        $convertToJpg = true;
    }

    // Save the image if not max width and max height, otherwise add to thumbnails
    if(is_null($max_width) && is_null($max_height)) {
        Storage::putFileAs($directory, $image, $file_name);
    } else {
        $thumbnails[] = [
            'width' => $max_width,
            'height' => $max_height,
            'prefix' => '',
            'fit' => false,
        ];
    }

    // Convert image on valid binary
    if (is_string($image) && file_exists($image)) {
        $image = file_get_contents($image);
    } elseif (is_string($image) && str_starts_with($image, 'http')) {
        // Remote URL
        $image = file_get_contents($image);
    } elseif (is_resource($image)) {
        $image = stream_get_contents($image);
    } elseif ($image instanceof \Livewire\TemporaryUploadedFile) {
        $image = file_get_contents($image->getRealPath());
    } else {
        $image = (string) $image;
    }

    // Execute the thumbnails
    foreach ($thumbnails as $thumbnail) {
        $width = $thumbnail['width'];
        $height = $thumbnail['height'];
        $prefix = $thumbnail['prefix'];
        $is_fit = $thumbnail['fit'] ?? false;

        // Make smaller the image
        $new_file = $manager->read($image);

        if ($is_fit) {
            $new_file->cover($width, $height);
        } elseif ($new_file->height() > $height || $new_file->width() > $width) {
            $new_file->scaleDown(width: $width, height: $height);
        }

        // Save the image
        $new_name = getThumb($file_name, $prefix, true);
        $new_file_name = $directory . '/' . $new_name;
        if ($convertToJpg) {
            Storage::put($new_file_name, (string) $new_file->toJpeg(90), 'public');
        } else {
            Storage::put($new_file_name, (string) $new_file->encode(), 'public');
        }
    }
    return $directory . '/' . $file_name;
}

function deleteImagesWithThumbs($file_name)
{
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

function markdownToHtml(?string $markdown, array $classes = []): HtmlString
{
    if (is_null($markdown)) {
        return new HtmlString('');
    }

    $html = app(CommonMarkConverter::class)->convert($markdown);
    // Convert \n inside <p> to <br>
    $pattern = '/<p>(.*?)<\/p>/s';

    // Función de reemplazo que convierte \n a <br> dentro de cada párrafo
    $replacement = function ($matches) {
        $content = $matches[1];
        $content = str_replace("\n", '<br>', $content);

        // Convertir \n a <br> pero preservar los que ya están como &nbsp; o HTML
        // $content = preg_replace('/(?<!&)(?<!\&)\n(?!\;)/', '<br>', $content);
        return '<p>' . $content . '</p>';
    };

    // Aplicar la conversión solo dentro de los párrafos
    $html = preg_replace_callback($pattern, $replacement, $html);

    // Continue of function
    $crawler = new Crawler($html);
    $defaultClasses = [
        'p' => 'mb-4 text-base leading-relaxed text-gray-800',
        'h1' => 'text-4xl font-bold mb-6',
        'h2' => 'text-3xl font-semibold mb-5',
        'h3' => 'text-2xl font-semibold mb-4',
        'h4' => 'text-xl font-semibold mb-3',
        'h5' => 'text-lg font-semibold mb-2',
        'h6' => 'text-base font-semibold mb-1',
        'ul' => 'list-disc pl-6 mb-4',
        'ol' => 'list-decimal pl-6 mb-4',
        'li' => 'mb-1',
        'a' => 'text-blue-600 underline hover:text-blue-800',
        'strong' => 'font-bold',
        'em' => 'italic',
        'blockquote' => 'border-l-4 border-gray-300 pl-4 italic text-gray-600 mb-4',
        'code' => 'bg-gray-100 px-1 rounded text-sm font-mono',
        'pre' => 'bg-gray-900 text-white text-sm p-4 rounded overflow-auto mb-4',
        'hr' => 'my-8 border-t border-gray-300',
        'img' => 'my-4 rounded',
        'table' => 'table-auto w-full border-collapse my-6',
        'thead' => 'bg-gray-100',
        'tbody' => '',
        'tr' => 'border-b',
        'th' => 'px-4 py-2 text-left font-semibold',
        'td' => 'px-4 py-2',
    ];
    $classes = collect($defaultClasses)->merge($classes);
    $all = $classes['all'] ?? '';
    unset($classes['all']);
    $classes = $classes->map(function ($class) use ($all) {
        return trim("{$all} {$class}");
    })->all();

    // * This probably could be extracted to a useful function to modify HTML
    $extractMatchingHtml = function (Crawler $crawler, array $tagsToCheck) use (&$extractMatchingHtml) {
        $html = '';

        $children = count($crawler);

        foreach ($crawler as $domElement) {
            $tagName = $domElement->nodeName;

            $childCrawler = new Crawler($domElement);
            $children = $childCrawler->children();

            if ($children->count()) {
                $childrenHtml = $childCrawler->html();
                for ($i = 0; $i < $children->count(); $i++) {
                    $child = $children->eq($i);
                    $beforeHtml = Str::before($childrenHtml, $child->outerHtml());
                    $afterHtml = Str::after($childrenHtml, $child->outerHtml());
                    $contentHtml =  $extractMatchingHtml($child, $tagsToCheck);
                    $childrenHtml = "{$beforeHtml}{$contentHtml}{$afterHtml}";
                }
            } else {
                $childrenHtml = $childCrawler->html();
            }

            $nodeAttrs = collect($domElement->attributes)->mapWithKeys(fn($v) => [$v->nodeName => $v->nodeValue]);

            if (isset($tagsToCheck[$tagName])) {
                $nodeClasses = explode(' ', $nodeAttrs->get('class', ''));
                $mergeClasses  = explode(' ', $tagsToCheck[$tagName]);

                $allClasses = collect([
                    ...$nodeClasses,
                    ...$mergeClasses
                ])->map(fn($v) => trim($v))
                    ->unique()
                    ->join(' ');

                $nodeAttrs['class'] = htmlspecialchars($allClasses);
            }

            $attrs = $nodeAttrs
                ->map(fn($v, $k) => $k . '="' . htmlspecialchars($v) . '"')
                ->join(' ');

            $html .= "<{$tagName} {$attrs}>{$childrenHtml}</{$tagName}>";
        }
        return $html;
    };
    $html = $extractMatchingHtml($crawler->filter('body')->children(), $classes);
    $html = str_replace('<br ></br>', '<br />', $html);
    return new HtmlString($html);
}

function cleanMarkdown($markdown)
{
    // Eliminar encabezados
    $markdown = preg_replace('/#+\s*(.*?)\s*#*$/m', '$1', $markdown);

    // Eliminar negritas y cursivas
    $markdown = preg_replace('/(\*\*|__)(.*?)\1/', '$2', $markdown);
    $markdown = preg_replace('/(\*|_)(.*?)\1/', '$2', $markdown);

    // Eliminar enlaces
    $markdown = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $markdown);

    // Eliminar imágenes
    $markdown = preg_replace('/!\[([^\]]+)\]\([^)]+\)/', '', $markdown);

    // Eliminar código en línea y bloques
    $markdown = preg_replace('/`([^`]+)`/', '$1', $markdown);
    $markdown = preg_replace('/```.*?\n(.*?)```/s', '$1', $markdown);

    // Eliminar listas
    $markdown = preg_replace('/^[\*\-+]\s+/m', '', $markdown);
    $markdown = preg_replace('/^\d+\.\s+/m', '', $markdown);

    // Eliminar bloques de citas
    $markdown = preg_replace('/^>\s+/m', '', $markdown);

    // Eliminar líneas horizontales
    $markdown = preg_replace('/^[-*_]{3,}\s*$/m', '', $markdown);

    // Limpiar espacios múltiples y saltos de línea
    $markdown = preg_replace('/\s+/', ' ', $markdown);
    $markdown = trim($markdown);

    return $markdown;
}
