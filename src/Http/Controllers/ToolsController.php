<?php

namespace WeblaborMx\Front\Http\Controllers;

use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ToolsController extends Controller
{
    public function uploadImage(Request $request)
    {
        $variables = json_decode($request->variables);
        $file = $request->file;

        $manager = ImageManager::imagick();
        $new_file = $manager->read($file);
        if ($new_file->height() > $variables->height || $new_file->width() > $variables->width) {
            $new_file->scaleDown(width: $variables->width, height: $variables->height);
        }

        $new_name = $this->getFileName($file);
        $file_name = $variables->directory.'/'.$new_name;
        Storage::put($file_name, (string) $new_file->encode(), 'public');
        $url = Storage::url($file_name);

        $response = new \StdClass();
        $response->link = $url;
        return response(stripslashes(json_encode($response)));
    }

    private function getFileName($file)
    {
        $file_name = Str::random(9);
        if (is_string($file)) {
            $extension = explode('.', $file);
            $extension = $extension[count($extension) - 1];
        } else {
            $extension = $file->guessExtension();
        }
        $file_name .= '.'.$extension;
        return $file_name;
    }

}
