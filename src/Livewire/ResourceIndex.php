<?php

namespace WeblaborMx\Front\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Component;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Jobs\FrontIndex;
use WeblaborMx\Front\Traits\IsRunable;

class ResourceIndex extends Component
{
    use AuthorizesRequests;
    use IsRunable;

    #[Locked]
    public $resource;

    public function mount(string $resource): void
    {
        $this->resource = $resource;
    }

    public function front()
    {
        return Front::makeResource($this->resource)->setSource('index');
    }

    public function result()
    {
        return $this->indexResponse();
    }

    private function indexResponse()
    {
        $front = Front::makeResource($this->resource)->setSource('index');

        $this->authorize('viewAny', $front->getModel());
        $this->frontAuthorize($front, 'index');

        $response = $front->beforeRequest();

        if ($response) {
            return $response;
        }

        return $this->run(new FrontIndex($front, $front->getBaseUrl()));
    }

    private function frontAuthorize($front, string $method): void
    {
        if (!in_array($method, $front->actions)) {
            abort(403, 'This action is unauthorized.');
        }
    }

    public function render()
    {
        return view('front::livewire.resource-index');
    }
}
