<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatRequest;
use App\Http\Requests\FileUploadRequest;
use App\Services\CatService;
use Illuminate\Http\Request;
use App\Events\CatAdded;
use Illuminate\Support\Facades\Redis;

class CatController extends Controller
{
    protected CatService $cats;

    public function __construct(CatService $cats)
    {
        $this->cats = $cats;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $cats = $this->cats->paginate($perPage, [
            'id', 'name', 'filer', 'filename', 'mime', 'sizer', 'extension'
        ]);

        return response()->json([
            'items' => $cats->items(),
            'pagination' => [
                'current_page' => $cats->currentPage(),
                'per_page'     => $cats->perPage(),
                'total'        => $cats->total(),
                'last_page'    => $cats->lastPage(),
            ],
        ]);
    }



     public function indexJson(Request $request)
    {
        // choose only id, name, filer
        $fields = ['id', 'name', 'filer', 'filename', 'mime', 'sizer', 'extension'];

        // call select() without $perPage → returns get()
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



    public function show(int $id)
    {
        $cat = $this->cats->findById($id);

        if (!$cat) {
            return response()->json(['error' => 'Cat not found'], 404);
        }

        return response()->json($cat);
    }

    public function store(CatRequest $catRequest, FileUploadRequest $fileRequest)
    {
        $data = $catRequest->validated();
        $folder = 'uploads/cats/file';
        $baseFileName = $data['name'] ?? 'cat';

        $cat = $this->cats->create($data, $fileRequest, $folder, $baseFileName);
        event(new CatAdded($cat));

        return response()->json($cat, 201);
    }

    public function update(int $id, CatRequest $catRequest, FileUploadRequest $fileRequest)
    {
        $data = $catRequest->validated();
        $folder = 'uploads/cats/file';
        $baseFileName = $data['name'] ?? 'cat';

        $cat = $this->cats->update($id, $data, $fileRequest, $folder, $baseFileName);

        if (!$cat) {
            return response()->json(['error' => 'Cat not found'], 404);
        }

        return response()->json($cat);
    }

    public function destroy(int $id)
    {
        $deleted = $this->cats->delete($id);

        if (!$deleted) {
            return response()->json(['error' => 'Cat not found'], 404);
        }

        return response()->json(['message' => 'Cat deleted successfully.']);
    }

    public function destroyMany(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->cats->deleteMany($ids);

        return response()->json(['message' => 'Selected cats deleted successfully.']);
    }
}
