<?php

namespace WeblaborMx\Front\Http\Controllers;

use Illuminate\Http\Request;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Traits\IsRunable;
use WeblaborMx\Front\Jobs\FrontStore;
use WeblaborMx\Front\Jobs\FrontShow;
use WeblaborMx\Front\Jobs\FrontIndex;
use WeblaborMx\Front\Jobs\FrontUpdate;
use WeblaborMx\Front\Jobs\FrontDestroy;
use WeblaborMx\Front\Jobs\FrontSearch;
use WeblaborMx\Front\Jobs\ActionShow;
use WeblaborMx\Front\Jobs\ActionStore;
use WeblaborMx\Front\Jobs\MassiveIndexEditShow;
use WeblaborMx\Front\Jobs\MassiveIndexEditStore;
use WeblaborMx\Front\Jobs\MassiveEditShow;
use WeblaborMx\Front\Jobs\MassiveEditStore;

class FrontController extends Controller
{
    use IsRunable;

    private $front;
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            $this->front = Front::makeResource($this->model);
            return call_user_func_array(array($this, $method), $arguments);
        }
    }

    /*
     * CRUD Functions
     */

    private function index()
    {
        $this->authorize('viewAny', $this->front->getModel());
        $this->frontAuthorize('index');

        // Front code
        $front = $this->front->setSource('index');
        $base_url = $front->getBaseUrl();
        $response = $this->processRequest(new FrontIndex($front, $base_url));
        if (isResponse($response)) {
            return $response;
        }
        // Show view
        $result = $response;
        return view('front::crud.index', $this->getParameters(compact('result', 'front')));
    }

    private function create()
    {
        $this->authorize('create', $this->front->getModel());
        $this->frontAuthorize('create');

        $front = $this->front->setSource('create');
        $response = $this->processRequest();
        if (isResponse($response)) {
            return $response;
        }

        return view('front::crud.create', $this->getParameters(compact('front')));
    }

    private function store(Request $request)
    {
        $this->authorize('create', $this->front->getModel());
        $this->frontAuthorize('store');

        // Front code
        $front = $this->front->setSource('store');
        $response = $this->processRequest(new FrontStore($request, $front));
        if (isResponse($response)) {
            return $response;
        }

        // Redirect to index page
        return redirect($front->createRedirectionUrl($response));
    }

    private function show($object)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate policy
        $this->authorize('view', $object);
        $this->frontAuthorize('show');

        // Front code
        $front = $this->front->setSource('show')->setObject($object);
        $response = $this->processRequest(new FrontShow($object, $front));
        if (isResponse($response)) {
            return $response;
        }

        // Show view
        $object = $response;
        return view('front::crud.show', $this->getParameters(compact('object', 'front')));
    }

    private function edit($object)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate policy
        $this->authorize('update', $object);
        $this->frontAuthorize('edit');

        // Front code
        $front = $this->front->setSource('edit')->setObject($object);
        $response = $this->processRequest();
        if (isResponse($response)) {
            return $response;
        }

        // Show view
        return view('front::crud.edit', $this->getParameters(compact('object', 'front')));
    }

    private function update($object, Request $request)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate policy
        $this->authorize('update', $object);
        $this->frontAuthorize('update');

        // Front code
        $front = $this->front->setSource('update')->setObject($object);
        $response = $this->processRequest(new FrontUpdate($request, $front, $object));
        if (isResponse($response)) {
            return $response;
        }
        // Redirect
        return back();
    }

    private function destroy($object)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate Policy
        $this->authorize('delete', $object);
        $this->frontAuthorize('destroy');

        // Front code
        $front = $this->front->setSource('show')->setObject($object);
        $response = $this->processRequest();
        if (isResponse($response)) {
            return $response;
        }

        return $this->run(new FrontDestroy($front, $object));
    }

    /*
     * Actions
     */

    private function actionShow($object, $action)
    {
        // Get object
        $object = $this->getObject($object);

        // Front code
        $front = $this->front->setSource('create')->setObject($object);
        $response = $this->run(new ActionShow($front, $object, $action, function () use ($object, $action) {
            return $this->actionStore($object->getKey(), $action, request());
        }));
        if (isResponse($response)) {
            return $response;
        }

        // Show view
        $action = $response;
        return view('front::crud.action', $this->getParameters(compact('action', 'front', 'object')));
    }

    private function actionStore($object, $action, Request $request)
    {
        // Get object
        $object = $this->getObject($object);

        // Front code
        $front = $this->front->setSource('create')->setObject($object);
        $response = $this->run(new ActionStore($front, $object, $action, $request));
        if (isResponse($response)) {
            return $response;
        }

        // Redirect back
        return back();
    }

    private function indexActionShow($action)
    {
        // Front code
        $front = $this->front->setSource('create');
        $response = $this->run(new ActionShow($front, null, $action, function () use ($action) {
            return $this->indexActionStore($action, request());
        }));
        if (isResponse($response)) {
            return $response;
        }

        // Show view
        $action = $response;
        return view('front::crud.action', $this->getParameters(compact('action', 'front')));
    }

    private function indexActionStore($action, Request $request)
    {
        // Front code
        $front = $this->front->setSource('create');
        $response = $this->run(new ActionStore($front, null, $action, $request));
        if (isResponse($response)) {
            return $response;
        }

        // Return
        return back();
    }

    /*
     * Massive Edition
     */

    private function massiveIndexEditShow()
    {
        $this->authorize('viewAny', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('create');
        $response = $this->run(new MassiveIndexEditShow($front));
        if (isResponse($response)) {
            return $response;
        }

        // Show view
        $data = collect(compact('front'))->merge($response)->all();
        return view('front::crud.massive-index-edit', $this->getParameters($data));
    }

    private function massiveIndexEditStore(Request $request)
    {
        $this->authorize('viewAny', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('create');
        $response = $this->run(new MassiveIndexEditStore($front, $request));
        if (isResponse($response)) {
            return $response;
        }

        // Return
        return back();
    }

    private function massiveEditShow($object, $key)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate Policy
        $this->authorize('update', $object);

        // Front code
        $front = $this->front->setSource('create')->setObject($object);
        $response = $this->run(new MassiveEditShow($front, $object, $key));
        if (isResponse($response)) {
            return $response;
        }

        // Show view
        $data = collect(compact('object', 'front'))->merge($response)->all();
        return view('front::crud.massive-edit', $this->getParameters($data));
    }

    private function massiveEditStore($object, $key, Request $request)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate Policy
        $this->authorize('update', $object);

        // Front code
        $front = $this->front->setSource('create')->setObject($object);
        $response = $this->run(new MassiveEditStore($front, $object, $key, $request));
        if (isResponse($response)) {
            return $response;
        }

        // Return
        return back();
    }

    /*
     * Sortable
     */

    private function sortableUp($object)
    {
        $object = $this->getObject($object);
        $object->moveOrderUp();
        return back();
    }

    private function sortableDown($object)
    {
        $object = $this->getObject($object);
        $object->moveOrderDown();
        return back();
    }

    private function sortable($object, $order, $start_object = null)
    {
        $start = is_null($start_object) ? 0 : $this->getObject($start_object)->order_column;
        $object = $this->getObject($object);
        $object::setNewOrder($order, $start);
        return back();
    }

    /*
     * More features
     */

    private function lenses($lense, Request $request)
    {
        $this->authorize('viewAny', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('index')->getLense($lense);
        $base_url = $front->getBaseUrl();

        $response = $this->run(new FrontIndex($front, $base_url));
        if (isResponse($response)) {
            return $response;
        }

        // Show view
        $result = $response;
        return view('front::crud.index', $this->getParameters(compact('result', 'front')));
    }

    private function search(Request $request)
    {
        $this->authorize('viewAny', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('index');
        $response = $this->run(new FrontSearch($front, $request));
        if (isResponse($response)) {
            return $response;
        }
    }

    private function processRequest(...$actions)
    {
        $pipe = [
            fn() => $this->front->beforeRequest(),
            ...array_map(fn($action) => fn() => $this->run($action), $actions)
        ];

        foreach ($pipe as $callable) {
            $response = $callable();
            if ($response) {
                return $response;
            }
        }
    }

    private function frontAuthorize($method)
    {
        if (!in_array($method, $this->front->actions)) {
            abort(403, 'This action is unauthorized.');
        }
    }
}
