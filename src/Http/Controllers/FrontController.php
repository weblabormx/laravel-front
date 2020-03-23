<?php

namespace WeblaborMx\Front\Http\Controllers;

use WeblaborMx\Front\Http\Repositories\FrontRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use WeblaborMx\Front\Traits\IsRunable;
use WeblaborMx\Front\Jobs\StoreFront;
use WeblaborMx\Front\Jobs\IndexFront;
use WeblaborMx\Front\Jobs\UpdateFront;
use WeblaborMx\Front\Jobs\DestroyFront;

class FrontController extends Controller
{
    use IsRunable;

    private $repository;
    private $front;

    public function __construct(FrontRepository $repository)
	{
        $this->repository = $repository;
        $this->front = $this->getFront();
    }

    public function index()
    {
        $this->authorize('viewAny', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('index');
        $base_url = $front->base_url;

        $response = $this->run(new IndexFront($front, $base_url));
        if($this->isResponse($response)) {
            return $response;
        }
        
        // Show view
        $objects = $response;
        return view('front::crud.index', compact('objects', 'front'));
    }

    public function create()
	{
        $this->authorize('create', $this->front->getModel());
        
        $front = $this->front->setSource('create');
        return view('front::crud.create', compact('front'));
    }

    public function store(Request $request)
	{
        $this->authorize('create', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('store');
        $response = $this->run(new StoreFront($request, $front));
        if($this->isResponse($response)) {
            return $response;
        }
        
        // Redirect to index page
        return redirect($front->base_url);
    }

    public function show($object)
    {
        // Get object
        $model = $this->front->getModel();
        $object = $model::find($object);
        if(!is_object($object)) {
            abort(404);
        }
        
        // Validate policy
        $this->authorize('view', $object);

        // Front code
        $front = $this->front->setSource('show')->setObject($object);
        $front->show($object);

        // Show view
        return view('front::crud.show', compact('object', 'front'));
    }

    public function edit($object)
    {
        // Get object
        $model = $this->front->getModel();
        $object = $model::find($object);
        if(!is_object($object)) {
            abort(404);
        }

        // Validate policy
        $this->authorize('update', $object);

        // Front code
        $front = $this->front->setSource('edit')->setObject($object);

        // Show view
        return view('front::crud.edit', compact('object', 'front'));
    }

    public function update($object, Request $request)
    {
        // Get object
        $model = $this->front->getModel();
        $object = $model::find($object);
        if(!is_object($object)) {
            abort(404);
        }
        
        // Validate policy
        $this->authorize('update', $object);

        // Front code
        $front = $this->front->setSource('update')->setObject($object);
        $response = $this->run(new UpdateFront($request, $front, $object));
        if($this->isResponse($response)) {
            return $response;
        }

        // Redirect
        return back();
    }

    public function destroy($object)
    {
        // Get object
        $model = $this->front->getModel();
        $object = $model::find($object);
        if(!is_object($object)) {
            abort(404);
        }

        // Validate Policy
        $this->authorize('delete', $object);

        // Front code
        $front = $this->front->setSource('show')->setObject($object);
        $response = $this->run(new DestroyFront($front, $object));
        if($this->isResponse($response)) {
            return $response;
        }

        // Redirect
        return redirect($this->front->base_url);
    }

    public function indexActionShow($action) 
    {
        $this->authorize('update', $this->front->getModel());
        
        $sport = $this->repository->findSport($sport);
        $sportable = $this->sportable;

        $class = $sport->getClass($this->sportable->db_class);
        $front = getFront($class, 'create')->addData(compact('sport'));
        $action = $this->repository->getIndexAction($action, $front);
        
        return view('front::crud.index-action', compact('action', 'front', 'sportable'));
    }

    public function indexActionStore($action, Request $request)
    {
        $this->authorize('update', $this->front->getModel());

        $sport = $this->repository->findSport($sport);
        $class = $sport->getClass($this->sportable->db_class);
        $front = getFront($class, 'create')->addData(compact('sport'));
        $action = $this->repository->getIndexAction($action, $front);
        $action->validate();

        $result = $action->handle($request);
        if(!isset($result)) {
            $message = config('front.messages.action_sucess');
            $message = str_replace('{title}', $action->title, $message);
            flash($message)->success();
        } else {
            $request->flash();
        }
        
        return back();
    }

    public function actionShow($object, $action) 
    {
        $original_object = $object;
        $original_action = $action;
        
        $model = $this->front->getModel();
        $object = $model::find($object);
        if(!is_object($object)) {
            abort(404);
        }

        $front = $this->front->setSource('create')->setObject($object);
        $action = $this->repository->getAction($action, $front);
        if(!is_object($action)) {
            abort(406, "Action wasn't found: {$original_action}");
        }
        if(!$action->show) {
            abort(404);
        }
        $action = $action->setObject($object);

        // Detect if dont have fields process inmediately
        if(count($action->fields())==0) {
            return $this->actionStore($original_object, $original_action, request());
        }

        return view('front::crud.action', compact('action', 'front', 'object'));
    }

    public function actionStore($object, $action, Request $request)
    {
        $model = $this->front->getModel();
        $object = $model::find($object);
        if(!is_object($object)) {
            abort(404);
        }

        $front = $this->front->setSource('create')->setObject($object);
        $action = $this->repository->getAction($action, $front);
        if(!is_object($action)) {
            abort(406, "Action wasn't found: {$original_action}");
        }
        if(!$action->show) {
            abort(404);
        }
        $action = $action->setObject($object);
        $action->validate();

        $result = $action->handle($object, $request);
        if(is_object($result) && get_class($result)=='Illuminate\Http\RedirectResponse') {
            $request->flash();
            return $result;
        }
        if(!isset($result)) {
            $message = config('front.messages.action_sucess');
            $message = str_replace('{title}', $action->title, $message);
            flash($message)->success();
        } else {
            $request->flash();
        }
        
        return back();
    }

    public function lenses($lense, Request $request)
    {
        $this->authorize('viewAny', $this->front->getModel());

        // Front code
        $front = $this->front->setSource('index');
        $objects = $this->repository->index($front)->getLense($lense);
        if(get_class($objects)!='Illuminate\Pagination\LengthAwarePaginator') {
            return $objects;
        }
        return view('front::crud.index', compact('objects', 'front'));
    }

    public function search(Request $request)
    {
        $title = $this->front->title;
        $result = $this->front->globalIndexQuery();

        // Get query if sent
        if($request->filled('filter_query')) {
            $query = json_decode($request->filter_query);
            $query = unserialize($query);
            $query = $query->getClosure();
            $result = $query($result);
        }
        
        $result  = $result->search($request->term)->limit(10)->get()->map(function($item) use ($title) {
            return [
                'label' => $item->$title, 
                'id' => $item->getKey(), 
                'value' => $item->$title 
            ];
        })->sortBy('label');
        print json_encode($result);
    }

    /*
     * Internal Functions
     */

    private function getFront()
    {
        $action = request()->route()->getAction();
        if(!is_array($action) || !isset($action['prefix'])) {
            return;
        }
        $action = explode('/', $action['prefix']);
        $action = $action[count($action)-1];
        $action = Str::camel(Str::singular($action));
        $action = ucfirst($action);
        $class = 'App\Front\\'.$action;
        return new $class;
    }

}
