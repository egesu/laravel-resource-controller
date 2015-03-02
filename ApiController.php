<?php
/**
 * ApiController.php
 *
 * @author Ege Sertçetin
 * @version $Id$
 * @since Tue 12 Aug 2014 09:03:54 PM
 */

namespace Api;

class ApiController extends \BaseController
{
    protected $baseClass = '';

    protected $response = '';

    protected $orderBy = 'order_num';

    protected $orderByDirection = 'ASC';

    protected $paginate = false;

    protected $query;

    protected $doNotGet = false;

    protected $input = [];

    protected $filters = [];

    protected $statusCode = 200;

    protected $adminUser = null;

    protected $app = null;


    public function __construct()
    {
        $this->input = \Input::all();
        $this->filters = \Input::get('filters');
        if(is_string($this->filters)) $this->filters = json_decode($this->filters);

        $this->app = \App::make('dugunApp');
        if($this->app->panel === 'crm') {
            $this->adminUser = $this->app->adminUser;
        }

        $baseClass = $this->baseClass;
        $baseClass::boot();

        $this->afterFilter('@printResponse');
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $baseClass = $this->baseClass;

        if($this->orderBy) {
            $this->query = $baseClass::orderByRaw($this->orderBy . ' ' . $this->orderByDirection);
        } else {
            $this->query = $baseClass::whereRaw('1 = 1');
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $baseClass = $this->baseClass;
        $this->paginate = false;
        $this->doNotGet = true;

        if(gettype($id) === 'object') {
            $this->query = $id;
        } else {
            $this->query = $baseClass::find($id);
        }

        if(method_exists($this->query, 'getRelations')) {
            $this->query->getRelations();
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $this->doNotGet = true;
        $baseClass = $this->baseClass;

        $resource = $baseClass::create($this->input);

        if(method_exists($resource, 'getRelations')) {
            $resource->getRelations();
        }

        $this->query = $resource;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $this->doNotGet = true;
        $baseClass = $this->baseClass;

        if(gettype($id) === 'object') {
            $this->query = $id;
        } else {
            $this->query = $baseClass::find($id);
        }

        $this->query->fill($this->input)->push();
        $this->query = $baseClass::find($this->query->id);

        if(method_exists($this->query, 'getRelations')) {
            $this->query->getRelations();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->doNotGet = true;
        $baseClass = $this->baseClass;

        if(gettype($id) === 'object') {
            $this->query = $id->delete();
        } else {
            $this->query = $baseClass::find($id)->delete();
        }

        $this->query = '';
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->doNotGet = true;
        $oldIndex = (int)\Input::get('old_index');
        $newIndex = (int)\Input::get('new_index');

        if(gettype($id) === 'object') {
            $this->query = $id;
        } else {
            $this->query = $baseClass::find($id);
        }

        if(method_exists($this->query, 'sort')) {
            if(!$this->query->sort($newIndex, $oldIndex)) {
                \App::abort(500);
            }
        } else {
            \App::abort(404);
        }
    }


    public function printResponse($route, $request, $response)
    {
        if(!in_array(explode('@', $route->getActionName())[1], [
            'index',
            'show',
            'update',
            'store',
            'edit',
            'destroy',
        ])) {
            return $response;
        }

        if($this->statusCode) {
            $response->setStatusCode($this->statusCode);
        }

        if($this->response) {
            return $response->setContent($this->response);
        }

        if(!$this->doNotGet) {
            if($this->paginate) {
                $this->response = $this->query->paginate($this->paginate);
            } else {
                $this->response = $this->query->get();
            }
        } else {
            $this->response = $this->query;
        }

        if($this->response) {
            return $response->setContent($this->response->toArray());
        } else {
            return $response->setContent('');
        }
    }
}
