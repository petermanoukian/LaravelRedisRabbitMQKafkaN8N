<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatRequest;
use App\Http\Requests\FileUploadRequest;
use App\Services\CatService;
use App\Services\ProdService;
use Illuminate\Http\Request;
use App\Events\Cat\CatAdded;
use App\Events\Cat\CatUpdated;
use App\Events\Cat\CatDeleted;
use App\Events\Prod\ProdDeleted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;

class CatController extends Controller
{
    protected CatService $cats;
    protected ProdService $prods;

    public function __construct(CatService $cats, ProdService $prods)
    {
        $this->cats = $cats;
        $this->prods = $prods;
    }


    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $cats = $this->cats->paginate( $perPage, ['id', 'name', 'filer', 'filename', 'mime', 'sizer', 'extension'], 
        'id', 'desc', false, true );
        return view('admin.cats.index', compact('cats'));
    }

    public function indexJson(Request $request)
    {
        $fields = ['id', 'name', 'filer', 'filename', 'mime', 'sizer', 'extension'];
        $cats = $this->cats->select($fields, 'id', 'desc');
        return response()->json($cats);
    }


    

    public function indexRedisJson(Request $request)
    {
        $cats = [];
        $cursor = null;
        
        do {
            [$cursor, $keys] = Redis::scan($cursor, [
                'match' => '*cat:*',   // Match keys with 'cat:' pattern
                'count' => 100,
            ]);
            
            foreach ($keys as $key) {
                // Remove the Laravel prefix
                $cleanKey = str_replace(config('database.redis.options.prefix'), '', $key);
                
                $value = Redis::get($cleanKey);
                if ($value !== null) {
                    $decoded = json_decode($value, true);
                    if ($decoded) {
                        $cats[] = $decoded;
                    }
                }
            }
        } while ($cursor != 0);
        
        return response()->json([
            'total' => count($cats),
            'data' => $cats
        ]);
    }


    public function create()
    {
        return view('admin.cats.create');
    }

    public function edit(int $id)
    {
        $cat = $this->cats->findById($id);
        return view('admin.cats.edit', compact('cat'));
    }

    public function store(CatRequest $catRequest, FileUploadRequest $fileRequest)
    {
        $data = $catRequest->validated();
        // Set folder and baseFileName inside the controller
        $folder = 'uploads/cats/file';
        $baseFileName = $data['name'] ?? 'cat';
        $catadded =$this->cats->create($data, $fileRequest, $folder, $baseFileName);
        event(new CatAdded($catadded)); // Fire the CatAdded event
        return redirect()->route('admin.cats.index')
            ->with('success', 'Cat created successfully.');
    }

    public function update(int $id, CatRequest $catRequest, FileUploadRequest $fileRequest)
    {
        $data = $catRequest->validated();

        // Set folder and baseFileName inside the controller
        $folder = 'uploads/cats/file';
        $baseFileName = $data['name'] ?? 'cat';

        $catupdated = $this->cats->update($id, $data, $fileRequest, $folder, $baseFileName);
        event(new CatUpdated($catupdated));

        return redirect()->route('admin.cats.index')
            ->with('success', 'Cat updated successfully.');
    }


    public function destroy(int $id)
    {
        $catt =  $this->cats->findById($id);

        $prods = $this->prods->findByCatId($id); 
        foreach ($prods as $prod) 
        {     
            event(new ProdDeleted($prod->id, $prod->name, $prod->des)); 
        }


        $this->cats->delete($id);
        //event(new CatDeleted($id));
        event(new CatDeleted($id, $catt->name, $catt->des));
        return redirect()->route('admin.cats.index')
            ->with('success', 'Cat deleted successfully.');
    }

    public function destroyMany(Request $request)
    {
        $ids = $request->input('ids', []);

        foreach ($ids as $id) 
        {
            
            
            $prods = $this->prods->findByCatId($id); 
            foreach ($prods as $prod) 
            {     
                event(new ProdDeleted($prod->id, $prod->name, $prod->des)); 
            }

            
            $catx = $this->cats->findById($id);
            if ($catx) {
                event(new CatDeleted($id, $catx->name, $catx->des));
            }
        }


        $this->cats->deleteMany($ids);

        return redirect()->route('admin.cats.index')
            ->with('success', 'Selected cats deleted successfully.');
    }
}
