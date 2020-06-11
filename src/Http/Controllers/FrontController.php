<?php

namespace WeblaborMx\Front\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use WeblaborMx\Front\Traits\IsRunable;
use WeblaborMx\Front\Jobs\FrontStore;
use WeblaborMx\Front\Jobs\FrontIndex;
use WeblaborMx\Front\Jobs\FrontUpdate;
use WeblaborMx\Front\Jobs\FrontDestroy;
use WeblaborMx\Front\Jobs\FrontSearch;
use WeblaborMx\Front\Jobs\ActionShow;
use WeblaborMx\Front\Jobs\ActionStore;
use WeblaborMx\Front\Jobs\MassiveEditShow;
use WeblaborMx\Front\Jobs\MassiveEditStore;

class FrontController extends Controller
{
    use IsRunable;

    private $front;

    public function __construct($front)
	{
        $this->front = $front;
    }

    /*
     * CRUD Functions
     */

    public function index()
    {
        $this->authorize('viewAny', $this->front->getModel());
        $this->frontAuthorize('index');

        // Front code
        $front = $this->front->setSource('index');
        $base_url = $front->getBaseUrl();

        $response = $this->run(new FrontIndex($front, $base_url));
        if($this->isResponse($response)) {
            return $response;
        }
        
        // Show view
        $result = $response;
        return view('front::crud.index', $this->getFields(compact('result', 'front')));
    }

    public function create()
	{
        $this->authorize('create', $this->front->getModel());
        $this->frontAuthorize('create');
        
        $front = $this->front->setSource('create');
        return view('front::crud.create', $this->getFields(compact('front')));
    }

    public function store(Request $request)
	{
        $this->authorize('create', $this->front->getModel());
        $this->frontAuthorize('store');

        // Front code
        $front = $this->front->setSource('store');
        $response = $this->run(new FrontStore($request, $front));
        if($this->isResponse($response)) {
            return $response;
        }
        
        // Redirect to index page
        return redirect($front->getBaseUrl());
    }

    public function show($object)
    {
        // Get object
        $object = $this->getObject($object);
        
        // Validate policy
        $this->authorize('view', $object);
        $this->frontAuthorize('show');

        // Front code
        $front = $this->front->setSource('show')->setObject($object);
        $front->show($object);

        // Show view
        return view('front::crud.show', $this->getFields(compact('object', 'front')));
    }

    public function edit($object)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate policy
        $this->authorize('update', $object);
        $this->frontAuthorize('edit');

        // Front code
        $front = $this->front->setSource('edit')->setObject($object);

        // Show view
        return view('front::crud.edit', $this->getFields(compact('object', 'front')));
    }

    public function update($object, Request $request)
    {
        // Get object
        $object = $this->getObject($object);
        
        // Validate policy
        $this->authorize('update', $object);
        $this->frontAuthorize('update');

        // Front code
        $front = $this->front->setSource('update')->setObject($object);
        $response = $this->run(new FrontUpdate($request, $front, $object));
        if($this->isResponse($response)) {
            return $response;
        }

        // Redirect
        return back();
    }

    public function destroy($object)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate Policy
        $this->authorize('delete', $object);
        $this->frontAuthorize('destroy');

        // Front code
        $front = $this->front->setSource('show')->setObject($object);
        $response = $this->run(new FrontDestroy($front, $object));
        if($this->isResponse($response)) {
            return $response;
        }

        // Redirect
        return redirect($this->front->getBaseUrl());
    }

    /*
     * Actions
     */

    public function actionShow($object, $action) 
    {
        // Get object
        $object = $this->getObject($object);

        // Front code
        $front = $this->front->setSource('create')->setObject($object);
        $response = $this->run(new ActionShow($front, $object, $action, function() use ($object, $action) {
            return $this->actionStore($object->getKey(), $action, request());
        }));
        if($this->isResponse($response)) {
            return $response;
        }

        // Show view
        $action = $response;
        return view('front::crud.action', $this->getFields(compact('action', 'front', 'object')));
    }

    public function actionStore($object, $action, Request $request)
    {
        // Get object
        $object = $this->getObject($object);

        // Front code
        $front = $this->front->setSource('create')->setObject($object);
        $response = $this->run(new ActionStore($front, $object, $action, $request));
        if($this->isResponse($response)) {
            return $response;
        }

        // Redirect back
        return back();
    }

    public function indexActionShow($action) 
    {
        // Front code
        $front = $this->front->setSource('create');
        $response = $this->run(new ActionShow($front, null, $action, function() use ($action) {
            return $this->indexActionStore($action, request());
        }));
        if($this->isResponse($response)) {
            return $response;
        }

        // Show view
        $action = $response;
        return view('front::crud.action', $this->getFields(compact('action', 'front')));
    }

    public function indexActionStore($action, Request $request)
    {
        // Front code
        $front = $this->front->setSource('create');
        $response = $this->run(new ActionStore($front, null, $action, $request));
        if($this->isResponse($response)) {
            return $response;
        }

        // Return
        return back();
    }

    /*
     * Massive Edition
     */

    public function massiveEditShow($object, $key) 
    {
        // Get object
        $object = $this->getObject($object);

        // Validate Policy
        $this->authorize('update', $object);
        
        // Front code
        $front = $this->front->setSource('create')->setObject($object);
        $response = $this->run(new MassiveEditShow($front, $object, $key));
        if($this->isResponse($response)) {
            return $response;
        }

        // Show view
        $data = collect(compact('object', 'front'))->merge($response)->all();
        return view('front::crud.massive-edit', $this->getFields($data));
    }

    public function massiveEditStore($object, $key, Request $request)
    {
        // Get object
        $object = $this->getObject($object);

        // Validate Policy
        $this->authorize('update', $object);

        // Front code
        $front = $this->front->setSource('create')->setObject($object);
        $response = $this->run(new MassiveEditStore($front, $object, $key, $request));
        if($this->isResponse($response)) {
            return $response;
        }

        // Return
        return back();
    }

    /*
     * More features
     */

    public function lenses($lense, Request $request)
    {
        $this->authorize('viewAny', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('index')->getLense($lense);
        $base_url = $front->getBaseUrl();

        $response = $this->run(new FrontIndex($front, $base_url));
        if($this->isResponse($response)) {
            return $response;
        }
        
        // Show view
        $result = $response;
        return view('front::crud.index', $this->getFields(compact('result', 'front')));
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('index');
        $response = $this->run(new FrontSearch($front, $request));
        if($this->isResponse($response)) {
            return $response;
        }
    }

    public function frontAuthorize($method)
    {
        if(!in_array($method, $this->front->actions)) {
            abort(403, 'This action is unauthorized.');
        }
    }
}
